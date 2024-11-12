<?php
session_start();
require_once "connection/db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit('Unauthorized access');
}

$id = intval($_GET['id']);

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
    u.last_name as physician_lastname
FROM lab_results t
JOIN patients p ON t.patient_id = p.id
JOIN users u ON t.physician_id = u.id
WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    exit('Record not found');
}

// Fetch household members
$householdSql = "SELECT * FROM household_members WHERE treatment_card_id = ?";
$householdStmt = $conn->prepare($householdSql);
$householdStmt->bind_param('i', $id);
$householdStmt->execute();
$household = $householdStmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="card mb-3">
    <div class="card-header">Basic Information</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>TB Case Number</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['case_number']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>Date Card Opened</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['date_opened']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>Region/Province</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['region_province']) ?>" readonly>
                </div>
            </div>
        </div>
        <!-- Add more rows for other basic information -->
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Patient Information</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-2">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['fullname']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label>Age</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['age']) ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label>Gender</label>
                    <input type="text" class="form-control" value="<?= $data['gender'] == 1 ? 'Male' : 'Female' ?>" readonly>
                </div>
            </div>
        </div>
        <!-- Add more rows for other patient information -->
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Clinical Information</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>Bacteriological Status</label>
                    <input type="text" class="form-control" value="<?= $data['bacteriological_status'] ? htmlspecialchars($data['bacteriological_status']) : 'N/A' ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>TB Classification</label>
                    <input type="text" class="form-control" value="<?= $data['tb_classification'] ? htmlspecialchars($data['tb_classification']) : 'N/A' ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label>Diagnosis</label>
                    <input type="text" class="form-control" value="<?= $data['diagnosis'] ? htmlspecialchars($data['diagnosis']) : 'N/A' ?>" readonly>
                </div>
            </div>
        </div>
        <!-- Add more rows for other clinical information -->
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Registration Group</label>
                <input type="text" class="form-control" value="<?= $data['registration_group'] ? htmlspecialchars($data['registration_group']) : 'N/A' ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Treatment Regimen</label>
                <input type="text" class="form-control" value="<?= $data['treatment_regimen'] ? htmlspecialchars($data['treatment_regimen']) : 'N/A' ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Treatment Started Date</label>
                <input type="text" class="form-control" value="<?= $data['treatment_started_date'] ? htmlspecialchars($data['treatment_started_date']) : 'N/A' ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Treatment Outcome</label>
                <input type="text" class="form-control" value="<?= $data['treatment_outcome'] ? htmlspecialchars($data['treatment_outcome']) : 'N/A' ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Treatment Outcome Date</label>
                <input type="text" class="form-control" value="<?= $data['treatment_outcome_date'] ? htmlspecialchars($data['treatment_outcome_date']) : 'N/A' ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label>Source of Patient</label>
                <input type="text" class="form-control" value="<?= $data['source_of_patient'] ? htmlspecialchars($data['source_of_patient']) : 'N/A' ?>" readonly>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($household)): ?>
<div class="card mb-3">
    <div class="card-header">Household Members</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Screened</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($household as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['first_name']) ?></td>
                        <td><?= htmlspecialchars($member['age']) ?></td>
                        <td><?= $member['screened'] ? 'Yes' : 'No' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?> 