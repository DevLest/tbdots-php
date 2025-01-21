<?php
session_start();

if(!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require_once "connection/db.php";
require_once "functions/log_activity.php";
include_once('head.php');

// Add this near the top of the file, after session_start()
if(isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
if(isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Consolidate all queries at the top
$patientsSqlList = "SELECT 
                    t2.id,
                    t2.case_number,
                    p2.fullname,
                    p2.gender,
                    p2.age,
                    t2.bacteriological_status,
                    t2.diagnosis,
                    t2.treatment_regimen,
                    t2.treatment_outcome,
                    t2.created_at
                FROM lab_results t2
                JOIN patients p2 ON t2.patient_id = p2.id
                INNER JOIN (
                    SELECT case_number, MAX(created_at) as max_date
                    FROM lab_results
                    GROUP BY case_number
                ) t1 ON t2.case_number = t1.case_number 
                    AND t2.created_at = t1.max_date
                ORDER BY t2.created_at DESC";

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

// $patientsSql = "SELECT p.*, l.location as location_name, u.first_name as physician_name,
//       lr.reason_for_examination, lr.history_of_treatment, lr.month_of_treatment, 
//       lr.test_requested
//       FROM patients p 
//       LEFT JOIN locations l ON p.location_id = l.id 
//       LEFT JOIN users u ON p.physician_id = u.id
//       LEFT JOIN lab_results lr ON p.lab_results_id = lr.id";

$patientReturns = $conn->query($patientsSql);
$allPatients = $patientReturns->fetch_all(MYSQLI_ASSOC); // Store all results in array

// $locationsSql = "SELECT id, location as name FROM locations";
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

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mode']) && $_POST['mode'] == 'edit') {
  $newCaseNumber = isset($_POST['case_number']) ? $_POST['case_number'] : date('Y') . '-' . 1;
} else {
  $lastCaseNumberSql = "SELECT id 
                      FROM lab_results
                      ORDER BY id DESC
                      LIMIT 1";
  $lastCaseResult = $conn->query($lastCaseNumberSql);
  $lastNumber = $lastCaseResult->fetch_assoc()['id'] ?? 0;
  $newCaseNumber = date('Y') . '-' . ($lastNumber + 1);
}

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
  
    // Check if it's an AJAX update request
    if (isset($_POST['ajax_update'])) {
        $response = array();
        try {
            // Update lab results record
            $sql = "UPDATE lab_results SET 
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
                treatment_outcome_date = ?
                WHERE case_number = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssss", 
                $_POST['date_opened'],
                $_POST['region_province'],
                $_POST['facility_name'],
                $_POST['source_of_patient'],
                $_POST['bacteriological_status'],
                $_POST['tb_classification'],
                $_POST['diagnosis'],
                $_POST['registration_group'],
                $_POST['treatment_regimen'],
                $_POST['treatment_started_date'],
                $_POST['treatment_outcome'],
                $_POST['treatment_outcome_date'],
                $_POST['case_number']
            );
            
            if ($stmt->execute()) {
                // Log the activity
                logActivity($conn, $_SESSION['user_id'], 'UPDATE', 'lab_results', $_POST['case_number'], 'Updated laboratory record #' . $_POST['case_number']);
                
                $response['success'] = true;
                $response['message'] = 'Record updated successfully';
            } else {
                $response['success'] = false;
                $response['message'] = 'Error updating record';
            }
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Handle regular form submission for new records
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Debug: Print the query and values
        error_log("Starting lab results insertion...");
        
        // Check if patient already has an existing lab record
        if ($_POST['mode'] !== 'edit') {
            $check_sql = "SELECT case_number FROM lab_results WHERE patient_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            
            if (!$check_stmt) {
                $_SESSION['error'] = "Failed to prepare check statement: " . $conn->error;
                throw new Exception("Failed to prepare check statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("i", $_POST['patient_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $existing_record = $check_result->fetch_assoc();
                $check_stmt->close(); // Move close() inside the if block
                $_SESSION['error'] = "Patient already has an existing laboratory record with case number: " . $existing_record['case_number'];
                throw new Exception("Patient already has an existing laboratory record with case number: " . $existing_record['case_number']);
            }
            
            $check_stmt->close(); // Close statement if no existing record found
        }

        // Insert into lab_results table
        $sql = "INSERT INTO lab_results (
            case_number, 
            date_opened, 
            region_province, 
            patient_id, 
            physician_id,
            source_of_patient, 
            tst_result,
            cxr_findings,
            bacteriological_status, 
            tb_classification,
            diagnosis, 
            registration_group, 
            treatment_regimen,
            treatment_started_date, 
            treatment_outcome, 
            treatment_outcome_date,
            other_exam,
            other_exam_date,
            tbdc
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $_SESSION['error'] = "Prepare failed: " . $conn->error;
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Handle optional fields with null values if empty
        $tst_result = !empty($_POST['tst_result']) ? $_POST['tst_result'] : null;
        $cxr_findings = !empty($_POST['cxr_findings']) ? $_POST['cxr_findings'] : null;
        $treatment_outcome = !empty($_POST['treatment_outcome']) ? $_POST['treatment_outcome'] : null;
        $treatment_outcome_date = !empty($_POST['treatment_outcome_date']) ? $_POST['treatment_outcome_date'] : null;
        $other_exam = !empty($_POST['other_exam']) ? $_POST['other_exam'] : null;
        $other_exam_date = !empty($_POST['other_exam_date']) ? $_POST['other_exam_date'] : null;
        $tbdc = !empty($_POST['tbdc']) ? $_POST['tbdc'] : null;
        
        // Add validation for bacteriological_status
        $bacteriological_status = null;
        if (isset($_POST['bacteriological_status']) && $_POST['bacteriological_status'] !== '') {
            if (in_array($_POST['bacteriological_status'], ['confirmed', 'clinically'])) {
                $bacteriological_status = $_POST['bacteriological_status'];
            } else {
                $error_message = "Invalid bacteriological status value";
                // Redirect back or handle error appropriately
                header("Location: laboratory.php");
                exit();
            }
        }
        
        // Add validation for tb_classification
        $tb_classification = null;
        if (isset($_POST['tb_classification']) && $_POST['tb_classification'] !== '') {
            // Convert the input to match the exact enum values
            switch(strtolower($_POST['tb_classification'])) {
                case 'pulmonary':
                    $tb_classification = 'pulmonary';
                    break;
                case 'extra_pulmonary':
                case 'extra pulmonary':
                case 'extrapulmonary':
                    $tb_classification = 'extra_pulmonary';
                    break;
                default:
                    $_SESSION['error'] = "Invalid TB classification value";
                    header("Location: laboratory.php");
                    exit();
            }
        }
        
        // Add validation for diagnosis
        $diagnosis = null;
        if (isset($_POST['diagnosis']) && $_POST['diagnosis'] !== '') {
            // Convert the input to match the exact enum values
            switch(strtoupper($_POST['diagnosis'])) {
                case 'TB DISEASE':
                    $diagnosis = 'TB DISEASE';
                    break;
                case 'TB INFECTION':
                    $diagnosis = 'TB INFECTION';
                    break;
                case 'TB EXPOSURE':
                    $diagnosis = 'TB EXPOSURE';
                    break;
                default:
                    $_SESSION['error'] = "Invalid diagnosis value";
                    header("Location: laboratory.php");
                    exit();
            }
        }
        
        // Validate treatment_started_date
        $treatment_started_date = null;
        if (isset($_POST['treatment_started_date']) && $_POST['treatment_started_date'] !== '') {
            $treatment_started_date = $_POST['treatment_started_date'];
        } else {
            // Set to current date if empty
            $treatment_started_date = date('Y-m-d');
        }
        
        $bind_result = $stmt->bind_param("ssssissssssssssssss", 
            $_POST['case_number'],
            $_POST['date_opened'],
            $_POST['region_province'],
            $_POST['patient_id'],
            $_SESSION['user_id'],
            $_POST['source_of_patient'],
            $tst_result,
            $cxr_findings,
            $bacteriological_status,
            $tb_classification,  // Use the validated value
            $diagnosis,  // Use the validated value
            $_POST['registration_group'],
            $_POST['treatment_regimen'],
            $treatment_started_date,  // Use the validated value
            $treatment_outcome,
            $treatment_outcome_date,
            $other_exam,
            $other_exam_date,
            $tbdc
        );
        
        if (!$bind_result) {
            $_SESSION['error'] = "Binding parameters failed: " . $stmt->error;
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }
        
        // Debug: Print values being inserted
        error_log("Inserting values: " . json_encode([
            'case_number' => $_POST['case_number'],
            'date_opened' => $_POST['date_opened'],
            'region_province' => $_POST['region_province'],
            'patient_id' => $_POST['patient_id'],
            'physician_id' => $_SESSION['user_id'],
            'source_of_patient' => $_POST['source_of_patient'],
            'tst_result' => $tst_result,
            'cxr_findings' => $cxr_findings,
            'bacteriological_status' => $bacteriological_status,
            'tb_classification' => $tb_classification,
            'diagnosis' => $diagnosis,
            'registration_group' => $_POST['registration_group'],
            'treatment_regimen' => $_POST['treatment_regimen'],
            'treatment_started_date' => $treatment_started_date,
            'treatment_outcome' => $treatment_outcome,
            'treatment_outcome_date' => $treatment_outcome_date,
            'other_exam' => $other_exam,
            'other_exam_date' => $other_exam_date,
            'tbdc' => $tbdc
        ]));
        
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Execute failed: " . $stmt->error;
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $lab_results_id = $conn->insert_id;
        error_log("Lab results inserted successfully with ID: " . $lab_results_id);
        
        // Continue with other insertions only if we have a valid lab_results_id
        if ($lab_results_id) {
            // Insert clinical examinations (only if exam_date is provided)
            if (isset($_POST['exam_date']) && is_array($_POST['exam_date'])) {
                $exam_sql = "INSERT INTO clinical_examinations (
                    lab_results_id, examination_date, weight, unexplained_fever,
                    unexplained_cough, unimproved_wellbeing, poor_appetite,
                    positive_pe_findings, side_effects
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $exam_stmt = $conn->prepare($exam_sql);
                if (!$exam_stmt) {
                    $_SESSION['error'] = "Prepare clinical examinations failed: " . $conn->error;
                    throw new Exception("Prepare clinical examinations failed: " . $conn->error);
                }
                
                foreach ($_POST['exam_date'] as $key => $date) {
                    if (!empty($date)) {
                        $fever = isset($_POST['fever'][$key]) ? 1 : 0;
                        $cough = isset($_POST['cough'][$key]) ? 1 : 0;
                        $wellbeing = isset($_POST['wellbeing'][$key]) ? 1 : 0;
                        $appetite = isset($_POST['appetite'][$key]) ? 1 : 0;
                        $pe_findings = isset($_POST['pe_findings'][$key]) ? 1 : 0;
                        $side_effects = !empty($_POST['side_effects'][$key]) ? $_POST['side_effects'][$key] : null;
                        $weight = !empty($_POST['weight'][$key]) ? $_POST['weight'][$key] : null;
                        
                        if (!$exam_stmt->bind_param("isdiiiiss", 
                            $lab_results_id,
                            $date,
                            $weight,
                            $fever,
                            $cough,
                            $wellbeing,
                            $appetite,
                            $pe_findings,
                            $side_effects
                        )) {
                            $_SESSION['error'] = "Binding clinical examination parameters failed: " . $exam_stmt->error;
                            throw new Exception("Binding clinical examination parameters failed: " . $exam_stmt->error);
                        }
                        
                        if (!$exam_stmt->execute()) {
                            $_SESSION['error'] = "Execute clinical examination failed: " . $exam_stmt->error;
                            throw new Exception("Execute clinical examination failed: " . $exam_stmt->error);
                        }
                    }
                }
            }
            
            // Insert household members (only if household_name is provided)
            if (isset($_POST['household_name']) && is_array($_POST['household_name'])) {
                $household_sql = "INSERT INTO household_members (
                    lab_results_id, first_name, age, screened
                ) VALUES (?, ?, ?, ?)";
                
                $household_stmt = $conn->prepare($household_sql);
                if (!$household_stmt) {
                    $_SESSION['error'] = "Prepare household members failed: " . $conn->error;
                    throw new Exception("Prepare household members failed: " . $conn->error);
                }
                
                foreach ($_POST['household_name'] as $key => $name) {
                    if (!empty($name)) {
                        $screened = isset($_POST['household_screened'][$key]) ? 1 : 0;
                        $age = !empty($_POST['household_age'][$key]) ? $_POST['household_age'][$key] : null;
                        
                        if (!$household_stmt->bind_param("isii", 
                            $lab_results_id,
                            $name,
                            $age,
                            $screened
                        )) {
                            $_SESSION['error'] = "Binding household member parameters failed: " . $household_stmt->error; 
                            throw new Exception("Binding household member parameters failed: " . $household_stmt->error);
                        }
                        
                        if (!$household_stmt->execute()) {
                            $_SESSION['error'] = "Execute household member failed: " . $household_stmt->error;
                            throw new Exception("Execute household member failed: " . $household_stmt->error);
                        }
                    }
                }
            }
            
            // Insert drug history (only if drug_history is provided)
            if (isset($_POST['drug_history'])) {
                $drug_history_sql = "INSERT INTO drug_histories (
                    lab_results_id, has_history, duration, drugs_taken
                ) VALUES (?, ?, ?, ?)";
                
                $drug_history_stmt = $conn->prepare($drug_history_sql);
                if (!$drug_history_stmt) {
                    $_SESSION['error'] = "Prepare drug history failed: " . $conn->error;
                    throw new Exception("Prepare drug history failed: " . $conn->error);
                }
                
                $has_history = $_POST['drug_history'] === 'Yes' ? 1 : 0;
                
                // Validate and format duration
                $duration = null;
                if (!empty($_POST['drug_duration'])) {
                    switch(strtolower(trim($_POST['drug_duration']))) {
                        case 'less than 1 mo':
                        case 'less than 1 month':
                        case '<1':
                            $duration = 'less than 1 mo';
                            break;
                        case '1 mo or more':
                        case '1 month or more':
                        case '>=1':
                            $duration = '1 mo or more';
                            break;
                        default:
                            error_log("Invalid duration value: " . $_POST['drug_duration']);
                            $_SESSION['error'] = "Invalid duration value. Allowed values are: 'less than 1 mo' or '1 mo or more'";
                            header("Location: laboratory.php");
                            exit();
                    }
                }
                
                $drugs_taken = isset($_POST['drugs_taken']) ? implode(',', $_POST['drugs_taken']) : null;
                
                if (!$drug_history_stmt->bind_param("iiss", 
                    $lab_results_id,
                    $has_history,
                    $duration,
                    $drugs_taken
                )) {
                    $_SESSION['error'] = "Binding drug history parameters failed: " . $drug_history_stmt->error;
                    throw new Exception("Binding drug history parameters failed: " . $drug_history_stmt->error);
                }
                
                if (!$drug_history_stmt->execute()) {
                    $_SESSION['error'] = "Execute drug history failed: " . $drug_history_stmt->error;
                    throw new Exception("Execute drug history failed: " . $drug_history_stmt->error);
                }
            }
            
            // Save DSSM Results
            if (isset($_POST['dssm_due_date']) && is_array($_POST['dssm_due_date'])) {
                $dssm_sql = "INSERT INTO dssm_results (lab_results_id, month, due_date, exam_date, result) 
                             VALUES (?, ?, ?, ?, ?)";
                $dssm_stmt = $conn->prepare($dssm_sql);
                
                foreach ($_POST['dssm_due_date'] as $key => $due_date) {
                    if (!empty($due_date) || !empty($_POST['dssm_exam_date'][$key]) || !empty($_POST['dssm_result'][$key])) {
                        $dssm_stmt->bind_param("iisss", 
                            $lab_results_id,
                            $key, // month number
                            $due_date,
                            $_POST['dssm_exam_date'][$key],
                            $_POST['dssm_result'][$key]
                        );
                        $dssm_stmt->execute();
                    }
                }
            }

            // Save Drug Dosages
            $drug_names = ['Isoniazid [H] 10mg/kg (200mg/5ml)', 'Rifampicin [R]', 'Pyrazinamide [Z]', 'Ethambutol [E]'];
            foreach ($drug_names as $drug_name) {
                $dosage_sql = "INSERT INTO drug_dosages (lab_results_id, drug_name, 
                       month_1, month_2, month_3, month_4, month_5, month_6, 
                       month_7, month_8, month_9, month_10, month_11, month_12) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $dosage_stmt = $conn->prepare($dosage_sql);
                
                // Get the drug prefix (e.g., 'isoniazid', 'rifampicin', etc.)
                $drug_prefix = strtolower(explode(' ', $drug_name)[0]);
                
                // Create references for bind_param
                $ref_id = $lab_results_id;
                $ref_name = $drug_name;
                $refs = array(&$ref_id, &$ref_name);
                
                // Add monthly values as references
                for ($month = 1; $month <= 12; $month++) {
                    $field_name = $drug_prefix . "_month_" . $month;
                    $monthly_values[$month-1] = isset($_POST[$field_name]) ? $_POST[$field_name] : null;
                    $refs[] = &$monthly_values[$month-1];
                }
                
                // Create the types string
                $types = "ss" . str_repeat("d", 12);
                array_unshift($refs, $types);
                
                // Bind parameters using references
                call_user_func_array(array($dosage_stmt, 'bind_param'), $refs);
                
                // Execute the statement
                $dosage_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Log the activity
            logActivity($conn, $_SESSION['user_id'], 'CREATE', 'lab_results', $lab_results_id, 'Added new laboratory record');
            
            $_SESSION['success'] = "Laboratory record added successfully";
            header("Location: laboratory.php");
            exit;
        } else {
            throw new Exception("Failed to get last insert ID");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in laboratory.php: " . $e->getMessage());
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: laboratory.php");
        exit;
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

<?php if(isset($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show position-fixed text-white" role="alert" style="top: 20px; right: 20px; z-index: 1080; max-width: 500px;">
    <?php echo $error_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(isset($success_message)): ?>
<div class="alert alert-success alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 1080; max-width: 500px;">
    <?php echo $success_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

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
    <div class="modal fade" id="addLaboratoryModal" tabindex="-1" aria-labelledby="addLaboratoryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" style="max-width: 95%; z-index: 9999;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addLaboratoryModalLabel">TB Treatment Card</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="mode" value="">
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
                        <input type="text" class="form-control" name="region_province" value="Negros Occidental">
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
                                  <option value="<?php echo $patientRow['id']; ?>"<?php echo $dataAttributesString; ?>">
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
                            <option value="">Select Bacteriological Status</option>
                            <option value="confirmed">Bacteriologically Confirmed</option>
                            <option value="clinically">Clinically Diagnosed</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label>Classification of TB Disease</label>
                        <select name="tb_classification" class="form-control">
                            <option value="">Select Classification of TB Disease</option>
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

              <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Clinical Examination</span>
                  <button type="button" class="btn btn-sm btn-secondary" onclick="addClinicalExamRow()">
                    <i class="fas fa-plus"></i> Add Row
                  </button>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table" id="clinical-exam-table">
                      <thead>
                        <tr class="text-nowrap">
                          <th style="width: 150px;">Date</th>
                          <th style="width: 120px;">Weight (Kg)</th>
                          <th style="width: 100px;">Unexplained Fever</th>
                          <th style="width: 100px;">Cough/Wheezing</th>
                          <th style="width: 100px;">Well Being</th>
                          <th style="width: 100px;">Appetite</th>
                          <th style="width: 100px;">PE Findings</th>
                          <th>Side Effects</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><input type="date" class="form-control form-control-sm" name="exam_date[]"></td>
                          <td><input type="number" step="0.1" class="form-control form-control-sm" name="weight[]"></td>
                          <td class="text-center"><input type="checkbox" class="form-check-input" name="fever[]"></td>
                          <td class="text-center"><input type="checkbox" class="form-check-input" name="cough[]"></td>
                          <td class="text-center"><input type="checkbox" class="form-check-input" name="wellbeing[]"></td>
                          <td class="text-center"><input type="checkbox" class="form-check-input" name="appetite[]"></td>
                          <td class="text-center"><input type="checkbox" class="form-check-input" name="pe_findings[]"></td>
                          <td><input type="text" class="form-control form-control-sm" name="side_effects[]"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="card mb-3">
                <div class="card-header">Drugs: Dosages and Preparations</div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Drug</th>
                          <th>Month 1</th>
                          <th>Month 2</th>
                          <th>Month 3</th>
                          <th>Month 4</th>
                          <th>Month 5</th>
                          <th>Month 6</th>
                          <th>Month 7</th>
                          <th>Month 8</th>
                          <th>Month 9</th>
                          <th>Month 10</th>
                          <th>Month 11</th>
                          <th>Month 12</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Isoniazid [H] 10mg/kg (200mg/5ml)</td>
                          <?php for($i = 1; $i <= 12; $i++): ?>
                          <td>
                            <input type="number" step="0.1" class="form-control" name="isoniazid_month_<?php echo $i; ?>">
                          </td>
                          <?php endfor; ?>
                        </tr>
                        <tr>
                          <td>Rifampicin [R]</td>
                          <?php for($i = 1; $i <= 12; $i++): ?>
                          <td>
                            <input type="number" step="0.1" class="form-control" name="rifampicin_month_<?php echo $i; ?>">
                          </td>
                          <?php endfor; ?>
                        </tr>
                        <tr>
                          <td>Pyrazinamide [Z]</td>
                          <?php for($i = 1; $i <= 12; $i++): ?>
                          <td>
                            <input type="number" step="0.1" class="form-control" name="pyrazinamide_month_<?php echo $i; ?>">
                          </td>
                          <?php endfor; ?>
                        </tr>
                        <tr>
                          <td>Ethambutol [E]</td>
                          <?php for($i = 1; $i <= 12; $i++): ?>
                          <td>
                            <input type="number" step="0.1" class="form-control" name="ethambutol_month_<?php echo $i; ?>">
                          </td>
                          <?php endfor; ?>
                        </tr>
                      </tbody>
                    </table>
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
                          <option value="less than 1 mo" selected>Less than 1 month</option>
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

              <!-- Other Diagnostic Tests Section -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Other Exam</label>
                    <div class="input-group">
                      <input type="text" class="form-control" name="other_exam" placeholder="Enter other exam details">
                      <input type="date" class="form-control" name="other_exam_date">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>TBDC</label>
                    <input type="text" class="form-control" name="tbdc" placeholder="Enter TBDC details">
                  </div>
                </div>
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
                          <option value="TB DISEASE" selected>TB DISEASE</option>
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
                          <option value="ON-GOING" selected>ON-GOING</option>
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

          <script>
            $(document).ready(function() {
                
                // Listen for modal show event
                $('#addLaboratoryModal').on('show.bs.modal', function () {
                    
                    // Add change event listener to patient select
                    $('#patient_select').on('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption) {
                            populatePatientData(selectedOption);
                        }
                    });
                });
            });

            function populatePatientData(selectedOption) {
                if (!selectedOption || !selectedOption.value) {
                    console.log('No valid option selected');
                    return;
                }

                // Show loading state
                const form = $('#addLaboratoryModal form');
                form.css('opacity', '0.5').css('pointer-events', 'none');
                
                // Use jQuery AJAX for better compatibility
                $.ajax({
                    url: 'get_patient_data.php',
                    method: 'GET',
                    data: { id: selectedOption.value },
                    dataType: 'json',
                    success: function(data) {                        
                        // Populate fields using jQuery
                        $('#addLaboratoryModal textarea[name="address"]').val(data.address || '');
                        $('#addLaboratoryModal input[name="age"]').val(data.age || '');
                        $('#addLaboratoryModal select[name="sex"]').val(data.gender === '1' || data.gender === 1 ? 'M' : 'F');
                        $('#addLaboratoryModal input[name="contact"]').val(data.contact || '');
                        $('#addLaboratoryModal input[name="dob"]').val(data.dob || '');
                        $('#addLaboratoryModal input[name="height"]').val(data.height || '');
                        $('#addLaboratoryModal select[name="bcg_scar"]').val(data.bcg_scar || '');
                        $('#addLaboratoryModal input[name="occupation"]').val(data.occupation || '');
                        $('#addLaboratoryModal input[name="phil_health_no"]').val(data.phil_health_no || '');
                        $('#addLaboratoryModal input[name="contact_person"]').val(data.contact_person || '');
                        $('#addLaboratoryModal input[name="contact_person_no"]').val(data.contact_person_no || '');
                        $('#addLaboratoryModal input[name="facility_name"]').val(data.location || '');

                        // Set current date for empty date fields
                        const currentDate = new Date().toISOString().split('T')[0];
                        ['date_opened', 'treatment_started_date'].forEach(function(fieldName) {
                            const dateField = $('#addLaboratoryModal input[name="' + fieldName + '"]');
                            if (dateField.length && !dateField.val()) {
                                dateField.val(currentDate);
                            }
                        });

                        // Restore form interactivity
                        form.css('opacity', '1').css('pointer-events', 'auto');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching patient data:', error);
                        form.css('opacity', '1').css('pointer-events', 'auto');
                        alert('Error loading patient data. Please try again.');
                    }
                });
            }
          </script>
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
                showAlert('Laboratory record updated successfully');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addLaboratoryModal'));
                modal.hide();
                window.location.reload();
            } else {
                showAlert('Error updating laboratory record: ' + data.message, 'danger');
            }
            hideLoading();
        })
        .catch(error => {
            hideLoading();
            showAlert('Error updating laboratory record: ' + error.message, 'danger');
        });
    }

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
        $.ajax({
            url: 'get_laboratory.php',
            method: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                
                if (response.success) {
                    const data = response.data;
                    
                    try {
                        // Basic Information
                        $('input[name="case_number"]').val(data.case_number);
                        $('input[name="mode"]').val('edit');
                        $('input[name="date_opened"]').val(data.date_opened);
                        $('input[name="region_province"]').val(data.region_province);
                        $('input[name="source_of_patient"]').val(data.source_of_patient);
                        
                        // Patient Details
                        $('#patient_select').val(data.patient_id);
                        $('textarea[name="address"]').val(data.address);
                        $('input[name="age"]').val(data.age);
                        $('select[name="sex"]').val(data.sex === 1 ? 'M' : 'F');
                        $('input[name="contact"]').val(data.contact_number);
                        
                        // Additional Patient Info
                        $('input[name="height"]').val(data.height);
                        $('input[name="occupation"]').val(data.occupation);
                        $('input[name="phil_health_no"]').val(data.phil_health_no);
                        $('input[name="contact_person"]').val(data.contact_person);
                        $('input[name="contact_person_no"]').val(data.contact_number);
                        $('select[name="bcg_scar"]').val(data.bcg_scar);
                        
                        // Diagnostic Tests
                        $('input[name="tst_result"]').val(data.tst_result);
                        $('textarea[name="cxr_findings"]').val(data.cxr_findings);
                        $('input[name="other_exam"]').val(data.other_exam);
                        $('input[name="other_exam_date"]').val(data.other_exam_date);
                        $('input[name="tbdc"]').val(data.tbdc);
                        $('select[name="bacteriological_status"]').val(data.bacteriological_status);
                        $('select[name="tb_classification"]').val(data.tb_classification);
                        
                        // Treatment Details
                        $('select[name="diagnosis"]').val(data.diagnosis);
                        $('select[name="registration_group"]').val(data.registration_group);
                        $('select[name="treatment_regimen"]').val(data.treatment_regimen);
                        $('input[name="treatment_started_date"]').val(data.treatment_started_date);
                        $('select[name="treatment_outcome"]').val(data.treatment_outcome);
                        $('input[name="treatment_outcome_date"]').val(data.treatment_outcome_date);

                        // DSSM Results
                        if (data.dssm_results && data.dssm_results.length > 0) {
                            data.dssm_results.forEach((result, index) => {
                                $(`input[name="dssm_due_date[]"]`).eq(index).val(result.due_date);
                                $(`input[name="dssm_exam_date[]"]`).eq(index).val(result.exam_date);
                                $(`input[name="dssm_result[]"]`).eq(index).val(result.result);
                            });
                        }

                        // Clinical Examinations
                        if (data.clinical_examinations && data.clinical_examinations.length > 0) {
                            $('#clinical-exam-table tbody').empty();
                            data.clinical_examinations.forEach(exam => {
                                $('#clinical-exam-table tbody').append(`
                                    <tr>
                                        <td><input type="date" class="form-control" name="exam_date[]" value="${exam.examination_date}"></td>
                                        <td><input type="number" step="0.1" class="form-control" name="weight[]" value="${exam.weight}"></td>
                                        <td><input type="checkbox" class="form-check-input" name="fever[]" ${exam.unexplained_fever ? 'checked' : ''}></td>
                                        <td><input type="checkbox" class="form-check-input" name="cough[]" ${exam.unexplained_cough ? 'checked' : ''}></td>
                                        <td><input type="checkbox" class="form-check-input" name="wellbeing[]" ${exam.unimproved_wellbeing ? 'checked' : ''}></td>
                                        <td><input type="checkbox" class="form-check-input" name="appetite[]" ${exam.poor_appetite ? 'checked' : ''}></td>
                                        <td><input type="checkbox" class="form-check-input" name="pe_findings[]" ${exam.positive_pe_findings ? 'checked' : ''}></td>
                                        <td><input type="text" class="form-control" name="side_effects[]" value="${exam.side_effects || ''}"></td>
                                    </tr>
                                `);
                            });
                        }

                        // Drug Dosages
                        if (data.drug_dosages && data.drug_dosages.length > 0) {
                            data.drug_dosages.forEach(dosage => {
                                let prefix;
                                if (dosage.drug_name.includes('Isoniazid')) prefix = 'isoniazid';
                                else if (dosage.drug_name.includes('Rifampicin')) prefix = 'rifampicin';
                                else if (dosage.drug_name.includes('Pyrazinamide')) prefix = 'pyrazinamide';
                                else if (dosage.drug_name.includes('Ethambutol')) prefix = 'ethambutol';

                                if (prefix) {
                                    for (let i = 1; i <= 12; i++) {
                                        const monthField = `month_${i}`;
                                        if (dosage[monthField] !== null && dosage[monthField] !== "0.00") {
                                            $(`input[name="${prefix}_month_${i}"]`).val(dosage[monthField]);
                                        }
                                    }
                                }
                            });
                        }

                        // Household Members
                        const tbody = document.getElementById('householdMembersTable');
                        while (tbody.firstChild) {
                          tbody.removeChild(tbody.firstChild);
                        }
                        if (data.household_members && data.household_members.length > 0) {
                            data.household_members.forEach(member => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td><input type="text" class="form-control" name="household_name[]" value="${member['first_name'] || ''}"></td>
                                    <td><input type="number" class="form-control" name="household_age[]" value="${member['age'] || ''}"></td>
                                    <td><input type="checkbox" name="household_screened[]" ${member['screened'] == 1 ? 'checked' : ''}></td>
                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
                                `;
                                tbody.appendChild(row);
                            });
                        }

                        // Show modal and update form state
                        const modal = new bootstrap.Modal(document.getElementById('addLaboratoryModal'));
                        modal.show();
                        
                        // Update form button and add record ID
                        $('input[name="lab_record_id"]').val(data.id);
                        $('#form-button').text('Update Record');

                    } catch (error) {
                        console.error('Error while populating form:', error);
                        alert('Error while populating form: ' + error.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                showAlert('Error loading laboratory record: ' + error, 'danger');
            }
        });
    }

    function addClinicalExamRow() {
        const tbody = document.querySelector('#clinical-exam-table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="date" class="form-control" name="exam_date[]"></td>
            <td><input type="number" step="0.1" class="form-control" name="weight[]"></td>
            <td><input type="checkbox" class="form-check-input" name="fever[]"></td>
            <td><input type="checkbox" class="form-check-input" name="cough[]"></td>
            <td><input type="checkbox" class="form-check-input" name="wellbeing[]"></td>
            <td><input type="checkbox" class="form-check-input" name="appetite[]"></td>
            <td><input type="checkbox" class="form-check-input" name="pe_findings[]"></td>
            <td><input type="text" class="form-control" name="side_effects[]"></td>
        `;
        tbody.appendChild(newRow);
    }

    // Add this function to handle alerts
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.role = 'alert';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1080; max-width: 500px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
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
</body>