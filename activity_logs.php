<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

$action_filter = $_GET['action'] ?? '';
$table_filter = $_GET['table'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build the query
$query = "
    SELECT al.*, u.first_name, u.last_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE 1=1
";

// Add filters
if ($action_filter) {
    $query .= " AND al.action = '" . $conn->real_escape_string($action_filter) . "'";
}
if ($table_filter) {
    $query .= " AND al.table_name = '" . $conn->real_escape_string($table_filter) . "'";
}
if ($date_filter) {
    $query .= " AND DATE(al.created_at) = '" . $conn->real_escape_string($date_filter) . "'";
}
if ($search) {
    $query .= " AND (al.details LIKE '%" . $conn->real_escape_string($search) . "%'
                OR u.first_name LIKE '%" . $conn->real_escape_string($search) . "%'
                OR u.last_name LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$query .= " ORDER BY al.created_at DESC";
$result = $conn->query($query);
?>

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
                <h6 class="text-white text-capitalize ps-3">Activity Logs</h6>
              </div>
            </div>
            
            <!-- Filters -->
            <div class="card-body px-3 pb-2">
              <form method="GET" class="row mb-3">
                <div class="col-md-3">
                  <select name="action" class="form-control">
                    <option value="">All Actions</option>
                    <option value="CREATE" <?= $action_filter == 'CREATE' ? 'selected' : '' ?>>Create</option>
                    <option value="UPDATE" <?= $action_filter == 'UPDATE' ? 'selected' : '' ?>>Update</option>
                    <option value="DELETE" <?= $action_filter == 'DELETE' ? 'selected' : '' ?>>Delete</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select name="table" class="form-control">
                    <option value="">All Tables</option>
                    <option value="patients" <?= $table_filter == 'patients' ? 'selected' : '' ?>>Patients</option>
                    <option value="users" <?= $table_filter == 'users' ? 'selected' : '' ?>>Users</option>
                    <!-- Add other tables as needed -->
                  </select>
                </div>
                <div class="col-md-3">
                  <input type="date" name="date" class="form-control" value="<?= $date_filter ?>">
                </div>
                <div class="col-md-3">
                  <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= $search ?>">
                </div>
                <div class="col-md-12 mt-2">
                  <button type="submit" class="btn btn-primary">Filter</button>
                  <a href="activity_logs.php" class="btn btn-secondary">Reset</a>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Date/Time</th>
                      <th>User</th>
                      <th>Action</th>
                      <th>Table</th>
                      <th>Details</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= $row['action'] ?></td>
                        <td><?= $row['table_name'] ?></td>
                        <td><?= htmlspecialchars($row['details']) ?></td>
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

    <?php include_once('footer.php'); ?>
  </main>

  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.1.0"></script>
</body>

</html> 