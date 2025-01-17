<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
include_once('head.php');

// Add this function at the top of your file, after the includes
function generateBatchNumber($conn) {
    // Format: YYYYMMDD-XXX where XXX is a sequential number
    $date = date('Ymd');
    
    // Get the latest batch number for today
    $sql = "SELECT batch_number 
            FROM inventory 
            WHERE batch_number LIKE ?
            ORDER BY id DESC LIMIT 1";
    $pattern = $date . '-%';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Extract the sequence number and increment it
        $sequence = intval(substr($row['batch_number'], -3)) + 1;
    } else {
        // Start with 1 if no batch number exists for today
        $sequence = 1;
    }
    
    // Format the sequence number with leading zeros
    return $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}



// Handle POST requests
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add/Edit Product
    if(isset($_POST['action']) && $_POST['action'] == 'product') {
        try {
            $brand_name = trim($_POST['brand_name']);
            $generic_name = trim($_POST['generic_name']);
            $uses = trim($_POST['uses']);
            $dosage = trim($_POST['dosage']);
            $unit_of_measure = trim($_POST['unit_of_measure']);
            
            if(isset($_POST['product_id']) && !empty($_POST['product_id'])) {
                // Update existing product
                $sql = "UPDATE products SET 
                        brand_name = ?, 
                        generic_name = ?, 
                        uses = ?, 
                        dosage = ?, 
                        unit_of_measure = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $brand_name, $generic_name, $uses, $dosage, $unit_of_measure, $_POST['product_id']);
            } else {
                // Add new product
                $sql = "INSERT INTO products (brand_name, generic_name, uses, dosage, unit_of_measure) 
                       VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $brand_name, $generic_name, $uses, $dosage, $unit_of_measure);
            }
            
            if($stmt->execute()) {
                $id = isset($_POST['product_id']) ? $_POST['product_id'] : $stmt->insert_id;
                logActivity($conn, $_SESSION['user_id'], 
                           isset($_POST['product_id']) ? 'UPDATE' : 'INSERT', 
                           'products', 
                           $id, 
                           "Added/Updated product: $brand_name");
            }
        } catch (Exception $e) {
            error_log("Error in product operation: " . $e->getMessage());
            // Handle error appropriately
        }
    }

    // Add Inventory (separate from product creation)
    if(isset($_POST['action']) && $_POST['action'] == 'inventory') {
        
        try {
            $type = $_POST['type']; // Will now be either 'IN' or 'OUT'
            
            if($type == 'IN') {
                // Process single stock in
                $product_id = trim($_POST['product_id']);
                $quantity = trim($_POST['quantity']);
                $expiration_date = trim($_POST['expiration_date']);
                $batch_number = generateBatchNumber($conn);
                
                $sql = "INSERT INTO inventory (product_id, quantity, expiration_date, batch_number) 
                       VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $product_id, $quantity, $expiration_date, $batch_number);
                
                if($stmt->execute()) {
                    // Record transaction
                    $sql = "INSERT INTO inventory_transactions 
                           (type, product_id, quantity, batch_number, patient_id, notes, user_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;
                    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
                    $stmt->bind_param("siisisi", $type, $product_id, $quantity, 
                                    $batch_number, $patient_id, $notes, $_SESSION['user_id']);
                    $stmt->execute();
                }
                
            } else {
                // Process multiple stock out items
                $product_ids = $_POST['product_id'];
                $quantities = $_POST['quantity'];
                $patient_id = isset($_POST['patient_id']) && !empty($_POST['patient_id']) ? $_POST['patient_id'] : null;
                $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Process each item
                    for($i = 0; $i < count($product_ids); $i++) {
                        $product_id = trim($product_ids[$i]);
                        $quantity = trim($quantities[$i]);
                        
                        // Validate available quantity - Fixed query
                        $sql = "SELECT COALESCE(SUM(quantity), 0) as total 
                               FROM inventory 
                               WHERE product_id = ? 
                               AND (expiration_date > CURDATE() OR expiration_date IS NULL)
                               AND quantity > 0";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $available = $result->fetch_assoc()['total'];
                        
                        if($available < $quantity) {
                            throw new Exception("Insufficient inventory for product ID: $product_id. Available: $available, Requested: $quantity");
                        }
                        
                        // Get inventory items ordered by expiration date (FIFO)
                        $sql = "SELECT id, quantity 
                               FROM inventory 
                               WHERE product_id = ? 
                               AND (expiration_date > CURDATE() OR expiration_date IS NULL)
                               AND quantity > 0
                               ORDER BY expiration_date ASC, id ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $inventoryItems = $stmt->get_result();
                        
                        $remainingQuantity = $quantity;
                        
                        // Deduct from each inventory item until the required quantity is met
                        while($item = $inventoryItems->fetch_assoc()) {
                            if($remainingQuantity <= 0) break;
                            
                            $deductQuantity = min($remainingQuantity, $item['quantity']);
                            $newQuantity = $item['quantity'] - $deductQuantity;
                            
                            // Update inventory
                            $sql = "UPDATE inventory 
                                   SET quantity = ? 
                                   WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ii", $newQuantity, $item['id']);
                            $stmt->execute();
                            
                            $remainingQuantity -= $deductQuantity;
                        }
                        
                        // Record transaction
                        $sql = "INSERT INTO inventory_transactions 
                               (type, product_id, quantity, patient_id, notes, user_id) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("siisis", $type, $product_id, $quantity, 
                                        $patient_id, $notes, $_SESSION['user_id']);
                        $stmt->execute();
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $_SESSION['success_message'] = "Inventory successfully updated!";
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    error_log("Error in inventory operation: " . $e->getMessage());
                    $_SESSION['error_message'] = "Error updating inventory: " . $e->getMessage();
                }
            }
            
        } catch (Exception $e) {
            if($type == 'OUT') {
                $conn->rollback();
            }
            error_log("Error in inventory operation: " . $e->getMessage());
            $_SESSION['error_message'] = "Error updating inventory: " . $e->getMessage();
        }
    }

    // Delete Product
    if(isset($_POST['delete_id'])) {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_POST['delete_id']);
        
        if($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'DELETE', 'products', $_POST['delete_id'], "Deleted product");
        }
    }
}

