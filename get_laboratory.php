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
    
    // Get clinical examinations
    $stmt = $conn->prepare("SELECT * FROM clinical_examinations 
                           WHERE lab_results_id = ? 
                           ORDER BY examination_date");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $clinicalData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get drug administrations
    $stmt = $conn->prepare("SELECT * FROM drug_administrations 
                           WHERE lab_results_id = ? 
                           ORDER BY administration_date");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $drugData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $response = [
        'success' => true,
        'data' => $labData,
        'clinical_data' => $clinicalData,
        'drug_data' => $drugData
    ];
    
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 