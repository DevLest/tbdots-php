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
  } else {
    if ($fullname && $dob && $location) {
      // Only check for duplicates if we have name, location, and birthday
      $stmt = $conn->prepare("SELECT id FROM patients WHERE fullname = ? AND dob = ? AND location_id = ?");
      $stmt->bind_param('ssi', $fullname, $dob, $location);
      $stmt->execute();
      $result = $stmt->get_result();
      $existingPatient = $result->fetch_assoc();

      if ($existingPatient && (!isset($_POST['id']) || $_POST['id'] != $existingPatient['id'])) {
        echo "<div class='alert alert-danger'>A patient with the same name, birthday, and location already exists.</div>";
        exit; // Stop processing if duplicate found
      }
    }

    $sql = "INSERT INTO patients (
              fullname, 
              age, 
              gender, 
              contact, 
              address, 
              physician_id, 
              location_id,
              height,
              dob,
              occupation,
              phil_health_no,
              contact_person,
              contact_person_no
            ) VALUES (
              ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      "siissiiisssss",
      $fullname,
      $age,
      $gender,
      $contact,
      $address,
      $physician,
      $location,
      $height,
      $dob,
      $occupation,
      $phil_health_no,
      $contact_person,
      $contact_person_no
    );

    if($stmt->execute()) {
      $newPatientId = $conn->insert_id;
      logActivity($conn, $_SESSION['user_id'], 'CREATE', 'patients', $newPatientId, "Added patient: $fullname");
      echo "<div class='alert alert-success'>Patient added successfully.</div>";
    } else {
      echo "<div class='alert alert-danger'>Error adding patient: " . $conn->error . "</div>";
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

// Get selected filters from URL parameters
$selectedMunicipality = isset($_GET['municipality']) ? (int)$_GET['municipality'] : 0;
$selectedBarangay = isset($_GET['barangay']) ? (int)$_GET['barangay'] : 0;
$selectedAge = isset($_GET['age']) ? (int)$_GET['age'] : 0;
$selectedGender = isset($_GET['gender']) ? (int)$_GET['gender'] : 0;

// Build the WHERE clause
$whereConditions = [];
if ($selectedLocation > 0) {
    $whereConditions[] = "p.location_id = $selectedLocation";
}
if ($selectedMunicipality > 0) {
    $whereConditions[] = "m.id = $selectedMunicipality";
}
if ($selectedBarangay > 0) {
    $whereConditions[] = "b.id = $selectedBarangay";
}
if ($selectedAge > 0) {
    $whereConditions[] = "p.age = $selectedAge";
}
if ($selectedGender > 0) {
    $whereConditions[] = "p.gender = $selectedGender";
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
$patientsData = $conn->query($sql);

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

  .treatment-card {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 5px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }
  .treatment-card h5 {
    margin-bottom: 15px;
  }
  .treatment-card p {
    margin-bottom: 10px;
  }

  /* Existing styles remain the same */
  .form-select, .form-control, .btn-light, .btn-outline-light {
    border: 1px solid #ced4da;
    background-color: #fff;
    color: #333;
  }

  .form-select:focus, .form-control:focus, .btn-light:focus, .btn-outline-light:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .btn-light {
    background-color: #f8f9fa;
    color: #333;
  }

  .btn-outline-light {
    border-color: #f8f9fa;
    color: #333;
  }

  .btn-outline-light:hover {
    background-color: #e2e6ea;
    color: #333;
  }

  .card-header {
    background-color: #E91E63;
    color: #fff;
  }

  .text-white {
    color: #fff !important;
  }

  /* New responsive styles */
  @media (max-width: 1200px) {
    .form-select, .form-control {
      min-width: 150px !important;
    }
  }

  @media (max-width: 768px) {
    .d-flex.gap-2.flex-nowrap {
      flex-wrap: wrap !important;
      justify-content: flex-start;
    }
    
    .form-select, .form-control {
      min-width: 100% !important;
    }
    
    .btn {
      flex: 1;
      min-width: auto !important;
    }
  }

  /* Ensure consistent height for all form elements */
  .form-select, .form-control, .btn {
    height: 38px;
    padding: 0.375rem 0.75rem;
  }

  /* Prevent button text from wrapping */
  .btn {
    white-space: nowrap;
  }

  /* Updated responsive styles */
  @media (max-width: 1400px) {
    .d-flex.gap-2 {
      flex-wrap: wrap;
    }
    
    .form-select {
      flex: 0 0 auto;
    }
    
    .form-control {
      flex: 1 1 200px;
    }
  }

  @media (max-width: 992px) {
    .d-flex.gap-2 > div {
      width: 100%;
    }
    
    .form-select, .form-control {
      width: auto;
      flex: 1;
    }
    
    .d-flex.gap-2 .btn {
      flex: 1;
    }
  }

  /* Improved spacing */
  .gap-2 {
    gap: 0.75rem !important;
  }

  /* Card header styling */
  .card-header {
    background-color: #E91E63;
    color: #fff;
    padding: 1rem;
  }

  /* Form elements hover/focus states */
  .form-select:hover, .form-control:hover {
    border-color: #adb5bd;
  }

  .form-select:focus, .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .patient-details {
    padding: 20px;
  }

  .details-section {
    margin-bottom: 30px;
  }

  .section-title {
    color: #344767;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #E91E63;
  }

  .details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
  }

  .detail-item {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 8px;
  }

  .detail-item label {
    display: block;
    color: #7b809a;
    font-size: 0.875rem;
    margin-bottom: 4px;
  }

  .detail-item span {
    color: #344767;
    font-weight: 500;
    font-size: 1rem;
  }

  #patientDetailsModal .modal-dialog {
    max-width: 800px;
  }

  #patientDetailsModal .modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  #patientDetailsModal .modal-header {
    background-color: #E91E63;
    color: white;
    border-radius: 15px 15px 0 0;
  }

  #patientDetailsModal .modal-title {
    font-weight: 600;
  }

  #patientDetailsModal .btn-close {
    color: white;
  }

  #patientDetailsModal .modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
  }

  #patientDetailsModal .btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 8px 20px;
    font-weight: 500;
  }

  #patientDetailsModal .btn-secondary:hover {
    background-color: #5a6268;
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
              <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex gap-2" style="flex: 1;">
                    <div class="d-flex flex-wrap gap-2 align-items-center" style="flex: 1;">
                        <select name="municipality" class="form-select" style="min-width: 180px; max-width: 200px;">
                            <option value="0">All Municipalities</option>
                            <?php 
                            $municipalities = $conn->query("SELECT id, location FROM municipalities");
                            while($municipality = $municipalities->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $municipality['id']; ?>" <?php echo $selectedMunicipality == $municipality['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($municipality['location']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <select name="barangay" class="form-select" style="min-width: 180px; max-width: 200px;">
                            <option value="0">All Barangays</option>
                            <?php 
                            $barangays = $conn->query("SELECT id, name FROM barangays");
                            while($barangay = $barangays->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $barangay['id']; ?>" <?php echo $selectedBarangay == $barangay['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($barangay['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <select name="gender" class="form-select" style="min-width: 140px; max-width: 160px;">
                            <option value="0">All Genders</option>
                            <option value="1" <?php echo $selectedGender == 1 ? 'selected' : ''; ?>>Male</option>
                            <option value="2" <?php echo $selectedGender == 2 ? 'selected' : ''; ?>>Female</option>
                        </select>
                        
                        <input type="text" name="search" class="form-control" placeholder="Search by name..." 
                               value="<?php echo htmlspecialchars($searchName); ?>" style="min-width: 200px; flex: 1;">
                    </div>

                    <div class="d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-light">FILTER</button>
                        <!-- <a href="patients.php" class="btn btn-outline-light">RESET</a> -->
                        <button type="button" class="btn btn-light d-flex align-items-center gap-2" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            <span>PATIENT</span>
                        </button>
                    </div>
                </div>
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
                        if ($patientsData->num_rows > 0) {
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
                          while($row = $patientsData->fetch_assoc()) {
                            // Check if lab results exist for the patient
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM lab_results WHERE patient_id = ?");
                            $stmt->bind_param('i', $row["id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $labResults = $result->fetch_assoc();

                            echo "
                              <tr>
                                <td><span class='text-secondary text-xs font-weight-bold'>".$row["fullname"]."</span></td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>".$address."</span>
                                </td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".($row["gender"] == 1 ? "Male" : "Female")."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["age"]."</span></td>
                                <td class='text-center'><span class='text-secondary text-xs font-weight-bold'>".$row["created_at"]."</span></td>
                                <td class='align-middle'>
                                  <button onclick='showPatientDetails(".$row["id"].")' class='btn btn-link text-secondary mb-0'>
                                    <i class='fa fa-user text-xs'></i> Details
                                  </button>
                                  " . ($labResults['count'] > 0 ? "
                                  <button onclick='showLabResults(".$row["id"].", \"".htmlspecialchars($row["fullname"], ENT_QUOTES)."\")' class='btn btn-link text-secondary mb-0'>
                                    <i class='fa fa-flask text-xs'></i> Lab Results
                                  </button>" : "") . "
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

    <!-- Treatment Details Modal -->
    <div class="modal fade" id="treatmentDetailsModal" tabindex="-1" aria-labelledby="treatmentDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="treatmentDetailsModalLabel">Treatment Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="treatmentDetailsContent">
            <!-- Content will be loaded dynamically -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Patient Details Modal -->
    <div class='modal fade' id='patientDetailsModal' tabindex='-1' aria-labelledby='patientDetailsModalLabel' aria-hidden='true'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <h5 class='modal-title' id='patientDetailsModalLabel'>Patient Details</h5>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body' id='patientDetailsContent'>
            <!-- Content will be loaded dynamically -->
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
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

        fetch(`get_lab_results_card.php?id=${patientId}`)
          .then(response => response.json())
          .then(data => {
            console.log('Response data:', data);
            if (data.success) {
              const labData = data.data;
              let content = `
                <div class="card mb-3">
                  <div class="card-header">Basic Information</div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group mb-2">
                          <label>TB Case Number</label>
                          <input type="text" class="form-control" value="${labData.case_number || 'N/A'}" readonly>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group mb-2">
                          <label>Date Card Opened</label>
                          <input type="text" class="form-control" value="${labData.date_opened || 'N/A'}" readonly>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group mb-2">
                          <label>Region/Province</label>
                          <input type="text" class="form-control" value="${labData.region_province || 'N/A'}" readonly>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group mb-2">
                          <label>Patient Name</label>
                          <input type="text" class="form-control" value="${labData.fullname || 'N/A'}" readonly>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group mb-2">
                          <label>Age</label>
                          <input type="text" class="form-control" value="${labData.age || 'N/A'}" readonly>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group mb-2">
                          <label>Sex</label>
                          <input type="text" class="form-control" value="${labData.sex === 1 ? 'Male' : 'Female'}" readonly>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card mb-3">
                  <div class="card-header">Diagnostic Tests</div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group mb-2">
                          <label>Tuberculin Skin Test (TST)</label>
                          <input type="text" class="form-control" value="${labData.tst_result || 'N/A'}" readonly>
                        </div>
                        <div class="form-group mb-2">
                          <label>CXR Findings</label>
                          <textarea class="form-control" readonly rows="2">${labData.cxr_findings || 'N/A'}</textarea>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group mb-2">
                          <label>Bacteriological Status</label>
                          <input type="text" class="form-control" value="${labData.bacteriological_status || 'N/A'}" readonly>
                        </div>
                        <div class="form-group mb-2">
                          <label>Classification of TB Disease</label>
                          <input type="text" class="form-control" value="${labData.tb_classification || 'N/A'}" readonly>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group mb-2">
                          <label>Treatment Details</label>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                              <tr>
                                <th>Diagnosis</th>
                                <td>${labData.diagnosis || 'N/A'}</td>
                              </tr>
                              <tr>
                                <th>Registration Group</th>
                                <td>${labData.registration_group || 'N/A'}</td>
                              </tr>
                              <tr>
                                <th>Treatment Regimen</th>
                                <td>${labData.treatment_regimen || 'N/A'}</td>
                              </tr>
                              <tr>
                                <th>Treatment Started Date</th>
                                <td>${labData.treatment_started_date || 'N/A'}</td>
                              </tr>
                              <tr>
                                <th>Treatment Outcome</th>
                                <td>${labData.treatment_outcome || 'N/A'}</td>
                              </tr>
                              <tr>
                                <th>Treatment Outcome Date</th>
                                <td>${labData.treatment_outcome_date || 'N/A'}</td>
                              </tr>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>`;

              // Update both elements with the content
              const cardContent = document.querySelector('.card-body#treatmentDetailsCard');
              const modalContent = document.querySelector('.modal-body#treatmentDetailsContent');
              
              if (cardContent) cardContent.innerHTML = content;
              if (modalContent) modalContent.innerHTML = content;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            const errorHTML = `
              <div class="alert alert-danger">
                Error loading lab results: ${error.message}
              </div>
            `;
            const cardContent = document.querySelector('.card-body#treatmentDetailsCard');
            const modalContent = document.querySelector('.modal-body#treatmentDetailsContent');
            
            if (cardContent) cardContent.innerHTML = errorHTML;
            if (modalContent) modalContent.innerHTML = errorHTML;
          });

        const modal = new bootstrap.Modal(document.getElementById('treatmentDetailsModal'));
        modal.show();
      }

      // Initialize when document is ready
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips if needed
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      });

      // Add this function to check for duplicate patients
      async function checkDuplicatePatient() {
          const fullname = document.getElementById('fullname').value;
          const dob = document.getElementById('dob').value;
          const location = document.getElementById('location').value;
          const id = document.getElementById('id').value;

          // Only check for duplicates if all three fields are filled
          if (!fullname || !location || !dob) {
              return true; // Allow form submission if any required field is empty
          }

          const formData = new FormData();
          formData.append('check_duplicate', '1');
          formData.append('fullname', fullname);
          formData.append('dob', dob);
          formData.append('location', location);
          if (id) formData.append('id', id);

          try {
              const response = await fetch('check_duplicate_patient.php', {
                  method: 'POST',
                  body: formData
              });
              const data = await response.json();
              if (data.exists) {
                  alert('A patient with the same name, birthday, and location already exists.');
                  return false;
              }
              return true;
          } catch (error) {
              console.error('Error checking duplicate:', error);
              return true; // Allow form submission on error
          }
      }

      // Form submission handler
      document.querySelector('form[role="form"]').addEventListener('submit', async function(e) {
          e.preventDefault();
          
          // Basic validation for required fields
          const fullname = document.getElementById('fullname').value;
          const location = document.getElementById('location').value;
          
          if (!fullname || !location) {
              alert('Please fill in all required fields (Name and Location).');
              return;
          }
          
          // Only check for duplicates if we have a birthday
          if (document.getElementById('dob').value) {
              const isValid = await checkDuplicatePatient();
              if (!isValid) return;
          }
          
          this.submit();
      });

      function showPatientDetails(patientId) {
        fetch(`get_patient_details.php?id=${patientId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const patient = data.data;
              const content = `
                <div class="patient-details">
                  <div class="details-section">
                    <h6 class="section-title">Personal Information</h6>
                    <div class="details-grid">
                      <div class="detail-item">
                        <label>Name:</label>
                        <span>${patient.fullname}</span>
                      </div>
                      <div class="detail-item">
                        <label>Age:</label>
                        <span>${patient.age}</span>
                      </div>
                      <div class="detail-item">
                        <label>Gender:</label>
                        <span>${patient.gender == 1 ? 'Male' : 'Female'}</span>
                      </div>
                      <div class="detail-item">
                        <label>Date of Birth:</label>
                        <span>${patient.dob || 'N/A'}</span>
                      </div>
                      <div class="detail-item">
                        <label>Height:</label>
                        <span>${patient.height ? patient.height + ' cm' : 'N/A'}</span>
                      </div>
                      <div class="detail-item">
                        <label>Occupation:</label>
                        <span>${patient.occupation || 'N/A'}</span>
                      </div>
                    </div>
                  </div>

                  <div class="details-section">
                    <h6 class="section-title">Contact Information</h6>
                    <div class="details-grid">
                      <div class="detail-item">
                        <label>Contact Number:</label>
                        <span>${patient.contact || 'N/A'}</span>
                      </div>
                      <div class="detail-item">
                        <label>Address:</label>
                        <span>${patient.address || 'N/A'}</span>
                      </div>
                      <div class="detail-item">
                        <label>PhilHealth Number:</label>
                        <span>${patient.phil_health_no || 'N/A'}</span>
                      </div>
                    </div>
                  </div>

                  <div class="details-section">
                    <h6 class="section-title">Emergency Contact</h6>
                    <div class="details-grid">
                      <div class="detail-item">
                        <label>Contact Person:</label>
                        <span>${patient.contact_person || 'N/A'}</span>
                      </div>
                      <div class="detail-item">
                        <label>Contact Number:</label>
                        <span>${patient.contact_person_no || 'N/A'}</span>
                      </div>
                    </div>
                  </div>
                </div>
              `;
              document.getElementById('patientDetailsContent').innerHTML = content;
              const modal = new bootstrap.Modal(document.getElementById('patientDetailsModal'));
              modal.show();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            document.getElementById('patientDetailsContent').innerHTML = `
              <div class='alert alert-danger'>
                Error loading patient details: ${error.message}
              </div>
            `;
          });
      }
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