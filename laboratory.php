<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){

  if(isset($_POST["location"]) && !empty(trim($_POST["location"]))){
      $location = trim($_POST["location"]);
  }

  if(isset($_POST["fullname"]) && !empty(trim($_POST["fullname"]))){
      $fullname = trim($_POST["fullname"]);
  }

  if(isset($_POST["age"]) && !empty(trim($_POST["age"]))){
      $age = trim($_POST["age"]);
  }

  if(isset($_POST["gender"]) && !empty(trim($_POST["gender"]))){
      $gender = trim($_POST["gender"]);
  }

  if(isset($_POST["contact"]) && !empty(trim($_POST["contact"]))){
      $contact = trim($_POST["contact"]);
  }

  if(isset($_POST["address"]) && !empty(trim($_POST["address"]))){
      $address = trim($_POST["address"]);
  }
  
  if(isset($_POST["physician"]) && !empty(trim($_POST["physician"]))){
      $physician = trim($_POST["physician"]);
  }
  
  if(!empty($location) && !empty($fullname) && !empty($age) && !empty($gender) && !empty($contact) && !empty($address) && !empty($physician) && $_POST["id"] == ""){
    $sql = "INSERT INTO patients (fullname, age, gender, contact, address, physician_id, location_id) VALUES ('$fullname', '$age', '$gender', '$contact', '$address', '$physician', '$location')";
    $addUsers = $conn->query($sql);
  } else if(isset($_POST["id"]) && !empty(trim($_POST["id"]))){
    $sql = "UPDATE patients SET fullname = '$fullname', age = '$age', gender = '$gender', contact = '$contact', address = '$address', physician_id = '$physician', location_id = '$location' WHERE id = '".trim($_POST["id"])."'";
    $editUser = $conn->query($sql);
  }

  if(isset($_POST["delete_id"]) && !empty(trim($_POST["delete_id"]))){
    $delete_id = trim($_POST["delete_id"]);
    $sql = "DELETE FROM patients WHERE id = '$delete_id'";
    $deleteUser = $conn->query($sql);
  }
}

$userssql = "SELECT * FROM patients";
$users = $conn->query($userssql);

$locationsql = "SELECT * FROM locations";
$locations = $conn->query($locationsql);

$physicianssql = "SELECT * FROM users WHERE role = 3";
$physicians = $conn->query($physicianssql);
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
                <h6 class="text-white text-capitalize ps-3 mb-0">Laboratory Result</h6>
                <?php if(isset($_SESSION['module']) && in_array(10, $_SESSION['module'])): ?>
                  <button type="button" class="btn btn-light text-capitalize me-3" data-bs-toggle="modal" data-bs-target="#addLaboratoryModal">
                    Add Results
                  </button>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Full name</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Address</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gender</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age</th>
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
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["fullname"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["address"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".( $row["gender"] == 1 ? "Male" : "Female")."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["age"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle'>";
                            echo in_array(11, $_SESSION['module']) ? "
                              <a href='javascript:void(0);' onclick='editUser(\"".$row["id"]."\",\"".$row["fullname"]."\",\"".$row["age"]."\",\"".$row["gender"]."\",\"".$row["contact"]."\",\"".$row["address"]."\",\"".$row["physician_id"]."\",\"".$row["location_id"]."\")' class='text-secondary font-weight-bold text-xs' data-toggle='tooltip' data-original-title='Edit user'>
                                Edit
                              </a>" : "";
                            echo in_array(12, $_SESSION['module']) ? "
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
    <div class="modal fade" id="addLaboratoryModal" tabindex="-1" aria-labelledby="addLaboratoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addLaboratoryModalLabel">Add Laboratory</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="modal-body">
                <input type="hidden" class="form-control" id="id" name="id">
                <div class="form-group">
                  <label for="location">Location</label>
                  <select class="form-control" id="location" name="location">
                    <option>Choose a Location</option>
                    <?php foreach($locations as $location): ?>
                      <option value="<?php echo $location['id']; ?>"><?php echo $location['location']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="fullname" class="form-label">Full name</label>
                  <input type="text" class="form-control" id="fullname" name="fullname">
                </div>
                <div class="form-group">
                  <label for="age" class="form-label">Age</label>
                  <input type="number" class="form-control" id="age" name="age">
                </div>
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <select class="form-control" id="gender" name="gender">
                    <option>Choose a Gender</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="contact" class="form-label">Contact Number</label>
                  <input type="number" class="form-control" id="contact" name="contact">
                </div>
                <div class="form-group">
                  <label for="address" class="form-label">Address</label>
                  <input type="text" class="form-control" id="address" name="address">
                </div>
                <div class="form-group">
                  <label for="physician">Physician</label>
                  <select class="form-control" id="physician" name="physician">
                    <option>Choose a Physician</option>
                    <?php foreach($physicians as $physician): ?>
                      <option value="<?php echo $physician['id']; ?>"><?php echo $physician['first_name']. " ". $physician['last_name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="form-button">Add Laboratory</button>
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