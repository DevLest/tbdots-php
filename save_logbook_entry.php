<?php
session_start();
require_once('connection.php');

// Check if user is logged in and has physician role
if (!isset($_SESSION['user_id']) || !in_array(3, $_SESSION['role_modules'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $physician_id = $_SESSION['user_id'];
    $notes = $_POST['notes'];
    $log_date = $_POST['log_date'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO patient_logbook (patient_id, physician_id, notes, log_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $patient_id, $physician_id, $notes, $log_date);
        
        if ($stmt->execute()) {
            // Log the activity
            $activity_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, details) VALUES (?, 'CREATE', 'patient_logbook', ?, ?)");
            $details = "Added logbook entry for patient #" . $patient_id;
            $record_id = $stmt->insert_id;
            $activity_stmt->bind_param("iis", $_SESSION['user_id'], $record_id, $details);
            $activity_stmt->execute();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 