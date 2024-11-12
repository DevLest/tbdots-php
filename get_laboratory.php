<?php
require_once 'connection/db.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Laboratory record ID is required');
    }

    $id = intval($_GET['id']);
    
    $sql = "SELECT lr.*, p.fullname, p.age, p.gender, p.address, p.contact, p.dob,
            p.height, p.bcg_scar, p.occupation, p.phil_health_no, p.contact_person,
            p.contact_person_no
            FROM lab_results lr
            JOIN patients p ON lr.patient_id = p.id 
            WHERE lr.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Laboratory record not found');
    }
    
    $labData = $result->fetch_assoc();
    
    $householdSql = "SELECT * FROM household_members WHERE treatment_card_id = ?";
    $householdStmt = $conn->prepare($householdSql);
    $householdStmt->bind_param("i", $id);
    $householdStmt->execute();
    $householdResult = $householdStmt->get_result();
    
    $labData['household_members'] = [];
    while ($member = $householdResult->fetch_assoc()) {
        $labData['household_members'][] = $member;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $labData
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 