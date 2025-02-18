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
$selectedOutcome = isset($_GET['outcome']) ? $_GET['outcome'] : '0';

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
$sql = "SELECT DISTINCT p.*, 
        b.name as barangay_name, 
        m.location as municipality_name,
        lr.treatment_outcome
        FROM patients p 
        LEFT JOIN locations l ON p.location_id = l.id 
        LEFT JOIN municipalities m ON l.municipality_id = m.id 
        LEFT JOIN barangays b ON l.barangay_id = b.id
        LEFT JOIN lab_results lr ON p.id = lr.patient_id
        WHERE 1=1";

if ($selectedMunicipality != '0') {
    $sql .= " AND l.municipality_id = '$selectedMunicipality'";
}

if ($selectedBarangay != '0') {
    $sql .= " AND l.barangay_id = '$selectedBarangay'";
}

if ($selectedGender != '0') {
    $sql .= " AND p.gender = '$selectedGender'";
}

if (!empty($searchName)) {
    $sql .= " AND p.fullname LIKE '%$searchName%'";
}

if ($selectedOutcome != '0') {
    if ($selectedOutcome == 'NO_RESULTS') {
        $sql .= " AND lr.id IS NULL";
    } else {
        $sql .= " AND lr.treatment_outcome = '$selectedOutcome'";
    }
}

