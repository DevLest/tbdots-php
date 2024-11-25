<?php
session_start();
require_once "connection/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_duplicate'])) {
    $fullname = trim($_POST['fullname']);
    $dob = trim($_POST['dob']);
    $location = trim($_POST['location']);
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;

    $sql = "SELECT id FROM patients WHERE fullname = ? AND dob = ? AND location_id = ?";
    if ($id) {
        $sql .= " AND id != ?";
    }

    $stmt = $conn->prepare($sql);
    
    if ($id) {
        $stmt->bind_param('ssii', $fullname, $dob, $location, $id);
    } else {
        $stmt->bind_param('ssi', $fullname, $dob, $location);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode([
        'exists' => $result->num_rows > 0
    ]);
    exit;
} 