<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
include_once('head.php');

// Consolidate all queries at the top
$patientsSqlList = "SELECT 
                      t.id,
                      t.case_number,
                      p.fullname,
                      p.gender,
                      p.age,
                      t.bacteriological_status,
                      t.diagnosis,
                      t.treatment_regimen,
                      t.treatment_outcome,
                      t.created_at
                  FROM lab_results t
                  JOIN patients p ON t.patient_id = p.id
                  ORDER BY t.created_at DESC";
                  
$patientListReturns = $conn->query($patientsSqlList);
$allPatientsList = $patientListReturns->fetch_all(MYSQLI_ASSOC); // Store all results in array

$patientsSql = "SELECT p.*, m.location as location_name, u.first_name as physician_name,
    lr.case_number, lr.date_opened, lr.region_province, lr.facility_name,
    lr.source_of_patient, lr.bacteriological_status, lr.tb_classification,
    lr.diagnosis, lr.registration_group, lr.treatment_regimen,
    lr.treatment_started_date, lr.treatment_outcome, lr.treatment_outcome_date
    FROM patients p 
    LEFT JOIN municipalities m ON p.location_id = m.id 
    LEFT JOIN users u ON p.physician_id = u.id
    LEFT JOIN lab_results lr ON p.lab_results_id = lr.id";

$patientReturns = $conn->query($patientsSql);
$allPatients = $patientReturns->fetch_all(MYSQLI_ASSOC); // Store all results in array

$locationsSql = "SELECT 
    l.id,
    CONCAT('Brgy. ', b.name, ', ', m.location) as name 
FROM locations l
JOIN municipalities m ON l.municipality_id = m.id
JOIN barangays b ON l.barangay_id = b.id
ORDER BY m.location, b.name";

$locations = $conn->query($locationsSql);

$physiciansSql = "SELECT * FROM users WHERE role = 3";
$physicians = $conn->query($physiciansSql);

// Add this query near the top with other queries
$lastCaseNumberSql = "SELECT MAX(CAST(SUBSTRING(case_number, 5) AS UNSIGNED)) as last_number 
                      FROM lab_results 
                      WHERE case_number LIKE CONCAT(YEAR(CURRENT_DATE), '-%')";
