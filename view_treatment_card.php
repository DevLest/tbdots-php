<?php
require_once('includes/config.php');
require_once('includes/auth.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get laboratory record with patient and physician info
    $sql = "SELECT lr.*, p.fullname as patient_name, u.first_name, u.last_name 
            FROM lab_results lr
            JOIN patients p ON lr.patient_id = p.id
            JOIN users u ON lr.physician_id = u.id
            WHERE lr.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    
    if ($record) {
        // Format the data for display
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h4>Patient Information</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($record['patient_name']); ?></p>
                    <p><strong>Case Number:</strong> <?php echo htmlspecialchars($record['case_number']); ?></p>
                    <p><strong>Date Opened:</strong> <?php echo date('F d, Y', strtotime($record['date_opened'])); ?></p>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4>Treatment Details</h4>
                    <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($record['diagnosis']); ?></p>
                    <p><strong>Treatment Regimen:</strong> <?php echo htmlspecialchars($record['treatment_regimen']); ?></p>
                    <p><strong>Treatment Started:</strong> <?php echo $record['treatment_started_date'] ? date('F d, Y', strtotime($record['treatment_started_date'])) : 'Not started'; ?></p>
                    <p><strong>Treatment Outcome:</strong> <?php echo htmlspecialchars($record['treatment_outcome'] ?? 'Ongoing'); ?></p>
                </div>
            </div>
            
            <!-- Add more sections as needed -->
        </div>
        <?php
    } else {
        echo "<div class='alert alert-danger'>Record not found</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No record ID provided</div>";
}
?> 