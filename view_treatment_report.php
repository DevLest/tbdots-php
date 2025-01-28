<?php
session_start();
require_once "connection/db.php";

// Check if user is a physician
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 1) {
    die('Unauthorized');
}

if (!isset($_GET['patient_id'])) {
    die("No patient ID provided");
}

$patient_id = (int)$_GET['patient_id'];

// Get patient and lab results data
$sql = "SELECT 
    p.*,
    l.*,
    m.location as municipality_name,
    b.name as barangay_name,
    CONCAT(u.first_name, ' ', u.last_name) as physician_name,
    COALESCE(l.bacteriological_status, '') as bacteriological_status,
    COALESCE(l.tb_classification, '') as tb_classification
FROM patients p
JOIN lab_results l ON p.lab_results_id = l.id
JOIN locations loc ON p.location_id = loc.id
JOIN municipalities m ON loc.municipality_id = m.id
JOIN barangays b ON loc.barangay_id = b.id
JOIN users u ON l.physician_id = u.id
WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

// Get clinical examinations
$examSql = "SELECT ce.*
FROM clinical_examinations ce
JOIN lab_results l ON ce.lab_results_id = l.id
WHERE l.patient_id = ?
ORDER BY ce.examination_date ASC";

$examStmt = $conn->prepare($examSql);
$examStmt->bind_param("i", $patient_id);
$examStmt->execute();
$examinations = $examStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get drug administrations
$drugSql = "SELECT da.*
FROM drug_administrations da
JOIN lab_results l ON da.lab_results_id = l.id
WHERE l.patient_id = ?
ORDER BY da.created_at ASC";

$drugStmt = $conn->prepare($drugSql);
$drugStmt->bind_param("i", $patient_id);
$drugStmt->execute();
$medications = $drugStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get DSSM results
$dssmSql = "SELECT dr.*
FROM dssm_results dr
JOIN lab_results l ON dr.lab_results_id = l.id
WHERE l.patient_id = ?
ORDER BY dr.month ASC";

$dssmStmt = $conn->prepare($dssmSql);
$dssmStmt->bind_param("i", $patient_id);
$dssmStmt->execute();
$dssmResults = $dssmStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all lab results for this patient, ordered by date
$labSql = "SELECT 
    l.*,
    CONCAT(u.first_name, ' ', u.last_name) as physician_name,
    DATE_FORMAT(l.created_at, '%M %d, %Y') as report_date
FROM lab_results l
JOIN users u ON l.physician_id = u.id
WHERE l.patient_id = ?
ORDER BY l.created_at DESC";

$labStmt = $conn->prepare($labSql);
$labStmt->bind_param("i", $patient_id);
$labStmt->execute();
$labResults = $labStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug query to check patient ID
error_log("Fetching medications for patient ID: " . $patient_id);

// Get medication history - store in a variable we'll use later
$medSql = "SELECT 
    DATE_FORMAT(it.transaction_date, '%Y-%m-%d %H:%i:%s') as transaction_date,
    it.quantity,
    it.batch_number,
    it.notes,
    p.brand_name,
    p.generic_name,
    p.dosage,
    p.unit_of_measure,
    CONCAT(u.first_name, ' ', u.last_name) as dispensed_by
FROM inventory_transactions it
LEFT JOIN products p ON it.product_id = p.id
LEFT JOIN users u ON it.user_id = u.id
WHERE it.patient_id = ?";

