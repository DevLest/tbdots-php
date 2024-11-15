<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
include_once('head.php');

// Initialize variables
$fullname = '';
$dob = '';
$location = null;
$age = 0;
$gender = 0;
$contact = '';
$address = '';
$physician = null;
$height = null;
$occupation = '';
$phil_health_no = '';
$contact_person = '';
$contact_person_no = '';

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

  if (isset($_POST["delete_id"]) && !empty(trim($_POST["delete_id"]))) {
    $delete_id = trim($_POST["delete_id"]);

    // Check if there are related lab results
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM lab_results WHERE patient_id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $labResults = $result->fetch_assoc();

    if ($labResults['count'] > 0) {
        echo "<div class='alert alert-danger'>Cannot delete patient. There are related lab results associated with this patient.</div>";
    } else {
        // Proceed to delete the patient
        $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();

        // Get patient name before deletion for logging
        $stmt = $conn->prepare("SELECT fullname FROM patients WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();

        if ($patient) {
            logActivity(
                $conn,
                $_SESSION['user_id'],
                'DELETE',
                'patients',
                $delete_id,
                "Deleted patient: " . $patient['fullname']
            );
        }
    }
  }

  if ($fullname && $dob && $location) {
        // Check if the patient already exists
        $stmt = $conn->prepare("SELECT id FROM patients WHERE fullname = ? AND dob = ? AND location_id = ?");
        $stmt->bind_param('ssi', $fullname, $dob, $location);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingPatient = $result->fetch_assoc();

        if ($existingPatient) {
            // Update existing patient
            $id = $existingPatient['id'];
            $sql = "UPDATE patients SET 
                    age = ?, 
                    gender = ?, 
                    contact = ?, 
                    address = ?, 
                    physician_id = ?, 
                    height = ?, 
                    occupation = ?, 
                    phil_health_no = ?, 
                    contact_person = ?, 
                    contact_person_no = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iississsssi', $age, $gender, $contact, $address, $physician, $height, $occupation, $phil_health_no, $contact_person, $contact_person_no, $id);
        } else {
            // Insert new patient
            $sql = "INSERT INTO patients (fullname, age, gender, contact, address, physician_id, location_id, height, dob, occupation, phil_health_no, contact_person, contact_person_no) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('siisssiisssss', $fullname, $age, $gender, $contact, $address, $physician, $location, $height, $dob, $occupation, $phil_health_no, $contact_person, $contact_person_no);
        }

        if ($stmt->execute()) {
            $action = $existingPatient ? 'UPDATE' : 'CREATE';
            $patientId = $existingPatient ? $id : $stmt->insert_id;
            logActivity($conn, $_SESSION['user_id'], $action, 'patients', $patientId, ($action == 'UPDATE' ? "Updated" : "Added") . " patient: $fullname");
        }
    }
}

// Add location filter query
$locationsQuery = "
    SELECT l.id, m.location as municipality, b.name as barangay 
    FROM locations l
    JOIN municipalities m ON l.municipality_id = m.id 
    JOIN barangays b ON l.barangay_id = b.id
    ORDER BY m.location, b.name";
$locations = $conn->query($locationsQuery);

