<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

// Simplified query without filters
$query = "
    SELECT al.*, u.first_name, u.last_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
";
$result = $conn->query($query);

// Get unique table names for tabs
$tables_query = "SELECT DISTINCT table_name FROM activity_logs ORDER BY table_name";
$tables_result = $conn->query($tables_query);
$available_tables = [];
while ($table = $tables_result->fetch_assoc()) {
    $available_tables[] = $table['table_name'];
}
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