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
  
  if(isset($_POST["height"])) $height = trim($_POST["height"]);
  if(isset($_POST["dob"])) $dob = trim($_POST["dob"]);
  if(isset($_POST["occupation"])) $occupation = trim($_POST["occupation"]);
  if(isset($_POST["phil_health_no"])) $phil_health_no = trim($_POST["phil_health_no"]);
  if(isset($_POST["contact_person"])) $contact_person = trim($_POST["contact_person"]);
  if(isset($_POST["contact_person_no"])) $contact_person_no = trim($_POST["contact_person_no"]);
  
  if(isset($_POST["id"]) && !empty(trim($_POST["id"]))){
    $id = trim($_POST["id"]);
    
    if($dob) {
        $sql = "UPDATE patients SET 
                fullname = '$fullname', 
                age = '$age', 
                gender = '$gender', 
                contact = '$contact', 
                address = '$address', 
                physician_id = '$physician', 
                location_id = '$location',
                height = " . ($height ? "'$height'" : "NULL") . ",
                dob = '$dob',
                occupation = " . ($occupation ? "'$occupation'" : "NULL") . ",
                phil_health_no = " . ($phil_health_no ? "'$phil_health_no'" : "NULL") . ",
                contact_person = " . ($contact_person ? "'$contact_person'" : "NULL") . ",
                contact_person_no = " . ($contact_person_no ? "'$contact_person_no'" : "NULL") . "
                WHERE id = '$id'";
    } else {
        $sql = "UPDATE patients SET 
                fullname = '$fullname', 
                age = '$age', 
                gender = '$gender', 
                contact = '$contact', 
                address = '$address', 
                physician_id = '$physician', 
                location_id = '$location',
                height = " . ($height ? "'$height'" : "NULL") . ",
                dob = NULL,
                occupation = " . ($occupation ? "'$occupation'" : "NULL") . ",
                phil_health_no = " . ($phil_health_no ? "'$phil_health_no'" : "NULL") . ",
                contact_person = " . ($contact_person ? "'$contact_person'" : "NULL") . ",
                contact_person_no = " . ($contact_person_no ? "'$contact_person_no'" : "NULL") . "
                WHERE id = '$id'";
    }
    
    $editUser = $conn->query($sql);
    
    if($editUser) {
        logActivity($conn, $_SESSION['user_id'], 'UPDATE', 'patients', $id, "Updated patient: $fullname");
    }
  }

  if(isset($_POST["delete_id"]) && !empty(trim($_POST["delete_id"]))){
    $delete_id = trim($_POST["delete_id"]);
    $sql = "DELETE FROM patients WHERE id = '$delete_id'";
    $deleteUser = $conn->query($sql);

    // Get patient name before deletion
    $stmt = $conn->prepare("SELECT fullname FROM patients WHERE id = ?");
    $stmt->bind_param('i', $_POST['delete_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    
    logActivity(
        $conn, 
        $_SESSION['user_id'], 
        'DELETE', 
        'patients', 
        $_POST['delete_id'], 
        "Deleted patient: " . $patient['fullname']
    );
  }
}

$userssql = "SELECT * FROM patients";
$users = $conn->query($userssql);

$locationsql = "SELECT * FROM locations";
$locations = $conn->query($locationsql);

$physicianssql = "SELECT * FROM users WHERE role = 3";
$physicians = $conn->query($physicianssql);
?>
<!-- Add this right after your existing <style> tag or create one if it doesn't exist -->
<style>
  .form-control {
    border: 1px solid #ced4da !important;
    background-color: #fff !important;
    padding: 0.375rem 0.75rem !important;
  }

  .form-control:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
  }

  /* Fix z-index issues */
  .modal-backdrop {
    z-index: 1040 !important;
  }

  .modal {
    z-index: 1045 !important;
  }

  #sidenav-main {
    z-index: 1039 !important;
  }

  .modal-dialog {
    max-width: 80%;
  }

  .modal-body {
    padding: 20px;
    max-height: 80vh;
    overflow-y: auto;
  }

  .form-group {
    margin-bottom: 1rem;
  }

  .form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
  }

  .lab-results-section {
    transition: all 0.3s ease;
  }

  #treatmentDetailsContent {
    min-height: 200px;
  }

  #treatmentDetailsContent .card {
    margin-bottom: 1rem;
    border: 0;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }

  #treatmentDetailsContent .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
  }

  #treatmentDetailsContent .card-body {
    padding: 1.25rem;
  }

  #treatmentDetailsContent .table {
    margin-bottom: 0;
  }

  /* Add to your existing styles */
  .footer {
    position: relative;
    margin-top: 2rem;
    padding: 1rem 0;
    background-color: #f8f9fa;
  }

  .card {
    margin-bottom: 0 !important;
  }

  .btn-link {
    padding: 0.25rem 0.5rem;
    text-decoration: none;
  }

  .btn-link:hover {
    background-color: rgba(0,0,0,0.05);
    border-radius: 0.25rem;
  }

  /* Remove the white box */
  .container-fluid.py-4 {
    padding-bottom: 0 !important;
  }

  /* Fix modal z-index */
  .modal-backdrop {
    z-index: 1050 !important;
  }

  .modal {
    z-index: 1055 !important;
  }

  /* Add these styles to your existing <style> section */
  .card-header .input-group-outline {
    border-radius: 0.375rem;
    overflow: hidden;
  }

  .card-header .input-group-outline .form-control {
    border: none !important;
    padding: 0.5rem 1rem !important;
    background-color: white !important;
    color: #344767;
  }

  .card-header .input-group-outline .form-control:focus {
    box-shadow: none !important;
  }

  /* Update the existing search input styles */
  #searchPatient::placeholder {
    color: #7b809a;
    opacity: 1;
  }

  /* Add to your existing styles */
  .card-header .input-group-outline {
    min-width: 250px;
  }

  .card-header .btn-light {
    margin: 0;
    padding: 0.625rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 700;
  }

  .input-group-outline {
    border-radius: 0.375rem;
    overflow: hidden;
  }

  .input-group-outline .form-control {
    border: none;
    padding: 0.5rem 1rem;
  }

  .input-group-outline .form-control:focus {
    box-shadow: none;
  }

  tr:not(:first-child):hover {
    background-color: rgba(0,0,0,0.05);
    cursor: pointer;
  }

  .EDIT, .DELETE {
    cursor: default;
  }
