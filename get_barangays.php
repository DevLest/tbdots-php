<?php
require_once "connection/db.php";

$municipalityId = isset($_GET['municipality_id']) ? (int)$_GET['municipality_id'] : 0;

if ($municipalityId > 0) {
    $query = "SELECT b.id, b.name 
              FROM barangays b 
              JOIN locations l ON l.barangay_id = b.id 
              WHERE l.municipality_id = ? 
              ORDER BY b.name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $municipalityId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $barangays = [];
    while ($row = $result->fetch_assoc()) {
        $barangays[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($barangays);
} 