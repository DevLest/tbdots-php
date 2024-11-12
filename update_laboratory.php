<?php
require_once 'connection/db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $data = $_POST;
    $isUpdate = isset($data['id']) && !empty($data['id']);
    
    if ($isUpdate) {
        $treatment_started_date = !empty($data['treatment_started_date']) ? $data['treatment_started_date'] : null;
        $treatment_outcome_date = !empty($data['treatment_outcome_date']) ? $data['treatment_outcome_date'] : null;
        $date_opened = !empty($data['date_opened']) ? $data['date_opened'] : null;

        $sql = "UPDATE lab_results SET 
                case_number = ?,
                date_opened = ?,
                region_province = ?,
                facility_name = ?,
                source_of_patient = ?,
                bacteriological_status = ?,
                tb_classification = ?,
                diagnosis = ?,
                registration_group = ?,
                treatment_regimen = ?,
                treatment_started_date = ?,
                treatment_outcome = ?,
                treatment_outcome_date = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssssssssssi',
            $data['case_number'],
            $date_opened,
            $data['region_province'],
            $data['facility_name'],
            $data['source_of_patient'],
            $data['bacteriological_status'],
            $data['tb_classification'],
            $data['diagnosis'],
            $data['registration_group'],
            $data['treatment_regimen'],
            $treatment_started_date,
            $data['treatment_outcome'],
            $treatment_outcome_date,
            $data['id']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error updating laboratory record: ' . $stmt->error);
        }

        // Log the activity
        $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details) 
                VALUES (?, 'UPDATE', 'lab_results', ?, ?)";
        $stmt = $conn->prepare($sql);
        $details = "Updated laboratory record #" . $data['id'];
        $stmt->bind_param('iis', $_SESSION['user_id'], $data['id'], $details);
        $stmt->execute();

        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 