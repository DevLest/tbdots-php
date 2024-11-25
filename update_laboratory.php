<?php
// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'connection/db.php';
session_start();

// Ensure we're sending JSON response headers
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $conn->begin_transaction();
    
    $data = $_POST;
    $isUpdate = isset($data['id']) && !empty($data['id']);
    
    // First handle the main lab_results update
    if ($isUpdate) {
        $treatment_started_date = !empty($data['treatment_started_date']) ? $data['treatment_started_date'] : null;
        $treatment_outcome_date = !empty($data['treatment_outcome_date']) ? $data['treatment_outcome_date'] : null;
        $date_opened = !empty($data['date_opened']) ? $data['date_opened'] : null;

        // Validate bacteriological_status value
        $valid_bacteriological_status = ['Bacteriologically Confirmed', 'Clinically Diagnosed'];
        if (!empty($data['bacteriological_status']) && !in_array($data['bacteriological_status'], $valid_bacteriological_status)) {
            throw new Exception('Invalid bacteriological status value');
        }

        // Validate tb_classification value
        $valid_tb_classification = ['Pulmonary TB', 'Extra Pulmonary TB'];
        if (!empty($data['tb_classification']) && !in_array($data['tb_classification'], $valid_tb_classification)) {
            throw new Exception('Invalid TB classification value');
        }

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
            throw new Exception($stmt->error);
        }

        // Delete existing clinical examinations and drug administrations
        $stmt = $conn->prepare("DELETE FROM clinical_examinations WHERE lab_results_id = ?");
        $stmt->bind_param('i', $data['id']);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM drug_administrations WHERE lab_results_id = ?");
        $stmt->bind_param('i', $data['id']);
        $stmt->execute();

        // Insert new clinical examinations
        for ($i = 0; $i <= 10; $i++) {
            if (!empty($data['weight'][$i])) {
                $clinicalSql = "INSERT INTO clinical_examinations (
                    lab_results_id,
                    examination_date,
                    weight,
                    unexplained_fever,
                    unexplained_cough,
                    unimproved_wellbeing,
                    poor_appetite,
                    positive_pe_findings,
                    side_effects
                ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($clinicalSql);
                
                // Process checkboxes
                $fever = isset($data['fever_' . $i]) ? 1 : 0;
                $cough = isset($data['cough_' . $i]) ? 1 : 0;
                $wellbeing = isset($data['well_being_' . $i]) ? 1 : 0;
                $appetite = isset($data['appetite_' . $i]) ? 1 : 0;
                $peFindings = isset($data['pe_findings_' . $i]) ? 1 : 0;
                $sideEffects = $data['side_effects'][$i] ?? '';

                $stmt->bind_param('idiiiiis',
                    $data['id'],
                    $data['weight'][$i],
                    $fever,
                    $cough,
                    $wellbeing,
                    $appetite,
                    $peFindings,
                    $sideEffects
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error saving clinical examination: " . $stmt->error);
                }
            }
        }

        // Insert new drug administrations
        for ($i = 0; $i <= 10; $i++) {
            if (!empty($data['isoniazid'][$i]) || 
                !empty($data['rifampicin'][$i]) || 
                !empty($data['pyrazinamide'][$i]) || 
                !empty($data['ethambutol'][$i]) || 
                !empty($data['streptomycin'][$i])) {

                $drugSql = "INSERT INTO drug_administrations (
                    lab_results_id,
                    drug_name,
                    dosage,
                    initial
                ) VALUES 
                (?, 'Isoniazid', ?, ?),
                (?, 'Rifampicin', ?, ?),
                (?, 'Pyrazinamide', ?, ?),
                (?, 'Ethambutol', ?, ?),
                (?, 'Streptomycin', ?, ?)";

                $stmt = $conn->prepare($drugSql);

                $stmt->bind_param('idsidsidsidsids',
                    $data['id'],
                    $data['isoniazid_dosage'],
                    $data['isoniazid'][$i],
                    $data['id'],
                    $data['rifampicin_dosage'],
                    $data['rifampicin'][$i],
                    $data['id'],
                    $data['pyrazinamide_dosage'],
                    $data['pyrazinamide'][$i],
                    $data['id'],
                    $data['ethambutol_dosage'],
                    $data['ethambutol'][$i],
                    $data['id'],
                    $data['streptomycin_dosage'],
                    $data['streptomycin'][$i]
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error saving drug administration: " . $stmt->error);
                }
            }
        }

        // Log the activity
        $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details) 
                VALUES (?, 'UPDATE', 'lab_results', ?, ?)";
        $stmt = $conn->prepare($sql);
        $details = "Updated laboratory record #" . $data['id'];
        $stmt->bind_param('iis', $_SESSION['user_id'], $data['id'], $details);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $conn->commit();
        echo json_encode(['success' => true]);
        exit;
    } else {
        throw new Exception('Invalid update operation');
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage()); // Log the error server-side
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    exit;
} 