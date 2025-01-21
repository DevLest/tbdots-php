<?php
session_start();
require_once('connection/db.php');

header('Content-Type: application/json');

function debug_log($message, $data = null) {
    error_log("DEBUG: " . $message . ($data ? " - " . json_encode($data) : ""));
}

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No ID provided'
    ]);
    exit;
}

try {
    debug_log("Fetching laboratory record for ID: " . $_GET['id']);

    // Main query
    $sql = "SELECT 
        l.*,
        p.fullname, p.gender as sex, p.age, p.bcg_scar,
        p.height,
        dh.has_history, dh.duration, dh.drugs_taken,
        CONCAT(b.name, ', ', m.location) as address
        FROM lab_results l 
        LEFT JOIN patients p ON l.patient_id = p.id
        LEFT JOIN locations loc ON p.location_id = loc.id
        LEFT JOIN municipalities m ON loc.municipality_id = m.id
        LEFT JOIN barangays b ON loc.barangay_id = b.id
        LEFT JOIN drug_histories dh ON l.id = dh.lab_results_id
        WHERE l.id = ? 
        ORDER BY l.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $labData = $result->fetch_assoc();

    debug_log("Main laboratory data retrieved", $labData);

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
    debug_log("DSSM results retrieved", $labData['dssm_results']);

    // Get household members
    $householdSql = "SELECT first_name, age, screened 
                     FROM household_members 
                     WHERE lab_results_id = ?";
    $householdStmt = $conn->prepare($householdSql);
    $householdStmt->bind_param("i", $_GET['id']);
    $householdStmt->execute();
    $householdResult = $householdStmt->get_result();
    $labData['household_members'] = [];
    while ($row = $householdResult->fetch_assoc()) {
        $labData['household_members'][] = $row;
    }
    debug_log("Household members retrieved", $labData['household_members']);

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
    debug_log("Drug dosages retrieved", $labData['drug_dosages']);

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
    debug_log("Clinical examinations retrieved", $labData['clinical_examinations']);

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $labData
    ]);

} catch (Exception $e) {
    debug_log("Error occurred: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 