$medStmt = $conn->prepare($medSql);
$medStmt->bind_param("i", $patient_id);
$medStmt->execute();
$medicationHistory = $medStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug output
error_log("Initial medication fetch: " . print_r($medicationHistory, true));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Treatment Report</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #ddd;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
        }
        
        .label {
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f4f4f4;
        }
        
        .controls {
            margin: 20px 0;
            text-align: right;
        }
        
        .btn {
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
        }
        
        .lab-report {
            background-color: #f9f9f9;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .lab-report h3 {
            color: #333;
            border-bottom: 2px solid #666;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .lab-report h4 {
            color: #666;
            margin: 20px 0 10px 0;
        }
        
        .lab-report table {
            margin-bottom: 15px;
        }
        
        @media print {
            .lab-report {
                break-inside: avoid;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button class="btn" onclick="window.print()">Print Report</button>
        <button class="btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>

    <div class="report-header">
        <h2>Patient Treatment Report</h2>
    </div>

    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="info-grid">
            <div class="label">Name:</div>
            <div><?php echo htmlspecialchars($patient['fullname'] ?? ''); ?></div>
            
            <div class="label">Age/Gender:</div>
            <div><?php echo htmlspecialchars($patient['age'] ?? ''); ?> / <?php echo ($patient['gender'] ?? false) ? 'Male' : 'Female'; ?></div>
            
            <div class="label">Address:</div>
            <div><?php echo htmlspecialchars($patient['barangay_name'] ?? '') . ', ' . htmlspecialchars($patient['municipality_name'] ?? ''); ?></div>
            
            <div class="label">Case Number:</div>
            <div><?php echo htmlspecialchars($patient['case_number'] ?? ''); ?></div>
        </div>
    </div>

    <div class="report-section">
        <h2>Laboratory Reports History</h2>
        <?php foreach ($labResults as $index => $lab): ?>
            <div class="lab-report" style="margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Report #<?php echo $index + 1; ?> - <?php echo htmlspecialchars($lab['report_date']); ?></h3>
                <table>
                    <tr>
                        <th>Case Number:</th>
                        <td><?php echo htmlspecialchars($lab['case_number']); ?></td>
                        <th>Physician:</th>
                        <td><?php echo htmlspecialchars($lab['physician_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Bacteriological Status:</th>
                        <td><?php echo htmlspecialchars($lab['bacteriological_status'] ?? ''); ?></td>
                        <th>TB Classification:</th>
                        <td><?php echo htmlspecialchars($lab['tb_classification'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Diagnosis:</th>
                        <td><?php echo htmlspecialchars($lab['diagnosis']); ?></td>
                        <th>Treatment Regimen:</th>
                        <td><?php echo htmlspecialchars($lab['treatment_regimen']); ?></td>
                    </tr>
                    <tr>
                        <th>Treatment Started:</th>
                        <td><?php echo htmlspecialchars($lab['treatment_started_date']); ?></td>
                        <th>Treatment Outcome:</th>
                        <td>
                            <?php echo htmlspecialchars($lab['treatment_outcome']); ?>
                            <?php if ($lab['treatment_outcome_date']): ?>
                                (<?php echo htmlspecialchars($lab['treatment_outcome_date']); ?>)
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <?php
                // Get clinical examinations for this lab result
                $examSql = "SELECT ce.*
                    FROM clinical_examinations ce
                    WHERE ce.lab_results_id = ?
                    ORDER BY ce.examination_date ASC";
                
                $examStmt = $conn->prepare($examSql);
                $examStmt->bind_param("i", $lab['id']);
                $examStmt->execute();
                $examinations = $examStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                if (!empty($examinations)):
                ?>
                <h4>Clinical Examinations</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight</th>
                            <th>Symptoms</th>
                            <th>Side Effects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($examinations as $exam): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exam['examination_date']); ?></td>
                            <td><?php echo htmlspecialchars($exam['weight']); ?> kg</td>
                            <td>
                                <?php
                                $symptoms = [];
                                if ($exam['unexplained_fever']) $symptoms[] = 'Fever';
                                if ($exam['unexplained_cough']) $symptoms[] = 'Cough';
                                if ($exam['unimproved_wellbeing']) $symptoms[] = 'Poor Wellbeing';
                                if ($exam['poor_appetite']) $symptoms[] = 'Poor Appetite';
                                echo implode(', ', $symptoms);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($exam['side_effects']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php
                // Get DSSM results for this lab result
                $dssmSql = "SELECT dr.*
                    FROM dssm_results dr
                    WHERE dr.lab_results_id = ?
                    ORDER BY dr.month ASC";
                
                $dssmStmt = $conn->prepare($dssmSql);
                $dssmStmt->bind_param("i", $lab['id']);
                $dssmStmt->execute();
                $dssmResults = $dssmStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                if (!empty($dssmResults)):
                ?>
                <h4>DSSM Results</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Due Date</th>
                            <th>Exam Date</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dssmResults as $dssm): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dssm['month']); ?></td>
                            <td><?php echo htmlspecialchars($dssm['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($dssm['exam_date']); ?></td>
                            <td><?php echo htmlspecialchars($dssm['result']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php
                // Get medications for this lab result
                $drugSql = "SELECT da.*
                    FROM drug_administrations da
                    WHERE da.lab_results_id = ?
                    ORDER BY da.created_at ASC";
                
                $drugStmt = $conn->prepare($drugSql);
                $drugStmt->bind_param("i", $lab['id']);
                $drugStmt->execute();
                $medications = $drugStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                if (!empty($medications)):
                ?>
                <h4>Medications</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Drug Name</th>
                            <th>Dosage</th>
                            <th>Initial</th>
                            <th>Month 2-6</th>
                            <th>Month 7+</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medications as $med): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($med['drug_name']); ?></td>
                            <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                            <td><?php echo htmlspecialchars($med['initial']); ?></td>
                            <td>
                                <?php 
                                $midMonths = array_filter([
                                    $med['month_2'], $med['month_3'], 
                                    $med['month_4'], $med['month_5'],
                                    $med['month_6']
                                ]);
                                echo implode(', ', $midMonths);
                                ?>
                            </td>
                            <td>
                                <?php 
                                $lateMonths = array_filter([
                                    $med['month_7'], $med['month_8'],
                                    $med['month_9'], $med['month_10']
                                ]);
                                echo implode(', ', $lateMonths);
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <div class="section-title">Medication History</div>
        <?php if (!empty($medicationHistory)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Quantity</th>
                        <th>Dispensed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicationHistory as $med): ?>
                        <tr>
                            <td><?php echo $med['transaction_date']; ?></td>
                            <td>
                                <?php echo $med['brand_name']; ?>
                                <?php if (!empty($med['generic_name'])): ?>
                                    <br><small class="text-muted">(<?php echo $med['generic_name']; ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $med['quantity'] . ' ' . $med['unit_of_measure']; ?></td>
                            <td><?php echo $med['dispensed_by']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No medication history found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Add this at the end of the file
error_log("End of file reached");
?> 