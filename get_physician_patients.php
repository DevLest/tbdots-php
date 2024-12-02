<?php
session_start();
require_once "connection/db.php";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || !isset($_GET['physician_id'])) {
    http_response_code(403);
    exit;
}

$physician_id = intval($_GET['physician_id']);

$sql = "SELECT 
    p.*,
    lr.created_at as latest_lab_result,
    lr.treatment_outcome
FROM patients p 
LEFT JOIN (
    SELECT 
        patient_id,
        treatment_outcome,
        created_at,
        ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY created_at DESC) as rn
    FROM lab_results
) lr ON p.id = lr.patient_id AND lr.rn = 1
WHERE p.physician_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $physician_id);
$stmt->execute();
$result = $stmt->get_result();

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

header('Content-Type: application/json');
echo json_encode($patients); 