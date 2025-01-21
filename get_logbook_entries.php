<?php
session_start();
require_once "connection/db.php";

if (!isset($_GET['patient_id'])) {
    echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
    exit;
}

$patient_id = $_GET['patient_id'];

$sql = "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) as added_by 
        FROM patient_logbook l 
        LEFT JOIN users u ON l.physician_id = u.id 
        WHERE l.patient_id = ? 
        ORDER BY l.log_date DESC, l.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = [
        'log_date' => date('F j, Y', strtotime($row['log_date'])),
        'notes' => htmlspecialchars($row['notes']),
        'created_at' => $row['created_at']
    ];
}

echo json_encode([
    'success' => true,
    'entries' => $entries
]); 