// Get all products with their total inventory
$sql = "SELECT p.*, 
        COALESCE(SUM(i.quantity), 0) as total_stock,
        MIN(i.expiration_date) as nearest_expiry,
        GROUP_CONCAT(
            CONCAT(i.batch_number, ':', i.quantity, ':', i.expiration_date)
            ORDER BY i.expiration_date ASC
            SEPARATOR '|'
        ) as batch_details
        FROM products p
        LEFT JOIN inventory i ON p.id = i.product_id
        GROUP BY p.id";
$products = $conn->query($sql);
?>

<!-- Add necessary styles -->
<style>
    /* ... (copy styles from patients.php) ... */
    .stock-warning { color: #dc3545; }
    .stock-ok { color: #28a745; }
    .expiry-warning { color: #ffc107; }

    .form-control {
        border: 1px solid #ced4da !important;
        background-color: #fff !important;
        padding: 0.375rem 0.75rem !important;
    }

    .form-control:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .modal-body {
        padding: 20px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .card {
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
    }

    .card-header {
        background-color: #f8f9fa;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Make modal wider */
    .modal-dialog {
        max-width: 95% !important;
        margin: 1.75rem auto;
        position: relative;
    }

    .modal {
        z-index: 1060 !important;
    }

    .sidenav {
        z-index: 1040 !important;
    }

    /* Style scrollbar */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure proper spacing from top */
    .modal.show {
        padding-left: 0 !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
    }

    /* Button styling */
    .btn.btn-sm {
        padding: 8px 12px !important;
        font-size: 12px !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        min-width: fit-content !important;
    }
    
    .btn.bg-white {
        color: #344767 !important;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    .btn.bg-white:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    }
    
    /* Search input styling */
    .input-group-outline {
        min-width: 200px;
        max-width: 300px;
        flex: 1;
    }

    .total-stock {
        color: #28a745; /* Green for total stock */
    }
    .usable-stock {
        color: #007bff; /* Blue for usable stock */
    }
    .expired-stock {
        color: #dc3545; /* Red for expired stock */
    }
</style>

<body class="g-sidenav-show bg-gray-200">
    <?php include_once('sidebar.php'); ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include_once('navbar.php'); ?>
        
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <div class="d-flex justify-content-between align-items-center px-3">
                                    <h6 class="text-white text-capitalize mb-0">Inventory Management</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="input-group input-group-outline bg-white rounded">
                                            <input type="text" id="searchInventory" class="form-control" placeholder="Search inventory...">
                                        </div>
                                        <button type="button" class="btn bg-white btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                            + Product
                                        </button>
                                        <button type="button" class="btn bg-white btn-sm" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                            + Stock In
                                        </button>
                                        <button type="button" class="btn bg-white btn-sm" data-bs-toggle="modal" data-bs-target="#inventoryOutModal">
                                            - Stock Out
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            <?php if(isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success alert-dismissible fade show mx-4" role="alert">
                                    <?php 
                                    echo $_SESSION['success_message'];
                                    unset($_SESSION['success_message']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
                                    <?php 
                                    echo $_SESSION['error_message'];
                                    unset($_SESSION['error_message']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" id="inventoryTable">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Brand Name</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Generic Name</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Uses</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dosage</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nearest Expiry</th>
                                            <th class="text-secondary opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $products->fetch_assoc()): ?>
                                            <?php
                                            // Calculate usable and expired stock
                                            $usable_stock = 0;
                                            $expired_stock = 0;
                                            if ($row['batch_details']) {
                                                $batches = explode('|', $row['batch_details']);
                                                foreach ($batches as $batch) {
                                                    list($batchNumber, $quantity, $expiryDate) = explode(':', $batch);
                                                    if (strtotime($expiryDate) > time()) {
                                                        $usable_stock += $quantity;
                                                    } else {
                                                        $expired_stock += $quantity;
                                                    }
                                                }
                                            }
                                            ?>
                                            <tr onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)" style="cursor: pointer;">
                                                <td class="ps-4"><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['brand_name']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['generic_name']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['uses']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['dosage']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['unit_of_measure']); ?></span></td>
                                                <td>
                                                    <div class="stock-info">
                                                        <span class="total-stock">Total: <?php echo $row['total_stock']; ?></span><br>
                                                        <span class="usable-stock">Usable: <?php echo $usable_stock; ?></span><br>
                                                        <span class="expired-stock">Expired: <?php echo $expired_stock; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-xs <?php echo ($row['nearest_expiry'] && strtotime($row['nearest_expiry']) < strtotime('+30 days')) ? 'expiry-warning' : ''; ?>">
                                                        <?php echo $row['nearest_expiry'] ? date('Y-m-d', strtotime($row['nearest_expiry'])) : 'N/A'; ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <button onclick='editProduct(<?php echo json_encode($row); ?>)' class="btn btn-link text-secondary mb-0">
                                                        <i class="fa fa-edit text-xs"></i> Edit
                                                    </button>
                                                    <!-- <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');"> -->
                                                        <!-- <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>"> -->
                                                        <!-- <button type="submit" class="btn btn-link text-secondary mb-0"> -->
                                                            <!-- <i class="fa fa-trash text-xs"></i> Delete -->
                                                        <!-- </button> -->
                                                    <!-- </form> -->
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="product">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        <div class="modal-body">
                            <div class="card mb-3">
                                <div class="card-header">Product Information</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Brand Name</label>
                                                <input type="text" class="form-control" name="brand_name" id="brand_name" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Generic Name</label>
                                                <input type="text" class="form-control" name="generic_name" id="generic_name" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Uses</label>
                                                <textarea class="form-control" name="uses" id="uses"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Dosage</label>
                                                <input type="text" class="form-control" name="dosage" id="dosage">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Unit of Measure</label>
                                                <input type="text" class="form-control" name="unit_of_measure" id="unit_of_measure" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Inventory Modal -->
        <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Stock In</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="inventory">
                        <input type="hidden" name="type" value="IN">
                        <div class="modal-body">
                            <div class="card mb-3">
                                <div class="card-header">Stock Information</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Product</label>
                                                <select class="form-control" name="product_id" required>
                                                    <option value="">Select Product</option>
                                                    <?php 
                                                    $products->data_seek(0);
                                                    while($row = $products->fetch_assoc()): 
                                                    ?>
                                                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['brand_name']); ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Quantity</label>
                                                <input type="number" class="form-control" name="quantity" required min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Expiration Date</label>
                                                <input type="date" class="form-control" name="expiration_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Stock</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Inventory Out Modal -->
        <div class="modal fade" id="inventoryOutModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Inventory Out</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" id="inventoryOutForm">
                        <input type="hidden" name="action" value="inventory">
                        <input type="hidden" name="type" value="OUT">
                        <div class="modal-body">
                            <div class="card mb-3">
                                <div class="card-header">Release Information</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Patient (Optional)</label>
                                                <select class="form-control" name="patient_id">
                                                    <option value="">Select Patient</option>
                                                    <?php 
                                                    $patients = $conn->query("SELECT id, fullname FROM patients ORDER BY fullname");
                                                    while($patient = $patients->fetch_assoc()): 
                                                    ?>
                                                        <option value="<?php echo $patient['id']; ?>">
                                                            <?php echo htmlspecialchars($patient['fullname']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Notes</label>
                                                <textarea class="form-control" name="notes"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="items-container">
                                        <div class="row item-row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Product</label>
                                                    <select class="form-control product-select" name="product_id[]" required>
                                                        <option value="">Select Product</option>
                                                        <?php 
                                                        $products->data_seek(0);
                                                        while($row = $products->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $row['id']; ?>" 
                                                                    data-stock="<?php echo $row['total_stock']; ?>">
                                                                <?php echo htmlspecialchars($row['brand_name']); ?> 
                                                                (Available: <?php echo $row['total_stock']; ?>)
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Quantity</label>
                                                    <input type="number" class="form-control quantity-input" 
                                                           name="quantity[]" required min="1">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-item" 
                                                        style="margin-top: 32px;">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success" id="addItem">Add Item</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Release Items</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add this new modal before the closing body tag -->
        <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Product Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">Basic Information</div>
                                    <div class="card-body">
                                        <p><strong>Brand Name:</strong> <span id="detail_brand_name"></span></p>
                                        <p><strong>Generic Name:</strong> <span id="detail_generic_name"></span></p>
                                        <p><strong>Uses:</strong> <span id="detail_uses"></span></p>
                                        <p><strong>Dosage:</strong> <span id="detail_dosage"></span></p>
                                        <p><strong>Unit of Measure:</strong> <span id="detail_unit_of_measure"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">Stock Information</div>
                                    <div class="card-body">
                                        <p><strong>Current Stock:</strong> <span id="detail_stock"></span></p>
                                        <p><strong>Nearest Expiry:</strong> <span id="detail_expiry"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">Batch List</div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Batch Number</th>
                                                        <th>Quantity</th>
                                                        <th>Expiration Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="batch_list">
                                                    <!-- Batch details will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once('footer.php'); ?>
    </main>

    <!-- Scripts -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInventory').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#inventoryTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Edit product function
        function editProduct(data) {
            document.getElementById('edit_product_id').value = data.id;
            document.getElementById('brand_name').value = data.brand_name;
            document.getElementById('generic_name').value = data.generic_name;
            document.getElementById('uses').value = data.uses;
            document.getElementById('dosage').value = data.dosage;
            document.getElementById('unit_of_measure').value = data.unit_of_measure;
            
            document.getElementById('productModalLabel').textContent = 'Edit Product';
            new bootstrap.Modal(document.getElementById('addProductModal')).show();
        }

        // Initialize perfect scrollbar
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

        // Add this to your existing script section
        function showProductDetails(data) {
            // Populate existing details
            document.getElementById('detail_brand_name').textContent = data.brand_name;
            document.getElementById('detail_generic_name').textContent = data.generic_name;
            document.getElementById('detail_uses').textContent = data.uses;
            document.getElementById('detail_dosage').textContent = data.dosage;
            document.getElementById('detail_unit_of_measure').textContent = data.unit_of_measure;
            document.getElementById('detail_stock').textContent = data.total_stock;
            document.getElementById('detail_expiry').textContent = data.nearest_expiry || 'N/A';
            
            // Populate batch list
            const batchList = document.getElementById('batch_list');
            batchList.innerHTML = ''; // Clear existing content
            
            if (data.batch_details) {
                const batches = data.batch_details.split('|');
                batches.forEach(batch => {
                    const [batchNumber, quantity, expiryDate] = batch.split(':');
                    const row = document.createElement('tr');
                    
                    // Calculate status based on expiry date
                    const today = new Date();
                    const expiryDateTime = new Date(expiryDate);
                    const daysUntilExpiry = Math.ceil((expiryDateTime - today) / (1000 * 60 * 60 * 24));
                    
                    let status = '';
                    let statusClass = '';
                    if (daysUntilExpiry < 0) {
                        status = 'Expired';
                        statusClass = 'text-danger';
                    } else if (daysUntilExpiry <= 30) {
                        status = 'Expiring Soon';
                        statusClass = 'text-warning';
                    } else {
                        status = 'Good';
                        statusClass = 'text-success';
                    }
                    
                    row.innerHTML = `
                        <td>${batchNumber}</td>
                        <td>${quantity}</td>
                        <td>${expiryDate}</td>
                        <td><span class="${statusClass}">${status}</span></td>
                    `;
                    
                    batchList.appendChild(row);
                });
            } else {
                batchList.innerHTML = '<tr><td colspan="4" class="text-center">No batch information available</td></tr>';
            }
            
            // Show the modal
            new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
        }

        // Add hover effect styles
        document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
            row.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseout', function() {
                this.style.backgroundColor = '';
            });
        });

        // Handle dynamic item rows
        $(document).ready(function() {
            $('#addItem').click(function() {
                const newRow = $('.item-row:first').clone();
                newRow.find('input').val('');
                newRow.find('select').val('');
                $('.items-container').append(newRow);
            });

            $(document).on('click', '.remove-item', function() {
                if($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                }
            });

            // Validate quantity against available stock
            $(document).on('change', '.quantity-input, .product-select', function() {
                const row = $(this).closest('.item-row');
                const quantity = parseInt(row.find('.quantity-input').val()) || 0;
                const selected = row.find('.product-select option:selected');
                const available = parseInt(selected.data('stock')) || 0;

                if(quantity > available) {
                    alert('Quantity cannot exceed available stock: ' + available);
                    row.find('.quantity-input').val(available);
                }
            });

            // Form validation before submit
            $('#inventoryOutForm').submit(function(e) {
                const items = {};
                let isValid = true;

                $('.item-row').each(function() {
                    const productId = $(this).find('.product-select').val();
                    const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
                    
                    if(!productId || quantity <= 0) {
                        alert('Please fill all required fields');
                        isValid = false;
                        return false;
                    }

                    // Sum quantities for same product
                    items[productId] = (items[productId] || 0) + quantity;
                });

                // Validate total quantities
                for(const productId in items) {
                    const available = parseInt($('.product-select option[value="' + productId + '"]').data('stock'));
                    if(items[productId] > available) {
                        alert('Total quantity for a product cannot exceed available stock');
                        isValid = false;
                        break;
                    }
                }

                if(!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
