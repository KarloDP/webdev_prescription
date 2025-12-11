<?php
// Add this function to log audit events

/**
 * Log an audit event to the auditlog table
 */
function log_audit($conn, $userID, $role, $action, $details = '') {
    if (!($conn instanceof mysqli)) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO auditlog (userID, role, action, details, createdAt)
            VALUES (?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            return false;
        }
        
        $userID = (int)$userID;
        $stmt->bind_param('isss', $userID, $role, $action, $details);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>