<?php
session_start();
require_once('connection/db.php');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No ID provided'
    ]);
    exit;
}

try {
    // Get the main laboratory record
    $sql = "SELECT l.*, p.fullname, p.gender as sex, p.age, p.address, p.bcg_scar,
            p.height, p.occupation, p.phil_health_no, p.contact_person, 
            p.contact_person_no as contact_number,
            h.*,
            dh.has_history, dh.duration, dh.drugs_taken
            FROM lab_results l 
            LEFT JOIN patients p ON l.patient_id = p.id 
            LEFT JOIN household_members h ON l.id = h.lab_results_id
            LEFT JOIN drug_histories dh ON l.id = dh.lab_results_id
            WHERE l.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $labData = $result->fetch_assoc();

    if (!$labData) {
        throw new Exception("Laboratory record not found");
    }

    // Get DSSM results
    $dssmSql = "SELECT * FROM dssm_results WHERE lab_results_id = ? ORDER BY month";
    $dssmStmt = $conn->prepare($dssmSql);
    $dssmStmt->bind_param("i", $_GET['id']);
    $dssmStmt->execute();
    $dssmResult = $dssmStmt->get_result();
    $labData['dssm_results'] = [];
    while ($row = $dssmResult->fetch_assoc()) {
        $labData['dssm_results'][] = $row;
    }

    // Get clinical examinations
    $examSql = "SELECT * FROM clinical_examinations WHERE lab_results_id = ? ORDER BY examination_date";
    $examStmt = $conn->prepare($examSql);
    $examStmt->bind_param("i", $_GET['id']);
    $examStmt->execute();
    $examResult = $examStmt->get_result();
    $labData['clinical_examinations'] = [];
    while ($row = $examResult->fetch_assoc()) {
        $labData['clinical_examinations'][] = $row;
    }

    // Get drug dosages
    $dosageSql = "SELECT * FROM drug_dosages WHERE lab_results_id = ?";
    $dosageStmt = $conn->prepare($dosageSql);
    $dosageStmt->bind_param("i", $_GET['id']);
    $dosageStmt->execute();
    $dosageResult = $dosageStmt->get_result();
    $labData['drug_dosages'] = [];
    while ($row = $dosageResult->fetch_assoc()) {
        $labData['drug_dosages'][] = $row;
    }

    // Return success response
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
?> 