<?php
require_once 'config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

try {
    $sql = "SELECT * FROM patients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    
    if (!$patient) {
        throw new Exception('Patient not found');
    }
    
    echo json_encode($patient);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 