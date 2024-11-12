<?php
session_start();
require_once "connection/db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['patient_id'])) {
    exit('Unauthorized access');
}

$patient_id = intval($_GET['patient_id']);

// Fetch treatment card data with related information
$sql = "SELECT 
    t.*,
    p.fullname,
    p.age,
    p.gender,
    p.address,
    p.contact,
    p.dob,
    p.height,
    p.bcg_scar,
    p.occupation,
    p.phil_health_no,
    p.contact_person,
    p.contact_person_no,
    u.first_name as physician_name,
    u.last_name as physician_lastname,
    dh.has_history,
    dh.duration as drug_duration,
    dh.drugs_taken
FROM tb_treatment_cards t
JOIN patients p ON t.patient_id = p.id
JOIN users u ON t.physician_id = u.id
LEFT JOIN drug_histories dh ON dh.treatment_card_id = t.id
WHERE t.patient_id = ?
ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit('Record not found');
}

// Fetch all treatment cards for this patient
$treatmentCards = $result->fetch_all(MYSQLI_ASSOC);

// Output each treatment card
foreach ($treatmentCards as $data) {
    // Fetch clinical examinations for this treatment card
    $examSql = "SELECT * FROM clinical_examinations WHERE treatment_card_id = ? ORDER BY examination_date";
    $examStmt = $conn->prepare($examSql);
    $examStmt->bind_param('i', $data['id']);
    $examStmt->execute();
    $examinations = $examStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch household members for this treatment card
    $householdSql = "SELECT * FROM household_members WHERE treatment_card_id = ?";
    $householdStmt = $conn->prepare($householdSql);
    $householdStmt->bind_param('i', $data['id']);
    $householdStmt->execute();
    $household = $householdStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class="card mb-4">
        <div class="card-header">
            Treatment Card #<?= htmlspecialchars($data['case_number']) ?>
            <small class="text-muted float-end">
                Created: <?= date('M d, Y', strtotime($data['created_at'])) ?>
            </small>
        </div>
        <div class="card-body">
            <!-- Basic Information -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>TB Case Number:</strong> <?= htmlspecialchars($data['case_number']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Date Opened:</strong> <?= htmlspecialchars($data['date_opened']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Region/Province:</strong> <?= htmlspecialchars($data['region_province']) ?>
                </div>
            </div>

            <!-- Treatment Details -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Bacteriological Status:</strong> <?= htmlspecialchars($data['bacteriological_status'] ?? 'N/A') ?>
                </div>
                <div class="col-md-4">
                    <strong>Diagnosis:</strong> <?= htmlspecialchars($data['diagnosis'] ?? 'N/A') ?>
                </div>
                <div class="col-md-4">
                    <strong>Treatment Regimen:</strong> <?= htmlspecialchars($data['treatment_regimen'] ?? 'N/A') ?>
                </div>
            </div>

            <!-- Clinical Examinations -->
            <?php if (!empty($examinations)): ?>
            <h6 class="mt-4 mb-3">Clinical Examinations</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight</th>
                            <th>Symptoms</th>
                            <th>Side Effects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($examinations as $exam): ?>
                        <tr>
                            <td><?= htmlspecialchars($exam['examination_date']) ?></td>
                            <td><?= htmlspecialchars($exam['weight']) ?> kg</td>
                            <td>
                                <?php
                                $symptoms = [];
                                if ($exam['unexplained_fever']) $symptoms[] = 'Fever';
                                if ($exam['unexplained_cough']) $symptoms[] = 'Cough';
                                if ($exam['unimproved_wellbeing']) $symptoms[] = 'Poor well-being';
                                if ($exam['poor_appetite']) $symptoms[] = 'Poor appetite';
                                echo implode(', ', $symptoms) ?: 'None';
                                ?>
                            </td>
                            <td><?= htmlspecialchars($exam['side_effects'] ?: 'None') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php
}
?>