$lastCaseResult = $conn->query($lastCaseNumberSql);
$lastNumber = $lastCaseResult->fetch_assoc()['last_number'] ?? 0;
$newCaseNumber = date('Y') . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle AJAX update request
    if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['ajax_update'])) {
        try {
            $data = $_POST;
            
            // Handle empty date fields
            $treatment_started_date = !empty($data['treatment_started_date']) ? $data['treatment_started_date'] : null;
            $treatment_outcome_date = !empty($data['treatment_outcome_date']) ? $data['treatment_outcome_date'] : null;
            $date_opened = !empty($data['date_opened']) ? $data['date_opened'] : null;

            $sql = "UPDATE lab_results SET 
                    case_number = ?,
                    date_opened = ?,
                    region_province = ?,
                    facility_name = ?,
                    source_of_patient = ?,
                    bacteriological_status = ?,
                    tb_classification = ?,
                    diagnosis = ?,
                    registration_group = ?,
                    treatment_regimen = ?,
                    treatment_started_date = ?,
                    treatment_outcome = ?,
                    treatment_outcome_date = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssssssssssi',
                $data['case_number'],
                $date_opened,
                $data['region_province'],
                $data['facility_name'],
                $data['source_of_patient'],
                $data['bacteriological_status'],
                $data['tb_classification'],
                $data['diagnosis'],
                $data['registration_group'],
                $data['treatment_regimen'],
                $treatment_started_date,
                $data['treatment_outcome'],
                $treatment_outcome_date,
                $data['id']
            );

            if (!$stmt->execute()) {
                throw new Exception('Error updating laboratory record: ' . $stmt->error);
            }

            // Log the activity
            $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details) 
                    VALUES (?, 'UPDATE', 'lab_results', ?, ?)";
            $stmt = $conn->prepare($sql);
            $details = "Updated laboratory record #" . $data['id'];
            $stmt->bind_param('iis', $_SESSION['user_id'], $data['id'], $details);
            $stmt->execute();

            echo json_encode(['success' => true]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    if(isset($_POST['case_number'])) {
        try {
            $conn->begin_transaction();

            // Map the form values to database enum values
            $bacteriological_map = [
                'confirmed' => 'Bacteriologically Confirmed',
                'clinically' => 'Clinically Diagnosed'
            ];

            $tb_classification_map = [
                'pulmonary' => 'Pulmonary',
                'extra_pulmonary' => 'Extra Pulmonary'
            ];

            $diagnosis_map = [
                'TB DISEASE' => 'TB DISEASE',
                'TB INFECTION' => 'TB INFECTION',
                'TB EXPOSURE' => 'TB EXPOSURE'
            ];

            $registration_group_map = [
                'New' => 'New',
                'Relapse' => 'Relapse',
                'Treatment after Failure' => 'Treatment after Failure',
                'TALF' => 'TALF',
                'PTOU' => 'PTOU',
                'Other' => 'Other'
            ];

            $treatment_outcome_map = [
                'CURED' => 'CURED',
                'TREATMENT COMPLETED' => 'TREATMENT COMPLETED',
                'TREATMENT FAILED' => 'TREATMENT FAILED',
                'DIED' => 'DIED',
                'LOST TO FOLLOW UP' => 'LOST TO FOLLOW UP',
                'NOT EVALUATED' => 'NOT EVALUATED'
            ];

            // Prepare all variables before binding
            $case_number = $_POST['case_number'];
            $date_opened = $_POST['date_opened'];
            $region_province = $_POST['region_province'] ?? null;
            $facility_name = $_POST['facility_name'] ?? null;
            $patient_id = (int)$_POST['patient_id'];
            $physician_id = (int)$_SESSION['user_id'];
            $source_patient = $_POST['source_of_patient'] ?? null;
            
            // Convert to proper enum values
            $bact_status = isset($_POST['bacteriological_status']) && $_POST['bacteriological_status'] !== 'Select Bacteriological Status' 
                ? $bacteriological_map[$_POST['bacteriological_status']] 
                : null;
            
            $tb_class = isset($_POST['tb_classification']) && $_POST['tb_classification'] !== 'Select Classification of TB Disease'
                ? $tb_classification_map[$_POST['tb_classification']] 
                : null;
            
            $diagnosis = isset($_POST['diagnosis']) && $_POST['diagnosis'] !== 'Select Diagnosis'
                ? $diagnosis_map[$_POST['diagnosis']] 
                : null;
            
            $reg_group = isset($_POST['registration_group']) 
                ? $registration_group_map[$_POST['registration_group']] 
                : null;
            
            $treatment_reg = $_POST['treatment_regimen'] ?? null;
            $treatment_start = !empty($_POST['treatment_started_date']) ? $_POST['treatment_started_date'] : null;
            $treatment_outcome = isset($_POST['treatment_outcome']) 
                ? $treatment_outcome_map[$_POST['treatment_outcome']] 
                : null;
            $outcome_date = !empty($_POST['treatment_outcome_date']) ? $_POST['treatment_outcome_date'] : null;

            // Debug the transformed values
            error_log("Transformed values:");
            error_log("bacteriological_status: " . $bact_status);
            error_log("tb_classification: " . $tb_class);

            $sql = "INSERT INTO lab_results (
                case_number, 
                date_opened, 
                region_province, 
                facility_name, 
                patient_id, 
                physician_id, 
                source_of_patient, 
                bacteriological_status,
                tb_classification, 
                diagnosis, 
                registration_group, 
                treatment_regimen,
                treatment_started_date, 
                treatment_outcome, 
                treatment_outcome_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            
            // Fix: Use proper type definition for integers
            $stmt->bind_param('ssssiisssssssss', 
                $case_number,
                $date_opened,
                $region_province,
                $facility_name,
                $patient_id,
                $physician_id,
                $source_patient,
                $bact_status,
                $tb_class,
                $diagnosis,
                $reg_group,
                $treatment_reg,
                $treatment_start,
                $treatment_outcome,
                $outcome_date
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $treatment_card_id = $conn->insert_id;

            // Save household members only if there's valid data
            if (!empty($_POST['household_name']) && is_array($_POST['household_name'])) {
                foreach ($_POST['household_name'] as $key => $name) {
                    if (!empty($name)) {
                        $householdSql = "INSERT INTO household_members (treatment_card_id, first_name, age, screened) 
                                       VALUES (?, ?, ?, ?)";
                        $householdStmt = $conn->prepare($householdSql);
                        $age = $_POST['household_age'][$key] ?? null;
                        $screened = isset($_POST['household_screened'][$key]) ? 1 : 0;
                        $householdStmt->bind_param('isis', $treatment_card_id, $name, $age, $screened);
                        
                        if (!$householdStmt->execute()) {
                            throw new Exception("Error saving household member: " . $householdStmt->error);
                        }
                    }
                }
            }

            $conn->commit();
            $_SESSION['success'] = "Treatment card and related records saved successfully.";
            header("Location: laboratory.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in laboratory.php: " . $e->getMessage());
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: laboratory.php");
            exit;
        }
    }
}
?>
<!-- Add this in the head section or in your CSS file -->
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

  .modal-body {
    padding: 20px;
    max-height: 80vh;
    overflow-y: auto;
  }

  .card {
    margin-bottom: 1.5rem;
    border: 1px solid #dee2e6;
  }

  .card-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #dee2e6;
  }

  .card-body {
    padding: 1.25rem;
  }

  .table {
    margin-bottom: 0;
  }

  .table td,
  .table th {
    padding: 0.5rem;
    vertical-align: middle;
  }

  /* Custom styling for checkboxes */
  .form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.25em;
    border: 1px solid #ced4da;
  }

  /* Improve spacing between sections */
  .row {
    margin-bottom: 1rem;
  }

  /* Make labels more visible */
  label {
    font-weight: 500;
    color: #212529;
    margin-bottom: 0.5rem;
  }

  /* Style select boxes consistently */
  select.form-control {
    appearance: auto;
    -webkit-appearance: auto;
    -moz-appearance: auto;
  }

  /* Add some hover effect to buttons */
  .btn {
    transition: all 0.2s;
  }

  .btn:hover {
    transform: translateY(-1px);
  }

  /* Style the scrollbar */
  .modal-body::-webkit-scrollbar {
    width: 8px;
  }

  .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }

  .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
  }

  .form-check-inline {
    margin-right: 1rem;
  }

  .form-check-label {
    margin-bottom: 0;
    margin-left: 0.25rem;
  }

  .form-group label {
    margin-bottom: 0;
  }

  /* Optional: make select boxes a bit smaller to fit better in one line */
  .form-control {
    padding: 0.25rem 0.5rem;
    height: auto;
  }

  /* Add these styles to fix the sidebar overlapping */
  .modal {
    z-index: 1060 !important;
    /* Higher than sidebar */
  }

  /* Adjust sidebar z-index */
  .sidenav {
    z-index: 1040 !important;
    /* Below modal and backdrop */
  }

  /* Make sure modal is properly positioned */
  .modal-dialog {
    margin: 1.75rem auto;
    max-width: 95%;
    position: relative;
  }

  /* Additional styling to ensure modal content is visible */
  .modal-content {
    position: relative;
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  }

  /* Ensure proper spacing from top */
  .modal.show {
    padding-left: 0 !important;
    /* Override any default padding that might cause issues */
  }

  /* If using Material Dashboard, add this */
  .g-sidenav-show {
    overflow-x: hidden;
  }

  /* Ensure modal is scrollable on smaller screens */
  @media (max-width: 768px) {
    .modal-dialog {
      margin: 0.5rem;
      max-width: calc(100% - 1rem);
    }
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
              <div
                class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                <h6 class="text-white text-capitalize ps-3 mb-0">Laboratory Result</h6>
                <?php if(isset($_SESSION['module']) && in_array(10, $_SESSION['module'])): ?>
                <button type="button" class="btn btn-light text-capitalize me-3" data-bs-toggle="modal"
                  data-bs-target="#addLaboratoryModal">
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
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Case Number</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Patient Name</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gender</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bacteriological Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Diagnosis</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Treatment Regimen</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Treatment Outcome</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date Added</th>
                      <th class="text-secondary opacity-7">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach($allPatientsList as $patientRow):
                            echo "<tr>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>{$patientRow['case_number']}</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>{$patientRow['fullname']}</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . ($patientRow['gender'] == 1 ? 'Male' : 'Female') . "</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>{$patientRow['age']}</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . ($patientRow['bacteriological_status'] ?? 'N/A') . "</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . ($patientRow['diagnosis'] ?? 'N/A') . "</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . ($patientRow['treatment_regimen'] ?? 'N/A') . "</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . ($patientRow['treatment_outcome'] ?? 'N/A') . "</span>
                                </td>
                                <td class='text-center'>
                                    <span class='text-secondary text-xs font-weight-bold'>" . date('Y-m-d', strtotime($patientRow['created_at'])) . "</span>
                                </td>
                                <td class='align-middle'>
                                    <button type='button' class='btn btn-sm btn-info' onclick='editLabRecord({$patientRow["id"]})'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <button type='button' class='btn btn-sm btn-info' 
                                            onclick='viewTreatmentCard({$patientRow['id']})'>
                                        View Details
                                    </button>
                                </td>
                            </tr>";
                      endforeach;
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
    <div class="modal fade" id="addLaboratoryModal" tabindex="-1" aria-labelledby="addLaboratoryModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addLaboratoryModalLabel">TB Treatment Card</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="modal-body">
              <div class="card mb-3">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group mb-2">
                        <label>TB Case Number</label>
                          <input type="text" class="form-control" name="case_number" value="<?php echo $newCaseNumber; ?>" readonly>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group mb-2">
                        <label>Date Card Opened</label>
                        <input type="date" class="form-control" name="date_opened" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group mb-2">
                        <label>Region/Province</label>
                        <input type="text" class="form-control" name="region_province">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Patient Name</label>
                        <select class="form-control" name="patient_id" id="patient_select" required>
                          <option value="">Select Patient</option>
                          <?php 
                          if (!empty($allPatients)) {
                              foreach($allPatients as $patientRow): 
                                  $dataAttributes = [
                                      'data-location' => $patientRow['location_name'],
                                      'data-physician' => $patientRow['physician_name'],
                                      'data-age' => $patientRow['age'],
                                      'data-sex' => $patientRow['gender'],
                                      'data-address' => $patientRow['address'],
                                      'data-contact' => $patientRow['contact'],
                                      'data-dob' => $patientRow['dob'],
                                      'data-height' => $patientRow['height'],
                                      'data-bcg-scar' => $patientRow['bcg_scar'],
                                      'data-occupation' => $patientRow['occupation'],
                                      'data-phil-health-no' => $patientRow['phil_health_no'],
                                      'data-contact-person' => $patientRow['contact_person'],
                                      'data-contact-person-no' => $patientRow['contact_person_no']
                                  ];
                                  
                                  $dataAttributesString = '';
                                  foreach($dataAttributes as $key => $value) {
                                      $dataAttributesString .= ' ' . $key . '="' . htmlspecialchars($value ?? '') . '"';
                                  }
                          ?>
                                  <option value="<?php echo $patientRow['id']; ?>"<?php echo $dataAttributesString; ?> onclick="populatePatientData(this)">
                                      <?php echo htmlspecialchars($patientRow['fullname']); ?>
                                  </option>
                          <?php 
                              endforeach;
                          } else {
                              echo "<option>No patients found</option>";
                          }
                          ?>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Complete Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="row">
                        <div class="col-6">
                          <div class="form-group mb-2">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="dob">
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-group mb-2">
                            <label>Age</label>
                            <input type="number" class="form-control" name="age">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-6">
                          <div class="form-group mb-2">
                            <label>Sex</label>
                            <select class="form-control" name="sex">
                              <option value="M">Male</option>
                              <option value="F">Female</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-group mb-2">
                            <label>Height (cm)</label>
                            <input type="number" class="form-control" name="height">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row mt-2">
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>BCG Scar</label>
                        <select class="form-control" name="bcg_scar">
                          <option value="Yes">Yes</option>
                          <option value="No">No</option>
                          <option value="Doubtful">Doubtful</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Source of Patient</label>
                        <select class="form-control" name="source_of_patient">
                          <option value="Public Health Center">Public Health Center</option>
                          <option value="Other Health Facility">Other Health Facility</option>
                          <option value="Private Hospital/Clinics/Physicians/NGOs">Private
                            Hospital/Clinics/Physicians/NGOs</option>
                          <option value="Community">Community</option>
                        </select>
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
                        <input type="text" class="form-control" name="tst_result">
                      </div>
                      <div class="form-group mb-2">
                        <label>CXR Findings</label>
                        <textarea class="form-control" name="cxr_findings" rows="2"></textarea>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Bacteriological Status</label>
                        <select name="bacteriological_status" class="form-control">
                            <option>Select Bacteriological Status</option>
                            <option value="confirmed">Bacteriologically Confirmed</option>
                            <option value="clinically">Clinically Diagnosed</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Classification of TB Disease</label>
                        <select name="tb_classification" class="form-control">
                            <option>Select Classification of TB Disease</option>
                            <option value="pulmonary">Pulmonary</option>
                            <option value="extra_pulmonary">Extra Pulmonary</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group mb-2">
                        <label>DSSM/XPERT MTB/RIF Results Record</label>
                        <table class="table table-bordered">
                          <thead>
                            <tr>
                              <th>Month</th>
                              <th>Due Date</th>
                              <th>Date Examined</th>
                              <th>Result</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php for($i = 0; $i <= 7; $i++): ?>
                            <tr>
                              <td><?php echo $i; ?></td>
                              <td><input type="date" class="form-control" name="dssm_due_date[]"></td>
                              <td><input type="date" class="form-control" name="dssm_exam_date[]"></td>
                              <td><input type="text" class="form-control" name="dssm_result[]"></td>
                            </tr>
                            <?php endfor; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Other Patient Details Section -->
              <div class="card mb-3">
                <div class="card-header">Other Patient Details</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Occupation</label>
                        <input type="text" class="form-control" name="occupation">
                      </div>
                      <div class="form-group mb-2">
                        <label>PhilHealth Number</label>
                        <input type="text" class="form-control" name="phil_health_no">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Contact Person</label>
                        <input type="text" class="form-control" name="contact_person">
                      </div>
                      <div class="form-group mb-2">
                        <label>Contact Number</label>
                        <input type="text" class="form-control" name="contact_number">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Household Members Section -->
              <div class="card mb-3">
                <div class="card-header">Household Members</div>
                <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>First Name</th>
                        <th>Age</th>
                        <th>Screened</th>
                        <th><button type="button" class="btn btn-sm btn-primary" onclick="addHouseholdMember()">Add
                            Member</button></th>
                      </tr>
                    </thead>
                    <tbody id="householdMembersTable">
                      <tr>
                        <td><input type="text" class="form-control" name="household_name[]"></td>
                        <td><input type="number" class="form-control" name="household_age[]"></td>
                        <td><input type="checkbox" name="household_screened[]"></td>
                        <td><button type="button" class="btn btn-sm btn-danger"
                            onclick="this.parentElement.parentElement.remove()">Remove</button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- History of Anti-TB Drug Intake Section -->
              <div class="card mb-3">
                <div class="card-header">History of Anti-TB Drug Intake</div>
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-md-3">
                      <div class="form-group mb-0">
                        <label class="me-2">History:</label>
                        <select class="form-control" name="drug_history">
                          <option>Select History of Anti-TB Drug Intake</option>
                          <option value="No">No</option>
                          <option value="Yes">Yes</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group mb-0">
                        <label class="me-2">Duration:</label>
                        <select class="form-control" name="drug_duration">
                          <option>Select Duration of Anti-TB Drug Intake</option>
                          <option value="less than 1 mo">Less than 1 month</option>
                          <option value="1 mo or more">1 month or more</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group mb-0 d-flex align-items-center">
                        <label class="me-3">Drugs Taken:</label>
                        <div class="form-check form-check-inline">
                          <input type="checkbox" class="form-check-input" name="drugs_taken[]" value="H">
                          <label class="form-check-label">H</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input type="checkbox" class="form-check-input" name="drugs_taken[]" value="R">
                          <label class="form-check-label">R</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input type="checkbox" class="form-check-input" name="drugs_taken[]" value="Z">
                          <label class="form-check-label">Z</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input type="checkbox" class="form-check-input" name="drugs_taken[]" value="E">
                          <label class="form-check-label">E</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input type="checkbox" class="form-check-input" name="drugs_taken[]" value="S">
                          <label class="form-check-label">S</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add to Diagnostic Tests Section -->
              <div class="form-group mb-2">
                <label>Other Exam</label>
                <input type="text" class="form-control" name="other_exam">
                <input type="date" class="form-control mt-2" name="other_exam_date">
              </div>

              <div class="form-group mb-2">
                <label>TBDC</label>
                <input type="text" class="form-control" name="tbdc">
              </div>

              <!-- Treatment Details Section -->
              <div class="card mb-3">
                <div class="card-header">Treatment Details</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Diagnosis</label>
                        <select name="diagnosis" class="form-control">
                          <option>Select Diagnosis</option>
                          <option value="TB DISEASE">TB DISEASE</option>
                          <option value="TB INFECTION">TB INFECTION</option>
                          <option value="TB EXPOSURE">TB EXPOSURE</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Registration Group</label>
                        <select name="registration_group" class="form-control">
                          <option value="New">New</option>
                          <option value="Relapse">Relapse</option>
                          <option value="Treatment after Failure">Treatment after Failure</option>
                          <option value="TALF">TALF</option>
                          <option value="PTOU">PTOU</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group mb-2">
                        <label>Treatment Regimen</label>
                        <select class="form-control" name="treatment_regimen">
                          <option>Select Treatment Regimen</option>
                          <option value="2HRZE/4HR">2HRZE/4HR</option>
                          <option value="2HRZE/10HR">2HRZE/10HR</option>
                          <option value="2HRZE/6HE">2HRZE/6HE</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Treatment Started</label>
                        <input type="date" class="form-control" name="treatment_started_date">
                      </div>
                      <div class="form-group mb-2">
                        <label>Treatment Outcome</label>
                        <select name="treatment_outcome" class="form-control">
                          <option value="CURED">CURED</option>
                          <option value="TREATMENT COMPLETED">TREATMENT COMPLETED</option>
                          <option value="TREATMENT FAILED">TREATMENT FAILED</option>
                          <option value="DIED">DIED</option>
                          <option value="LOST TO FOLLOW UP">LOST TO FOLLOW UP</option>
                          <option value="NOT EVALUATED">NOT EVALUATED</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Treatment Outcome Date</label>
                        <input type="date" class="form-control" name="treatment_outcome_date">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save Record</button>
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

    function editUser(id, username, first_name, last_name, role) {
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

    function addHouseholdMember() {
      const tbody = document.getElementById('householdMembersTable');
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input type="text" class="form-control" name="household_name[]"></td>
        <td><input type="number" class="form-control" name="household_age[]"></td>
        <td><input type="checkbox" name="household_screened[]"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
      `;
      tbody.appendChild(row);
    }

    function openLabModal(patientId, patientName) {
        // First, select the patient in the dropdown
        const patientSelect = document.getElementById('patient_select');
        patientSelect.value = patientId;
        
        // Manually trigger data population
        const selectedOption = patientSelect.options[patientSelect.selectedIndex];
        populatePatientData(selectedOption);
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('addLaboratoryModal'));
        modal.show();
    }

    function viewLabResults(patientId) {
      // You can either show results in a new modal or redirect to a details page
      window.location.href = `view_lab_results.php?patient_id=${patientId}`;
    }

    function showLoading() {
        const form = document.querySelector('#addLaboratoryModal form');
        if (form) {
            form.style.opacity = '0.5';
            form.style.pointerEvents = 'none';
        }
    }

    function hideLoading() {
        const form = document.querySelector('#addLaboratoryModal form');
        if (form) {
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
        }
    }

    function populatePatientData(selectedOption) {
        console.log('Populating data for patient:', selectedOption.value);
        
        if (selectedOption && selectedOption.value) {
            try {
                // Basic Information
                const fields = {
                    'textarea[name="address"]': 'address',
                    'input[name="age"]': 'age',
                    'select[name="sex"]': { attr: 'gender', transform: v => v === '1' ? 'M' : 'F' },
                    'input[name="contact"]': 'contact',
                    'input[name="dob"]': 'dob',
                    'input[name="height"]': 'height',
                    'select[name="bcg_scar"]': 'bcgScar',
                    'input[name="occupation"]': 'occupation',
                    'input[name="phil_health_no"]': 'philHealthNo',
                    'input[name="contact_person"]': 'contactPerson',
                    'input[name="contact_person_no"]': 'contactPersonNo'
                };

                // Fetch patient data from server
                fetch(`get_patient_data.php?id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(data => {
                        // Populate each field
                        Object.entries(fields).forEach(([selector, dataKey]) => {
                            const element = document.querySelector(selector);
                            if (element) {
                                if (typeof dataKey === 'object') {
                                    // Handle transformed values
                                    element.value = dataKey.transform(data[dataKey.attr]) || '';
                                } else {
                                    element.value = data[dataKey] || '';
                                }
                            }
                        });

                        // Set current date for date fields if empty
                        const currentDate = new Date().toISOString().split('T')[0];
                        const dateFields = ['date_opened', 'treatment_started_date'];
                        dateFields.forEach(fieldName => {
                            const dateField = document.querySelector(`input[name="${fieldName}"]`);
                            if (dateField && !dateField.value) {
                                dateField.value = currentDate;
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching patient data:', error);
                    });
            } catch (error) {
                console.error('Error populating patient data:', error);
            }
        }
    }

    // Add update functionality
    function updateLabRecord(id) {
        const form = document.querySelector('#addLaboratoryModal form');
        const formData = new FormData(form);
        formData.append('id', id);
        formData.append('ajax_update', '1');
        
        showLoading();
        
        fetch('laboratory.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addLaboratoryModal'));
                modal.hide();
                window.location.reload();
            } else {
                alert('Error updating laboratory record: ' + data.message);
            }
            hideLoading();
        })
        .catch(error => {
            hideLoading();
            alert('Error updating laboratory record: ' + error.message);
        });
    }

    // Add both event listeners for the patient select
    document.addEventListener('DOMContentLoaded', function() {
        const patientSelect = document.getElementById('patient_select');
        if (patientSelect) {
            // Handle change event
            patientSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                populatePatientData(selectedOption);
            });

            // Handle focus event (backup in case change doesn't fire)
            patientSelect.addEventListener('focus', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    populatePatientData(selectedOption);
                }
            });
        }
    });

    function viewTreatmentCard(id) {
        // Show loading state
        const modal = new bootstrap.Modal(document.getElementById('viewTreatmentCardModal'));
        const modalBody = document.querySelector('#viewTreatmentCardModal .modal-body');
        modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        modal.show();

        // Fetch the data
        fetch(`get_treatment_card.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
            });
    }

    function editLabRecord(id) {
        showLoading();
        
        // Fetch the laboratory record data
        fetch(`get_laboratory.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const labData = data.data;
                    
                    // Open the modal
                    const modal = new bootstrap.Modal(document.getElementById('addLaboratoryModal'));
                    modal.show();
                    
                    // Set form to update mode
                    const form = document.querySelector('#addLaboratoryModal form');
                    
                    // Add hidden input for ID
                    let idInput = form.querySelector('input[name="id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    // Populate the form fields
                    Object.keys(labData).forEach(key => {
                        const element = form.querySelector(`[name="${key}"]`);
                        if (element) {
                            if (element.type === 'checkbox') {
                                element.checked = labData[key] === '1' || labData[key] === true;
                            } else if (element.type === 'select-one') {
                                // For dropdown menus
                                const value = String(labData[key] || '');
                                if (value) {
                                    // Find and select the matching option
                                    const option = Array.from(element.options).find(opt => {
                                        const optValue = String(opt.value || '').toLowerCase();
                                        const optText = String(opt.textContent || '').toLowerCase();
                                        const compareValue = value.toLowerCase();
                                        return optValue === compareValue || optText === compareValue;
                                    });
                                    if (option) {
                                        option.selected = true;
                                        // Trigger change event in case there are any dependencies
                                        element.dispatchEvent(new Event('change'));
                                    }
                                }
                            } else if (element.type === 'select-multiple') {
                                const values = (labData[key] || '').split(',');
                                Array.from(element.options).forEach(option => {
                                    option.selected = values.includes(option.value);
                                });
                            } else {
                                element.value = labData[key] || '';
                            }
                        }
                    });
                    
                    // Special handling for specific dropdowns
                    const bacteriologicalStatus = form.querySelector('[name="bacteriological_status"]');
                    if (bacteriologicalStatus && labData.bacteriological_status) {
                        const value = String(labData.bacteriological_status);
                        Array.from(bacteriologicalStatus.options).forEach(option => {
                            const optValue = String(option.value || '').toLowerCase();
                            const optText = String(option.textContent || '').toLowerCase();
                            const compareValue = value.toLowerCase();
                            if (optValue === compareValue || optText === compareValue) {
                                option.selected = true;
                            }
                        });
                    }

                    const tbClassification = form.querySelector('[name="tb_classification"]');
                    if (tbClassification && labData.tb_classification) {
                        const value = String(labData.tb_classification);
                        Array.from(tbClassification.options).forEach(option => {
                            const optValue = String(option.value || '').toLowerCase();
                            const optText = String(option.textContent || '').toLowerCase();
                            const compareValue = value.toLowerCase();
                            if (optValue === compareValue || optText === compareValue) {
                                option.selected = true;
                            }
                        });
                    }
                    
                    // Update the submit button text
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.textContent = 'Update Record';
                    
                    form.onsubmit = function(e) {
                        e.preventDefault();
                        updateLabRecord(id);
                    };
                    
                    hideLoading();
                } else {
                    alert('Error loading laboratory record: ' + data.message);
                    hideLoading();
                }
            })
            .catch(error => {
                hideLoading();
                alert('Error loading laboratory record: ' + error.message);
            });
    }
  </script>
  <div class="modal fade" id="viewTreatmentCardModal" tabindex="-1" aria-labelledby="viewTreatmentCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewTreatmentCardModalLabel">View TB Treatment Card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>