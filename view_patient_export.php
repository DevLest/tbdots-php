<?php
session_start();
require_once 'connection/db.php';

if(!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

// Get export type and filters
$export_type = $_GET['export_type'] ?? 'all';
$filters = [
    'municipality' => $_GET['municipality'] ?? '0',
    'barangay' => $_GET['barangay'] ?? '0',
    'gender' => $_GET['gender'] ?? '0',
    'outcome' => $_GET['outcome'] ?? '0',
    'search' => $_GET['search'] ?? ''
];

// Build query based on export type and filters
$sql = "SELECT DISTINCT 
    p.id,
    p.fullname, 
    p.age,
    p.gender,
    CONCAT(b.name, ', ', m.location) as address,
    lr.diagnosis,
    lr.treatment_outcome,
    lr.case_number
FROM patients p
LEFT JOIN locations l ON p.location_id = l.id
LEFT JOIN municipalities m ON l.municipality_id = m.id
LEFT JOIN barangays b ON l.barangay_id = b.id
LEFT JOIN (
    SELECT patient_id, diagnosis, treatment_outcome, case_number
    FROM lab_results lr1
    WHERE created_at = (
        SELECT MAX(created_at)
        FROM lab_results lr2
        WHERE lr2.patient_id = lr1.patient_id
    )
) lr ON p.id = lr.patient_id
WHERE 1=1";

// Add filters if export_type is 'current'
if ($export_type === 'current') {
    if ($filters['municipality'] != '0') {
        $sql .= " AND m.id = " . intval($filters['municipality']);
    }
    if ($filters['barangay'] != '0') {
        $sql .= " AND b.id = " . intval($filters['barangay']);
    }
    if ($filters['gender'] != '0') {
        $sql .= " AND p.gender = " . intval($filters['gender']);
    }
    if ($filters['outcome'] != '0') {
        $sql .= " AND lr.treatment_outcome = '" . $conn->real_escape_string($filters['outcome']) . "'";
    }
    if (!empty($filters['search'])) {
        $sql .= " AND p.fullname LIKE '%" . $conn->real_escape_string($filters['search']) . "%'";
    }
}

$result = $conn->query($sql);

// Get medication history if treatment export
$medications = [];
if ($export_type === 'treatment' && $_SESSION['role_id'] == 2) {
    $med_sql = "SELECT 
        p.id,
        it.transaction_date,
        pr.brand_name,
        it.quantity,
        it.batch_number,
        u.username as dispensed_by
    FROM patients p
    LEFT JOIN inventory_transactions it ON p.id = it.patient_id
    LEFT JOIN products pr ON it.product_id = pr.id
    LEFT JOIN users u ON it.user_id = u.id
    WHERE it.type = 'OUT'
    ORDER BY p.id, it.transaction_date DESC";
    
    $med_result = $conn->query($med_sql);
    while ($row = $med_result->fetch_assoc()) {
        $medications[$row['id']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Export</title>
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
            margin-bottom: 20px;
            position: relative;
            padding: 20px 0;
        }
        
        .header-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            height: auto;
        }
        
        .header-title {
            text-align: center;
            line-height: 1.4;
        }
        
        .header-title h2,
        .header-title h3,
        .header-title h4 {
            margin: 5px 0;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th { background-color: #f4f4f4; }
        
        .controls {
            margin: 20px 0;
            text-align: right;
        }
        
        .patient-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .medication-history {
            margin-left: 20px;
        }
        
        .report-type {
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 1.5em;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button onclick="window.print()">Print Report</button>
        <button onclick="window.location.href='patients.php'">Back to Patients</button>
    </div>

    <div class="report-header">
        <div class="header-content">
            <img src="../assets/img/icons/doh.jpeg" alt="DOH Logo" class="logo">
            <div class="header-title">
                <h2>TB DOTS</h2>
                <h3>5th District Negros</h3>
                <h4>Republic of The Philippines</h4>
                <h4>Department of Health</h4>
            </div>
            <img src="../assets/img/icons/logo.png" alt="TB Hub Logo" class="logo">
        </div>
        <h2 class="report-type">Patient Generated Report</h2>
        <p>Generated on: <?php echo date('M d, Y'); ?></p>
    </div>

    <?php if ($export_type === 'treatment' && $_SESSION['role_id'] == 2): ?>
        <?php while ($patient = $result->fetch_assoc()): ?>
        <div class="patient-section">
            <h3><?php echo htmlspecialchars($patient['fullname'] ?? ''); ?></h3>
            <table>
                <tr>
                    <th>Case Number</th>
                    <td><?php echo htmlspecialchars($patient['case_number'] ?? ''); ?></td>
                    <th>Age/Gender</th>
                    <td><?php echo htmlspecialchars($patient['age'] ?? ''); ?> / <?php echo ($patient['gender'] ?? false) ? 'Male' : 'Female'; ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td colspan="3"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>Diagnosis</th>
                    <td><?php echo htmlspecialchars($patient['diagnosis'] ?? ''); ?></td>
                    <th>Outcome</th>
                    <td><?php echo htmlspecialchars($patient['treatment_outcome'] ?? ''); ?></td>
                </tr>
            </table>

            <?php if (!empty($medications[$patient['id']])): ?>
            <div class="medication-history">
                <h4>Medication History</h4>
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
                        <?php foreach ($medications[$patient['id']] as $med): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($med['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($med['brand_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($med['quantity'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($med['batch_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($med['dispensed_by'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Diagnosis</th>
                    <th>Treatment Outcome</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['fullname'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['age'] ?? ''); ?></td>
                    <td><?php echo ($row['gender'] ?? false) ? 'Male' : 'Female'; ?></td>
                    <td><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['diagnosis'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['treatment_outcome'] ?? ''); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html> 