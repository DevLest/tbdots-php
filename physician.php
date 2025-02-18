<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
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
    
    if($addUsers) {
        logActivity(
            $conn, 
            $_SESSION['user_id'], 
            'CREATE', 
            'physicians', 
            $conn->insert_id, 
            "Created new physician: $first_name $last_name"
        );
    }
  } else if(isset($_POST["id"]) && !empty(trim($_POST["id"]))){
    $sql = "UPDATE users SET username = '$username', first_name = '$first_name', last_name = '$last_name', role = '$role' WHERE id = '".trim($_POST["id"])."'";
    $editUser = $conn->query($sql);
    
    if($editUser) {
        logActivity(
            $conn, 
            $_SESSION['user_id'], 
            'UPDATE', 
            'physicians', 
            $_POST["id"], 
            "Updated physician: $first_name $last_name"
        );
    }
  }

  if(isset($_POST["delete_id"]) && !empty(trim($_POST["delete_id"]))){
    $delete_id = trim($_POST["delete_id"]);
    $sql = "DELETE FROM users WHERE id = '$delete_id'";
    $deleteUser = $conn->query($sql);
    
    if($deleteUser) {
        logActivity(
            $conn, 
            $_SESSION['user_id'], 
            'DELETE', 
            'physicians', 
            $delete_id, 
            "Deleted physician: " . $physician['first_name'] . ' ' . $physician['last_name']
        );
    }
  }
}

$sql = "SELECT DISTINCT 
    u.id, 
    u.username, 
    u.first_name, 
    u.last_name, 
    u.password, 
    r.module, 
    r.description, 
    r.id as role_id, 
    u.created_at,
    COUNT(DISTINCT p.id) as patient_count 
FROM users u
INNER JOIN roles r ON u.role = r.id 
LEFT JOIN patients p ON u.id = p.physician_id
LEFT JOIN lab_results lr ON p.id = lr.patient_id AND lr.physician_id = u.id
WHERE u.role = 3 
GROUP BY u.id
ORDER BY u.created_at DESC";
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
                <?php if(isset($_SESSION['module']) && in_array(6, $_SESSION['module'])): ?>
                  <button type="button" class="btn btn-light text-capitalize me-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    Add Physician
                  </button>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Username</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Full name</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Patients</th>
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
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["username"]."</span></td>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["first_name"]. " ".$row["last_name"]."</span></td>
                                <td>
                                    <button type='button' class='btn btn-link text-secondary mb-0' onclick='viewPatients(".$row["id"].")'>
                                        <span class='text-secondary text-xs font-weight-bold'>".$row["patient_count"]." patients</span>
                                    </button>
                                </td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle'>";
                            echo in_array(7, $_SESSION['module']) ? "
                              <a href='javascript:void(0);' onclick='editUser(\"".$row["id"]."\",\"".$row["username"]."\",\"".$row["first_name"]."\",\"".$row["last_name"]."\",\"".$row["role_id"]."\")' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Edit user'>
                                Edit
                              </a>" : "";
                            echo in_array(8, $_SESSION['module']) ? "
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
                    <option value="3">Physician</option>
                  </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="form-button">Add Physician</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="viewPatientsModal" tabindex="-1" aria-labelledby="viewPatientsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPatientsModalLabel">Physician's Patients</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Patient Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contact</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Address</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Treatment Outcome</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Latest Lab Result</th>
                                </tr>
                            </thead>
                            <tbody id="patientsList">
                            </tbody>
                        </table>
                    </div>
                </div>
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
    var user_id = <?php echo $_SESSION['user_id']; ?>;
    var role = <?php echo $_SESSION['role_id']; ?>;
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

    function viewPatients(physicianId) {
      if(role == 1 || user_id == physicianId){
        fetch(`get_physician_patients.php?physician_id=${physicianId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('patientsList');
                tbody.innerHTML = '';
                
                data.forEach(patient => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${patient.fullname}</td>
                            <td>${patient.age}</td>
                            <td>${patient.contact || 'N/A'}</td>
                            <td>${patient.address || 'N/A'}</td>
                            <td>${patient.treatment_outcome || 'No outcome'}</td>
                            <td>${patient.latest_lab_result || 'No results'}</td>
                        </tr>
                    `;
                });
                
                new bootstrap.Modal(document.getElementById('viewPatientsModal')).show();
            });
          }
    }
  </script>
</body>

</html>