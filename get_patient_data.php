<?php
require_once 'connection/db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

try {
    $sql = "SELECT p.*, 
            CONCAT(b.name, ', ', m.location) as address 
            FROM patients p 
            LEFT JOIN locations l ON p.location_id = l.id 
            LEFT JOIN municipalities m ON l.municipality_id = m.id 
            LEFT JOIN barangays b ON l.barangay_id = b.id 
            WHERE p.id = ?";
            
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