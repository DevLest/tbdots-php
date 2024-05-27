<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){

  if(isset($_POST["username"]) && !empty(trim($_POST["username"]))){
      $username = trim($_POST["username"]);
  }

  if(isset($_POST["password"]) && !empty(trim($_POST["password"]))){
      $password = md5(trim($_POST["password"]));
  }

  if(isset($_POST["first_name"]) && !empty(trim($_POST["first_name"]))){
      $first_name = trim($_POST["first_name"]);
  }

  if(isset($_POST["last_name"]) && !empty(trim($_POST["last_name"]))){
      $last_name = trim($_POST["last_name"]);
  }

  if(isset($_POST["role"]) && !empty(trim($_POST["role"]))){
      $role = trim($_POST["role"]);
  }
  
  if(!empty($username) && !empty($password) && !empty($first_name) && !empty($last_name) && !empty($role) && $_POST["id"] == ""){
    $sql = "INSERT INTO users (username, password, first_name, last_name, role) VALUES ('$username', '$password', '$first_name', '$last_name', '$role')";
    $addUsers = $conn->query($sql);
  } else if(isset($_POST["id"]) && !empty(trim($_POST["id"]))){
    $sql = "UPDATE users SET username = '$username', first_name = '$first_name', last_name = '$last_name', role = '$role' WHERE id = '".trim($_POST["id"])."'";
    $editUser = $conn->query($sql);
  }

  
  if(isset($_POST["delete_id"]) && !empty(trim($_POST["delete_id"]))){
    $delete_id = trim($_POST["delete_id"]);
    $sql = "DELETE FROM users WHERE id = '$delete_id'";
    $deleteUser = $conn->query($sql);
  }
}

$sql = "SELECT users.id, username, first_name, last_name, password, roles.module, roles.description, roles.id as role_id, users.created_at FROM users INNER JOIN roles ON users.role = roles.id WHERE users.role != 3 ORDER BY users.created_at DESC";
$users = $conn->query($sql);

$rolesQuery = "SELECT * FROM roles";
$userRoles = $conn->query($rolesQuery);
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
                <h6 class="text-white text-capitalize ps-3 mb-0">Users List</h6>
                
                <?php if(isset($_SESSION['module']) && in_array(2, $_SESSION['module'])): ?>
                  <button type="button" class="btn btn-light text-capitalize me-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    Add User
                  </button>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Username</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Full name</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Registered Since</th>
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
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["username"]."</span></td>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["first_name"]. " ".$row["last_name"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["description"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle'>";
                            echo in_array(3, $_SESSION['module']) ? "
                              <a href='javascript:void(0);' onclick='editUser(\"".$row["id"]."\",\"".$row["username"]."\",\"".$row["first_name"]."\",\"".$row["last_name"]."\",\"".$row["role_id"]."\")' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Edit user'>
                                Edit
                              </a>" : "";
                            echo in_array(4, $_SESSION['module']) ? "
                              <form method='POST' action='".htmlspecialchars($_SERVER["PHP_SELF"])."'>
                                  <input type='hidden' name='delete_id' value='".$row["id"]."'>
                                  <button type='submit' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Delete user'>
                                      Delete
                                  </button>
                              </form>" : "";
                            echo "
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
    
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="modal-body">
                <input type="hidden" class="form-control" id="id" name="id">
                <div class="form-group">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" name="username">
                </div>
                <div class="form-group">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="form-group">
                  <label for="first_name" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="first_name" name="first_name">
                </div>
                <div class="form-group">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="last_name" name="last_name">
                </div>
                <div class="form-group">
                  <label for="role">Role</label>
                  <select class="form-control" id="role" name="role">
                    <option></option>
                    <?php foreach($userRoles as $userRole): ?>
                      <option value="<?php echo $userRole['id']; ?>"><?php echo $userRole['description']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="form-button">Add User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.1.0"></script>
  <script>
    var modal = new bootstrap.Modal(document.getElementById('exampleModal'))
    document.getElementById('openModalButton').addEventListener('click', function () {
      modal.show()
    })

    function editUser(id, username, first_name, last_name, role){
      var modal = new bootstrap.Modal(document.getElementById('addUserModal'))
      document.getElementById('id').value = id;
      document.getElementById('username').value = username;
      document.getElementById('first_name').value = first_name;
      document.getElementById('last_name').value = last_name;
      document.getElementById('role').value = role;
      document.getElementById('password').readOnly = true;
      document.getElementById('form-button').innerHTML = "Save Changes";
      modal.show()
    }

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