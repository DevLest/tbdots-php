<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

// Get filters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$table_filter = isset($_GET['table']) ? $_GET['table'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$sql = "SELECT 
          al.*,
          u.username as user_name,
          u.first_name,
          u.last_name
        FROM activity_logs al
        JOIN users u ON al.user_id = u.id
        WHERE 1=1";

if ($action_filter) {
    $sql .= " AND al.action = '$action_filter'";
}
if ($table_filter) {
    $sql .= " AND al.table_name = '$table_filter'";
}
if ($date_filter) {
    $sql .= " AND DATE(al.created_at) = '$date_filter'";
}
if ($search) {
    $sql .= " AND (u.username LIKE '%$search%' OR al.details LIKE '%$search%')";
}

$sql .= " ORDER BY al.created_at DESC";
$result = $conn->query($sql);
?>

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