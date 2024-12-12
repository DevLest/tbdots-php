<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
include_once('head.php');

// Handle POST requests for updating role permissions
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role_id'])) {
    try {
        $role_id = $_POST['role_id'];
        // Convert selected modules to integers before JSON encoding
        $modules = isset($_POST['modules']) ? array_map('intval', $_POST['modules']) : [];
        $modules_json = json_encode($modules);
        
        $sql = "UPDATE roles SET module = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $modules_json, $role_id);
        
        if($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'UPDATE', 'roles', $role_id, 
                       "Updated role permissions");
        }
    } catch (Exception $e) {
        error_log("Error updating role permissions: " . $e->getMessage());
    }
}

// Get all roles (excluding super admin with ID 1)
$user_role = $_SESSION['role_id']; // Assuming role_id is stored in session

if ($user_role == 1) { // Super Admin
    // Show all roles except super admin
    $roles = $conn->query("SELECT * FROM roles WHERE id != 1 ORDER BY id");
} else if ($user_role == 2) { // Admin
    // Show only regular and patients roles
    $roles = $conn->query("SELECT * FROM roles WHERE id IN (3, 4) ORDER BY id");
} else {
    // For other roles, show nothing or handle as needed
    $roles = $conn->query("SELECT * FROM roles WHERE 1=0"); // Returns empty result
}

// Get all modules
$modules = $conn->query("SELECT * FROM modules ORDER BY id");
$modulesList = [];
while($module = $modules->fetch_assoc()) {
    $modulesList[] = $module;
}
?>

<style>
    .form-control {
        border: 1px solid #ced4da !important;
        background-color: #fff !important;
        padding: 0.375rem 0.75rem !important;
    }

    .form-control:disabled {
        background-color: #e9ecef !important;
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        padding: 15px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .checkbox-item input[type="checkbox"] {
        margin-right: 8px;
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
                                    <h6 class="text-white text-capitalize mb-0">Roles & Permissions</h6>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($role = $roles->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="text-secondary text-xs">
                                                        <?php echo htmlspecialchars($role['description']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button onclick='editPermissions(<?php echo json_encode($role); ?>)' 
                                                            class="btn btn-link text-secondary mb-0">
                                                        <i class="fa fa-edit text-xs"></i> Edit Permissions
                                                    </button>
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

        <!-- Edit Permissions Modal -->
        <div class="modal fade" id="editPermissionsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role Permissions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="role_id" id="edit_role_id">
                        <div class="modal-body">
                            <div class="card mb-3">
                                <div class="card-header">Role Information</div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Role Name</label>
                                        <input type="text" class="form-control" id="role_description" disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Permissions</label>
                                        <div class="checkbox-group">
                                            <?php foreach($modulesList as $module): ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" 
                                                           name="modules[]" 
                                                           value="<?php echo $module['id']; ?>" 
                                                           id="module_<?php echo $module['id']; ?>">
                                                    <label for="module_<?php echo $module['id']; ?>">
                                                        <?php echo htmlspecialchars($module['module']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
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
        function editPermissions(role) {
            document.getElementById('edit_role_id').value = role.id;
            document.getElementById('role_description').value = role.description;
            
            // Reset all checkboxes
            document.querySelectorAll('input[name="modules[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Parse modules and ensure they're integers
            let modules = JSON.parse(role.module);
            modules = modules.map(Number);  // Convert all values to numbers
            
            modules.forEach(moduleId => {
                const checkbox = document.getElementById('module_' + moduleId);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            
            new bootstrap.Modal(document.getElementById('editPermissionsModal')).show();
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