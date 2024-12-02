<?php
session_start();
require_once "connection/db.php";

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (!isset($_GET['id'])) {
    die(json_encode(['success' => false, 'message' => 'No patient ID provided']));
}

$patientId = (int)$_GET['id'];

$sql = "SELECT * FROM patients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if ($patient) {
    echo json_encode(['success' => true, 'data' => $patient]);
} else {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
} 