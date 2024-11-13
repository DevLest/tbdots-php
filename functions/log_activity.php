<?php

function logActivity($conn, $userId, $action, $tableName, $recordId, $details) {
    // Ensure the action is one of the allowed ENUM values
    $allowedActions = ['CREATE', 'UPDATE', 'DELETE'];
    if (!in_array($action, $allowedActions)) {
        throw new Exception("Invalid action: $action");
    }

    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issis', $userId, $action, $tableName, $recordId, $details);
    $stmt->execute();
}
?> 