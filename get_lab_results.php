<?php
require_once "connection/db.php";

if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
    
    $sql = "SELECT 
              t.case_number,
              t.bacteriological_status,
              t.diagnosis,
              t.treatment_regimen,
              t.treatment_outcome,
              DATE_FORMAT(t.created_at, '%Y-%m-%d') as created_at
            FROM lab_results t
            WHERE t.patient_id = ?
            ORDER BY t.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lab_results = [];
    while ($row = $result->fetch_assoc()) {
        $lab_results[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($lab_results);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Patient ID is required']);
}
?> 