<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

$sql = "SELECT * FROM physicians";
$users = $conn->query($sql);
?>

<body class="g-sidenav-show  bg-gray-200">
  <?php
    include_once('sidebar.php');
  ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <?php
      include_once('navbar.php');
    ?>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                <h6 class="text-white text-capitalize ps-3 mb-0">Physicians List</h6>
                <button class="btn btn-light text-capitalize me-3">Add New Physicians</button>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Full name</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Added Date</th>
                      <th class="text-secondary opacity-7"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                        if ($users->num_rows > 0) {
                          // Output data of each row
                          while($row = $users->fetch_assoc()) {
                            echo "
                              <tr>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["fullname"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle'>
                                <a href='javascript:;' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Edit user'>
                                  Edit
                                </a>
                                <a href='javascript:;' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Delete user'>
                                  Delete
                                </a>
                              </td>
                              </tr>
                            ";
                          }
                        }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
        include_once('footer.php');
      ?>
    </div>
  </main>
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
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.1.0"></script>
</body>

</html>