// Get selected location and name from URL parameters
$selectedLocation = isset($_GET['location']) ? (int)$_GET['location'] : 0;
$searchName = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the WHERE clause
$whereConditions = [];
if ($selectedLocation > 0) {
    $whereConditions[] = "p.location_id = $selectedLocation";
}
if (!empty($searchName)) {
    $whereConditions[] = "p.fullname LIKE '%" . $conn->real_escape_string($searchName) . "%'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Ensure your SQL query is correct and fetching the necessary data
$sql = "
    SELECT 
        p.*,
        COALESCE(b.name, '') as barangay_name,
        COALESCE(m.location, '') as municipality_name
    FROM patients p
    LEFT JOIN locations l ON p.location_id = l.id
    LEFT JOIN municipalities m ON l.municipality_id = m.id
    LEFT JOIN barangays b ON l.barangay_id = b.id
    $whereClause
    ORDER BY p.created_at DESC";
$result = $conn->query($sql);

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
    background-color: rgba(0, 0, 0, 0.05);
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
    background-color: rgba(0, 0, 0, 0.05);
    cursor: pointer;
  }

  .EDIT,
  .DELETE {
    cursor: default;
  }

  .card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .card-header {
    border-radius: 15px !important;
  }

  .form-select {
    height: 40px;
    border-radius: 5px;
  }

  .form-select option {
    color: #333;
  }

  .form-control {
    height: 40px;
    border-radius: 5px;
  }

  .btn-light {
    background-color: white;
    border: none;
    height: 40px;
    border-radius: 5px;
    font-weight: 500;
    color: #333;
  }

  /* Remove focus outlines */
  .form-select:focus,
  .form-control:focus,
  .btn-light:focus {
    box-shadow: none;
  }

  /* Custom placeholder color */
  .form-control::placeholder {
    color: #999;
  }

  .btn-outline-light {
    border: 1px solid white;
    color: white;
    height: 40px;
    border-radius: 5px;
    font-weight: 500;
  }

  .btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border-color: white;
  }

  .btn {
    padding: 0.5rem 1.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
  }

  .alert {
    position: relative;
    z-index: 1050;
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
            <div class="card-header d-flex justify-content-between align-items-center p-4" style="background-color: #E91E63;">
              <div>
                <h6 class="text-white mb-0">Patient List</h6>
              </div>
              <form method="GET" class="d-flex align-items-center gap-3">
                <select name="location" class="form-select" style="width: 200px; background-color: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                  <option value="0">All Locations</option>
                  <?php 
                  $locations->data_seek(0); // Reset the locations result pointer
                  while($loc = $locations->fetch_assoc()): 
                  ?>
                    <option value="<?php echo $loc['id']; ?>" <?php echo $selectedLocation == $loc['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($loc['municipality'] . ' - ' . $loc['barangay']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
                <input type="text" name="search" class="form-control" placeholder="Search by name..." 
                  value="<?php echo htmlspecialchars($searchName); ?>"
                  style="width: 300px; background-color: white; border: none;">
                <button type="submit" class="btn btn-light">FILTER</button>
                <a href="patients.php" class="btn btn-outline-light">RESET</a>
                <button type="button" class="btn btn-light d-flex align-items-center gap-2" onclick="openAddModal()">
                  <i class="fas fa-plus"></i>
                  <span>PATIENT</span>
                </button>
              </form>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="patientsTable">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Full name
                      </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                        Address</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gender
                      </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age
                      </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                        Registered Since</th>
                      <th class="text-secondary opacity-7"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                        if ($result->num_rows > 0) {
                          $address = '';
                          if (!empty($row["barangay_name"])) {
                              $address .= "Brgy. " . htmlspecialchars($row["barangay_name"]);
                          }
                          if (!empty($row["municipality_name"])) {
                              $address .= (!empty($address) ? ", " : "") . htmlspecialchars($row["municipality_name"]);
                          }
                          if (empty($address) && !empty($row["address"])) {
                              $address = htmlspecialchars($row["address"]);
                          }
                          // Output data of each row
                          while($row = $result->fetch_assoc()) {
                            echo "
                              <tr onclick='showLabResults(".$row["id"].", \"".htmlspecialchars($row["fullname"], ENT_QUOTES)."\")' style='cursor: pointer;'>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["fullname"]."</span></td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>".$address."</span>
                                </td>
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
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addPatientModalLabel">Add Patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
            method="post">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <input type="hidden" class="form-control" id="id" name="id">
                  <div class="form-group">
                    <label for="location">Location</label>
                    <select class="form-control" id="location" name="location" required>
                      <option value="">Choose a Location</option>
                      <?php foreach($locations as $location): ?>
                      <option value="<?php echo $location['id']; ?>">
                        <?php 
                            echo "Brgy. " . $location['barangay'] . ", " . $location['municipality'];
                          ?>
                      </option>
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
                      <option value="<?php echo $physician['id']; ?>">
                        <?php echo $physician['first_name']. " ". $physician['last_name']; ?></option>
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

    <script>
      // Add Patient Modal Functions
      function openAddModal() {
        // Reset the form
        document.getElementById('id').value = '';
        document.getElementById('fullname').value = '';
        document.getElementById('age').value = '';
        document.getElementById('gender').value = '';
        document.getElementById('contact').value = '';
        document.getElementById('address').value = '';
        document.getElementById('height').value = '';
        document.getElementById('dob').value = '';
        document.getElementById('occupation').value = '';
        document.getElementById('phil_health_no').value = '';
        document.getElementById('contact_person').value = '';
        document.getElementById('contact_person_no').value = '';
        document.getElementById('physician').value = '';
        document.getElementById('location').value = '';
        
        // Update modal title and button
        document.getElementById('addPatientModalLabel').textContent = 'Add Patient';
        document.getElementById('form-button').textContent = 'Add Patient';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('addPatientModal'));
        modal.show();
      }

      // Edit Patient Function
      function editPatient(id, data) {
        event.stopPropagation(); // Prevent row click event
        
        // Populate form with patient data
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

        // Update modal title and button
        document.getElementById('addPatientModalLabel').textContent = 'Edit Patient';
        document.getElementById('form-button').textContent = 'Save Changes';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('addPatientModal'));
        modal.show();
      }

      // Show Lab Results Function
      function showLabResults(patientId, patientName) {
        console.log('Loading lab results for:', patientId, patientName);

        // Show the lab results section
        let labResultsSection = document.querySelector('.lab-results-section');
        labResultsSection.style.display = 'block';

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

      // Initialize when document is ready
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips if needed
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      });
    </script>

    <?php include_once('footer.php'); ?>
  </main>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.1.0"></script>
</body>

</html>