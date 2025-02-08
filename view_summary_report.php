<?php
session_start();
require_once 'connection/db.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$period = $_GET['period'] ?? 'week';

// Calculate date range
$end_date = date('Y-m-d');
switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-1 week'));
        $title = 'Weekly Patient Summary Report';
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-1 month'));
        $title = 'Monthly Patient Summary Report';
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        $title = 'Yearly Patient Summary Report';
        break;
    default:
        die('Invalid period');
}

// Get patient data
$sql = "SELECT 
    p.fullname,
    p.age,
    p.gender,
    CONCAT(b.name, ', ', m.location) as address,
    lr.diagnosis,
    lr.treatment_outcome,
    lr.created_at
FROM patients p
LEFT JOIN locations l ON p.location_id = l.id
LEFT JOIN municipalities m ON l.municipality_id = m.id
LEFT JOIN barangays b ON l.barangay_id = b.id
LEFT JOIN (
    SELECT patient_id, diagnosis, treatment_outcome, created_at
    FROM lab_results
    WHERE created_at BETWEEN ? AND ?
    AND id IN (
        SELECT MAX(id)
        FROM lab_results
        WHERE created_at BETWEEN ? AND ?
        GROUP BY patient_id
    )
) lr ON p.id = lr.patient_id
WHERE lr.created_at IS NOT NULL
ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
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
        
        .period {
            margin-top: 20px;
            text-align: center;
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
        <div class="period">
            Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Diagnosis</th>
                <th>Treatment Outcome</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                <td><?php echo htmlspecialchars($row['age']); ?></td>
                <td><?php echo $row['gender'] ? 'Male' : 'Female'; ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                <td><?php echo htmlspecialchars($row['treatment_outcome']); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html> 