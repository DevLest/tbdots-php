<?php
session_start();
require_once 'connection/db.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$sql = "SELECT id, fullname FROM patients ORDER BY fullname";
$result = $conn->query($sql);

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

header('Content-Type: application/json');
echo json_encode($patients);