$sql .= " ORDER BY p.created_at DESC";
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
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    width: auto;
    max-width: 90%;
    margin: 0 auto;
    padding: 15px;
    border-radius: 5px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
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

  .card-header .form-select,
  .card-header .form-control {
    height: 38px;
    background-color: white;
    border: none;
  }

  .card-header .btn {
    height: 38px;
    padding: 0 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
  }

  .card-header .dropdown-toggle {
    padding: 0 1rem;
    background-color: white;
    color: #333;
    border: none;
  }

  .card-header .dropdown-menu {
    padding: 0.5rem 0;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .card-header .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .card-header .dropdown-item:hover {
    background-color: #f8f9fa;
  }

  /* Table Styles */
  .table {
    border-collapse: separate;
    border-spacing: 0 8px;
    margin-top: -8px;
  }

  .table thead th {
    border: none;
    font-size: 0.75rem;
    padding: 12px 24px;
    text-transform: uppercase;
    font-weight: 700;
    background: transparent;
  }

  .table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s;
  }

  .table tbody tr:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .table tbody td {
    border: none;
    padding: 12px 24px;
    vertical-align: middle;
  }

  /* Status Badge Styles */
  .badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.75rem;
  }

  /* Action Buttons */
  .btn-link {
    color: #344767;
    text-decoration: none;
    font-size: 1rem;
    padding: 0.25rem !important;
    line-height: 1;
    transition: all 0.2s;
    margin: 0 2px;
    border-radius: 4px;
  }

  .btn-link:hover {
    color: #E91E63;
    background-color: rgba(233, 30, 99, 0.1);
  }

  /* Tooltip styles */
  .btn-link[title] {
    position: relative;
  }

  .btn-link[title]:hover:after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 5px;
  }

  /* Add this PHP function for treatment status colors */
  <?php
  function getTreatmentStatusClass($status) {
      switch ($status) {
          case 'CURED':
              return 'bg-success text-white';
          case 'TREATMENT COMPLETED':
              return 'bg-info text-white';
          case 'TREATMENT FAILED':
              return 'bg-danger text-white';
          case 'DIED':
              return 'bg-dark text-white';
          case 'LOST TO FOLLOW UP':
              return 'bg-warning text-dark';
          case 'NOT EVALUATED':
              return 'bg-secondary text-white';
          case 'ON-GOING':
              return 'bg-primary text-white';
          default:
              return 'bg-light text-dark';
      }
  }
  ?>
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
            <div class="card-header d-flex flex-wrap gap-3 p-4" style="background-color: #E91E63;">
              <div class="d-flex align-items-center gap-3">
                <h6 class="text-white mb-0">Patient List</h6>
                <div class="btn-group">
                  <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export me-2"></i>Export Patients
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="#" onclick="exportPatients('current')">
                        <i class="fas fa-filter me-2"></i>Export Current Filter
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#" onclick="exportPatients('all')">
                        <i class="fas fa-users me-2"></i>Export All Patients
                      </a>
                    </li>
                    <?php if(isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2 || $_SESSION['role_id'] == 1): ?>
                    <li>
                      <a class="dropdown-item" href="#" onclick="exportPatients('treatment')">
                        <i class="fas fa-notes-medical me-2"></i>Export with Treatment Details
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>

              <form method="GET" class="d-flex flex-grow-1 gap-2 flex-wrap">
                <div class="d-flex flex-wrap gap-2" style="flex: 1;">
                  <select name="municipality" class="form-select" style="max-width: 200px;">
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

                  <select name="barangay" class="form-select" style="max-width: 200px;">
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

                  <select name="gender" class="form-select" style="max-width: 150px;">
                    <option value="0">All Genders</option>
                    <option value="1" <?php echo $selectedGender == 1 ? 'selected' : ''; ?>>Male</option>
                    <option value="2" <?php echo $selectedGender == 2 ? 'selected' : ''; ?>>Female</option>
                  </select>

                  <select name="outcome" class="form-select" style="max-width: 200px;">
                    <option value="0">All Outcomes</option>
                    <option value="CURED" <?php echo $selectedOutcome == 'CURED' ? 'selected' : ''; ?>>Cured</option>
                    <option value="TREATMENT COMPLETED" <?php echo $selectedOutcome == 'TREATMENT COMPLETED' ? 'selected' : ''; ?>>Treatment Completed</option>
                    <option value="TREATMENT FAILED" <?php echo $selectedOutcome == 'TREATMENT FAILED' ? 'selected' : ''; ?>>Treatment Failed</option>
                    <option value="DIED" <?php echo $selectedOutcome == 'DIED' ? 'selected' : ''; ?>>Died</option>
                    <option value="LOST TO FOLLOW UP" <?php echo $selectedOutcome == 'LOST TO FOLLOW UP' ? 'selected' : ''; ?>>Lost to Follow Up</option>
                    <option value="NOT EVALUATED" <?php echo $selectedOutcome == 'NOT EVALUATED' ? 'selected' : ''; ?>>Not Evaluated</option>
                    <option value="ON-GOING" <?php echo $selectedOutcome == 'ON-GOING' ? 'selected' : ''; ?>>On-going</option>
                    <option value="NO_RESULTS" <?php echo $selectedOutcome == 'NO_RESULTS' ? 'selected' : ''; ?>>No Lab Results</option>
                  </select>

                  <input type="text" name="search" class="form-control" placeholder="Search by name..." 
                         value="<?php echo htmlspecialchars($searchName); ?>" style="flex: 1;">
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-light">FILTER</button>
                  <button type="button" class="btn btn-light d-flex align-items-center gap-2" onclick="openAddModal()">
                    <i class="fas fa-plus"></i>
                    <span>PATIENT</span>
                  </button>
                </div>
              </form>
            </div>
            <div class="card-body px-4 pb-4">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Full Name</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Address</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Gender</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Age</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Registered Since</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Treatment Status</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($patientsData->num_rows > 0): 
                      while($row = $patientsData->fetch_assoc()): 
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
                        
                        // Get lab results status
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM lab_results WHERE patient_id = ?");
                        $stmt->bind_param('i', $row["id"]);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $labResults = $result->fetch_assoc();
                    ?>
                      <tr>
                        <td>
                          <p class="text-sm font-weight-bold mb-0"><?php echo htmlspecialchars($row["fullname"]); ?></p>
                        </td>
                        <td>
                          <p class="text-sm text-secondary mb-0"><?php echo $address; ?></p>
                        </td>
                        <td>
                          <p class="text-sm text-secondary mb-0"><?php echo ($row["gender"] == 1 ? "Male" : "Female"); ?></p>
                        </td>
                        <td>
                          <p class="text-sm text-secondary mb-0"><?php echo htmlspecialchars($row["age"]); ?></p>
                        </td>
                        <td>
                          <p class="text-sm text-secondary mb-0"><?php echo date('Y-m-d H:i:s', strtotime($row["created_at"])); ?></p>
                        </td>
                        <td>
                          <span class="badge badge-sm <?php echo getTreatmentStatusClass($row["treatment_outcome"]); ?>">
                            <?php echo isset($row["treatment_outcome"]) ? htmlspecialchars($row["treatment_outcome"]) : "No Lab Results"; ?>
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-1">
                            <a href="#" onclick="showPatientDetails(<?php echo $row['id']; ?>)" 
                               class="btn btn-link p-1" title="View Details">
                              <i class="fas fa-info-circle"></i>
                            </a>
                            
                            <?php if ($labResults['count'] > 0): ?>
                            <a href="#" onclick="showLabResults(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES); ?>')" 
                               class="btn btn-link p-1" title="Lab Results">
                              <i class="fas fa-flask"></i>
                            </a>
                            <?php endif; ?>
                            
                            <a href="#" onclick="addLogbookEntry(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES); ?>')" 
                               class="btn btn-link p-1" title="Logbook">
                              <i class="fas fa-book"></i>
                            </a>
                            
                            <?php if (in_array(11, $_SESSION['module'])): ?>
                            <a href="#" onclick="editPatient(<?php echo $row['id']; ?>, <?php echo htmlspecialchars(json_encode($row), ENT_QUOTES); ?>)" 
                               class="btn btn-link p-1" title="Edit">
                              <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (in_array(12, $_SESSION['module'])): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this patient?');">
                              <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                              <button type="submit" class="btn btn-link p-1" title="Delete">
                                <i class="fas fa-trash"></i>
                              </button>
                            </form>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php 
                      endwhile;
                    endif; 
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
                    <input type="date" class="form-control" id="dob" name="dob" required>
                  </div>
                  <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" class="form-control" id="age" name="age">
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
                  <!-- <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                  </div> -->
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
      <div class='modal-dialog modal-lg'>
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

    <!-- Logbook Modal -->
    <div class="modal fade" id="logbookModal" tabindex="-1" aria-labelledby="logbookModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="logbookModalLabel">Patient Logbook</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex justify-content-end mb-3">
              <?php if (in_array(23, $_SESSION['module'])): ?>
                <button type="button" class="btn btn-primary" onclick="openAddLogModal()">
                  <i class="fas fa-plus"></i> Add New Log
                </button>
              <?php endif; ?>
            </div>
            <div id="logbookList">
              <!-- Logs will be loaded here dynamically -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add a new modal for adding logs -->
    <div class="modal fade" id="addLogModal" tabindex="-1" aria-labelledby="addLogModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addLogModalLabel">Add New Log Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="logbookForm">
            <div class="modal-body">
              <input type="hidden" id="logbook_patient_id" name="patient_id">
              <div class="form-group mb-3">
                <label for="log_date">Date</label>
                <input type="date" class="form-control" id="log_date" name="log_date" required>
              </div>
              <div class="form-group">
                <label for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>

      var role_id = <?php echo $_SESSION['role_id']; ?>;
      var module_id = <?php echo json_encode($_SESSION['module']); ?>;
      // Add Patient Modal Functions
      function openAddModal() {
        // Reset the form
        document.getElementById('id').value = '';
        document.getElementById('fullname').value = '';
        document.getElementById('age').value = '';
        document.getElementById('gender').value = '';
        document.getElementById('contact').value = '';
        // document.getElementById('address').value = '';
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
        // document.getElementById('address').value = data.address;
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
              const transactions = data.transactions || [];
              
              let content = `
                <div class="row mb-4">
                  <div class="col-md-6">
                    <h6 class="text-sm font-weight-bold mb-3">Personal Information</h6>
                    <p class="text-sm mb-2"><strong>Name:</strong> ${patient.fullname || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Age:</strong> ${patient.age || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Gender:</strong> ${patient.gender == 1 ? 'Male' : 'Female'}</p>
                    <p class="text-sm mb-2"><strong>Contact:</strong> ${patient.contact || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                  </div>
                  <div class="col-md-6">
                    <h6 class="text-sm font-weight-bold mb-3">Medical Information</h6>
                    <p class="text-sm mb-2"><strong>Height:</strong> ${patient.height ? patient.height + ' cm' : 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Date of Birth:</strong> ${patient.dob || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>PhilHealth No:</strong> ${patient.phil_health_no || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Emergency Contact:</strong> ${patient.contact_person || 'N/A'}</p>
                    <p class="text-sm mb-2"><strong>Emergency Contact No:</strong> ${patient.contact_person_no || 'N/A'}</p>
                  </div>
                </div>`;

                // Add Inventory Transaction History section
                content += `
                  <div class="row">
                    <div class="col-12">
                      <h6 class="text-sm font-weight-bold mb-3">Inventory Transaction History</h6>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                          <thead>
                            <tr>
                              <th class="text-xs">Date</th>
                              <th class="text-xs">Type</th>
                              <th class="text-xs">Product</th>
                              <th class="text-xs">Quantity</th>
                              <th class="text-xs">Batch Number</th>
                              <th class="text-xs">Notes</th>
                              <th class="text-xs">Staff</th>
                            </tr>
                          </thead>
                          <tbody>`;

                if (transactions.length > 0) {
                  transactions.forEach(trans => {
                    content += `
                      <tr>
                        <td class="text-xs">${trans.transaction_date}</td>
                        <td class="text-xs">${trans.type}</td>
                        <td class="text-xs">${trans.brand_name} (${trans.generic_name})</td>
                        <td class="text-xs">${trans.quantity} ${trans.unit_of_measure}</td>
                        <td class="text-xs">${trans.batch_number || 'N/A'}</td>
                        <td class="text-xs">${trans.notes || 'N/A'}</td>
                        <td class="text-xs">${trans.staff_name}</td>
                      </tr>`;
                  });
                } else {
                  content += `
                    <tr>
                      <td colspan="7" class="text-center text-xs">No transaction history found</td>
                    </tr>`;
                }

                content += `
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>`;

                document.getElementById('patientDetailsContent').innerHTML = content;
              }
            })
            .catch(error => {
              console.error('Error:', error);
              document.getElementById('patientDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                  Error loading patient details: ${error.message}
                </div>`;
            });

          const modal = new bootstrap.Modal(document.getElementById('patientDetailsModal'));
          modal.show();
      }

      // Add this function to calculate age from date of birth
      function calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        // Adjust age if birthday hasn't occurred this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
          age--;
        }
        
        return age;
      }

      // Add event listener to date of birth input
      document.getElementById('dob').addEventListener('change', function() {
        const dob = this.value;
        if (dob) {
          const age = calculateAge(dob);
          document.getElementById('age').value = age;
        }
      });

      function addLogbookEntry(patientId, patientName) {
        // Store patient ID in a data attribute on the logbook modal
        document.getElementById('logbookModal').setAttribute('data-patient-id', patientId);
        document.getElementById('logbookModalLabel').textContent = `Logbook for ${patientName}`;
        
        // Load existing logs
        loadLogbookEntries(patientId);
        
        // Show the logbook modal
        const modal = new bootstrap.Modal(document.getElementById('logbookModal'));
        modal.show();
      }

      function loadLogbookEntries(patientId) {
        fetch(`get_logbook_entries.php?patient_id=${patientId}`)
          .then(response => response.json())
          .then(data => {
            const logbookList = document.getElementById('logbookList');
            if (data.success && data.entries.length > 0) {
              const entriesHTML = data.entries.map(entry => `
                <div class="card mb-3">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="card-subtitle text-muted">${entry.log_date}</h6>
                      <small class="text-muted">Added by: ${entry.added_by}</small>
                    </div>
                    <p class="card-text">${entry.notes}</p>
                  </div>
                </div>
              `).join('');
              logbookList.innerHTML = entriesHTML;
            } else {
              logbookList.innerHTML = '<div class="alert alert-info">No logbook entries found.</div>';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            document.getElementById('logbookList').innerHTML = '<div class="alert alert-danger">Error loading logbook entries.</div>';
          });
      }

      function openAddLogModal() {
        const patientId = document.getElementById('logbookModal').getAttribute('data-patient-id');
        document.getElementById('logbook_patient_id').value = patientId;
        document.getElementById('log_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('notes').value = '';
        
        // Hide logbook modal and show add log modal
        bootstrap.Modal.getInstance(document.getElementById('logbookModal')).hide();
        const addLogModal = new bootstrap.Modal(document.getElementById('addLogModal'));
        addLogModal.show();
      }

      // Update form submission handler
      document.getElementById('logbookForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (role_id !== 1 && Array.isArray(module_id) && module_id.includes(23)) {
          alert('You are not authorized to add logbook entries.');
          return;
        }
        
        const formData = new FormData(this);
        
        fetch('save_logbook_entry.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Logbook entry saved successfully');
            
            // Hide add log modal
            bootstrap.Modal.getInstance(document.getElementById('addLogModal')).hide();
            
            // Show logbook modal and refresh entries
            const logbookModal = new bootstrap.Modal(document.getElementById('logbookModal'));
            logbookModal.show();
            loadLogbookEntries(formData.get('patient_id'));
          } else {
            alert('Error saving logbook entry: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error saving logbook entry');
        });
      });

      function exportPatients(type) {
        // Get current filter values
        const municipality = document.querySelector('select[name="municipality"]').value;
        const barangay = document.querySelector('select[name="barangay"]').value;
        const gender = document.querySelector('select[name="gender"]').value;
        const outcome = document.querySelector('select[name="outcome"]').value;
        const search = document.querySelector('input[name="search"]').value;

        // Build query parameters
        let params = new URLSearchParams();
        if (type === 'current') {
            params.append('municipality', municipality);
            params.append('barangay', barangay);
            params.append('gender', gender);
            params.append('outcome', outcome);
            params.append('search', search);
        }
        params.append('export_type', type);

        // Open export page in new tab
        window.open(`view_patient_export.php?${params.toString()}`, '_blank');
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