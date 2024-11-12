<?php
function logActivity($conn, $user_id, $action, $table_name, $record_id, $details) {
    $sql = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $user_id, $action, $table_name, $record_id, $details);
    return $stmt->execute();
}
?> 