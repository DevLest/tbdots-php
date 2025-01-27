<?php
session_start();
require_once 'connection/db.php';

// Check if user is a physician
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 1) {
    die('Unauthorized');
}

if (!isset($_GET['patient_id'])) {
    die('Patient ID required');
}

$patient_id = $_GET['patient_id'];

// Get patient and treatment data
$sql = "SELECT 
    p.*,
    CONCAT(b.name, ', ', m.location) as address,
    lr.case_number,
    lr.diagnosis,
    lr.treatment_outcome,
    lr.bacteriological_status,
    lr.treatment_regimen
FROM patients p
LEFT JOIN locations l ON p.location_id = l.id
LEFT JOIN municipalities m ON l.municipality_id = m.id
LEFT JOIN barangays b ON l.barangay_id = b.id
LEFT JOIN lab_results lr ON p.id = lr.patient_id
WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Get medication history
$sql = "SELECT 
    it.*,
    p.brand_name as product_name,
    u.username as dispensed_by
FROM inventory_transactions it
JOIN products p ON it.product_id = p.id
JOIN users u ON it.user_id = u.id
WHERE it.patient_id = ? AND it.type = 'OUT'
ORDER BY it.transaction_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$medications = $stmt->get_result();
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
            <div><?php echo htmlspecialchars($patient['address'] ?? ''); ?></div>
            
            <div class="label">Case Number:</div>
            <div><?php echo htmlspecialchars($patient['case_number'] ?? ''); ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Treatment Information</div>
        <div class="info-grid">
            <div class="label">Diagnosis:</div>
            <div><?php echo htmlspecialchars($patient['diagnosis'] ?? ''); ?></div>
            
            <div class="label">Status:</div>
            <div><?php echo htmlspecialchars($patient['bacteriological_status'] ?? ''); ?></div>
            
            <div class="label">Regimen:</div>
            <div><?php echo htmlspecialchars($patient['treatment_regimen'] ?? ''); ?></div>
            
            <div class="label">Outcome:</div>
            <div><?php echo htmlspecialchars($patient['treatment_outcome'] ?? ''); ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Medication History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Batch #</th>
                    <th>Dispensed By</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($med = $medications->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($med['transaction_date'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($med['product_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($med['quantity'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($med['batch_number'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($med['dispensed_by'] ?? ''); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 