</style>

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
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <div class="d-flex justify-content-between align-items-center px-3">
                  <h6 class="text-white text-capitalize mb-0">Patient List</h6>
                  <div class="d-flex align-items-center">
                    <div class="input-group input-group-outline bg-white rounded me-3">
                      <input type="text" id="searchPatient" class="form-control" placeholder="Search patients...">
                    </div>
                    <?php if(isset($_SESSION['module']) && in_array(10, $_SESSION['module'])): ?>
                      <button type="button" class="btn btn-light text-capitalize" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                        <i class="fa fa-plus">Patient</i>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="patientsTable">
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
                              <tr onclick='showLabResults(".$row["id"].", \"".htmlspecialchars($row["fullname"], ENT_QUOTES)."\")' style='cursor: pointer;'>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["fullname"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["address"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".($row["gender"] == 1 ? "Male" : "Female")."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["age"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle' onclick='event.stopPropagation()'>
                                  " . (in_array(11, $_SESSION['module']) ? "
                                    <button onclick='editPatient(".$row["id"].", ".json_encode($row).")' class='btn btn-link text-secondary mb-0'>
                                      <i class='fa fa-edit text-xs'></i> Edit
                                    </button>" : "") . "
                                  " . (in_array(12, $_SESSION['module']) ? "
                                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this patient?\");'>
                                      <input type='hidden' name='delete_id' value='".$row["id"]."'>
                                      <button type='submit' class='btn btn-link text-secondary mb-0'>
                                        <i class='fa fa-trash text-xs'></i> Delete
                                      </button>
                                    </form>" : "") . "
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
    </div>
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addPatientModalLabel">Add Patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <input type="hidden" class="form-control" id="id" name="id">
                  <div class="form-group">
                    <label for="location">Location</label>
                    <select class="form-control" id="location" name="location" required>
                      <option value="">Choose a Location</option>
                      <?php foreach($locations as $location): ?>
                        <option value="<?php echo $location['id']; ?>"><?php echo $location['location']; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="fullname">Full name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                  </div>
                  <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob">
                  </div>
                  <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" class="form-control" id="age" name="age" required>
                  </div>
                  <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                      <option value="">Choose a Gender</option>
                      <option value="1">Male</option>
                      <option value="2">Female</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="height">Height (cm)</label>
                    <input type="number" class="form-control" id="height" name="height">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" class="form-control" id="contact" name="contact">
                  </div>
                  <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" class="form-control" id="occupation" name="occupation">
                  </div>
                  <div class="form-group">
                    <label for="phil_health_no">PhilHealth Number</label>
                    <input type="text" class="form-control" id="phil_health_no" name="phil_health_no">
                  </div>
                  <div class="form-group">
                    <label for="contact_person">Emergency Contact Person</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person">
                  </div>
                  <div class="form-group">
                    <label for="contact_person_no">Emergency Contact Number</label>
                    <input type="text" class="form-control" id="contact_person_no" name="contact_person_no">
                  </div>
                  <div class="form-group">
                    <label for="physician">Physician</label>
                    <select class="form-control" id="physician" name="physician" required>
                      <option value="">Choose a Physician</option>
                      <?php foreach($physicians as $physician): ?>
                        <option value="<?php echo $physician['id']; ?>"><?php echo $physician['first_name']. " ". $physician['last_name']; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="form-button">Add Patient</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Add this after your patients table card -->
    <div class="row lab-results-section" style="display: none;">
      <div class="col-12">
        <div class="card my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3 mb-0">Patient Treatment Details</h6>
            </div>
          </div>
          <div class="card-body" id="treatmentDetailsContent">
            <!-- Content will be loaded dynamically -->
          </div>
        </div>
      </div>
    </div>
    
    <?php
        include_once('footer.php');
      ?>
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

    function showLabResults(patientId, patientName) {
        console.log('Loading lab results for:', patientId, patientName);
        
        // Show the lab results section
        let labResultsSection = document.querySelector('.lab-results-section');
        labResultsSection.style.display = 'block'; // Make sure it's visible
        
        // Update the content
        document.getElementById('treatmentDetailsContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading results for ${patientName}...</p>
            </div>
        `;
        
        // Scroll to the section
        labResultsSection.scrollIntoView({ behavior: 'smooth' });
        
        // Fetch the results
        fetch(`get_lab_results_card.php?patient_id=${patientId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('treatmentDetailsContent').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('treatmentDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading lab results: ${error.message}
                    </div>
                `;
            });
    }

    // Update the click handler
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded');
        
        // Fix the search functionality
        const searchInput = document.getElementById('searchPatient');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                console.log('Searching for:', searchValue);
                
                const tableRows = document.querySelectorAll('#patientsTable tbody tr');
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            });
        }
        
        // Fix the click handler for rows
        const tbody = document.querySelector('#patientsTable tbody');
        if (tbody) {
            tbody.addEventListener('click', function(e) {
                const row = e.target.closest('tr');
                console.log('Row clicked:', row);
                
                if (row && !e.target.closest('button, form')) {
                    const patientId = row.dataset.patientId;
                    const patientName = row.dataset.patientName;
                    console.log('Clicked patient:', { patientId, patientName });
                    
                    if (patientId) {
                        showLabResults(patientId, patientName);
                    }
                }
            });
        }
        
        // Check for patient_id in URL
        const urlParams = new URLSearchParams(window.location.search);
        const patientId = urlParams.get('patient_id');
        if (patientId) {
            showLabResults(patientId);
        }
    });

    // Add this function to handle patient editing
    function editPatient(id, data) {
      event.stopPropagation(); // Prevent row click event
      
      // Populate the modal with patient data
      document.getElementById('id').value = id;
      document.getElementById('fullname').value = data.fullname;
      document.getElementById('age').value = data.age;
      document.getElementById('gender').value = data.gender;
      document.getElementById('contact').value = data.contact;
      document.getElementById('address').value = data.address;
      document.getElementById('height').value = data.height;
      document.getElementById('dob').value = data.dob;
      document.getElementById('occupation').value = data.occupation;
      document.getElementById('phil_health_no').value = data.phil_health_no;
      document.getElementById('contact_person').value = data.contact_person;
      document.getElementById('contact_person_no').value = data.contact_person_no;
      document.getElementById('physician').value = data.physician_id;
      document.getElementById('location').value = data.location_id;
      
      // Update button text
      document.getElementById('form-button').innerHTML = "Save Changes";
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('addPatientModal'));
      modal.show();
    }
  </script>
</body>

</html>