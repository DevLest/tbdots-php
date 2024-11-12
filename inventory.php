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
            $product_id = trim($_POST['product_id']);
            $quantity = trim($_POST['quantity']);
            $expiration_date = trim($_POST['expiration_date']);
            $batch_number = generateBatchNumber($conn);  // Auto-generate batch number

            $sql = "INSERT INTO inventory (product_id, quantity, expiration_date, batch_number) 
                   VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $product_id, $quantity, $expiration_date, $batch_number);
            
            if($stmt->execute()) {
                logActivity($conn, $_SESSION['user_id'], 'INSERT', 'inventory', $stmt->insert_id, 
                           "Added inventory for product ID: $product_id");
            }
        } catch (Exception $e) {
            error_log("Error in inventory operation: " . $e->getMessage());
            // Handle error appropriately
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
        MIN(i.expiration_date) as nearest_expiry
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
                                    <div class="d-flex align-items-center">
                                        <div class="input-group input-group-outline bg-white rounded me-3">
                                            <input type="text" id="searchInventory" class="form-control" placeholder="Search inventory...">
                                        </div>
                                        <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                            <i class="fa fa-plus"></i> Add Product
                                        </button>
                                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                            <i class="fa fa-plus"></i> Add Stock
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
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
                                            <tr>
                                                <td class="ps-4"><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['brand_name']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['generic_name']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['uses']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['dosage']); ?></span></td>
                                                <td><span class="text-secondary text-xs"><?php echo htmlspecialchars($row['unit_of_measure']); ?></span></td>
                                                <td>
                                                    <span class="text-xs <?php echo $row['total_stock'] < 10 ? 'stock-warning' : 'stock-ok'; ?>">
                                                        <?php echo $row['total_stock']; ?>
                                                    </span>
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
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-link text-secondary mb-0">
                                                            <i class="fa fa-trash text-xs"></i> Delete
                                                        </button>
                                                    </form>
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
                        <h5 class="modal-title">Add Inventory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="inventory">
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
    </script>
</body>
</html>
