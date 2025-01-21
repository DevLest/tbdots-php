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

// Get patient details
$stmt = $conn->prepare("
    SELECT p.*, l.name as barangay_name, m.location as municipality_name 
    FROM patients p 
    LEFT JOIN locations loc ON p.location_id = loc.id
    LEFT JOIN barangays l ON loc.barangay_id = l.id
    LEFT JOIN municipalities m ON loc.municipality_id = m.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

// Get inventory transactions
$transactionStmt = $conn->prepare("
    SELECT 
        it.*, 
        p.brand_name,
        p.generic_name,
        p.unit_of_measure,
        u.first_name,
        u.last_name,
        CONCAT(u.first_name, ' ', u.last_name) as staff_name
    FROM inventory_transactions it
    JOIN products p ON it.product_id = p.id
    JOIN users u ON it.user_id = u.id
    WHERE it.patient_id = ?
    ORDER BY it.transaction_date DESC
");
$transactionStmt->bind_param('i', $patientId);
$transactionStmt->execute();
$transactionsResult = $transactionStmt->get_result();
$transactions = [];
while ($row = $transactionsResult->fetch_assoc()) {
    $transactions[] = $row;
}

if ($patient) {
    echo json_encode([
        'success' => true,
        'data' => $patient,
        'transactions' => $transactions
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
} 