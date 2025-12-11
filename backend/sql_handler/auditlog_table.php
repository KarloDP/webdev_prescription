<?php
// filepath: c:\wamp64\www\WebDev_Prescription\backend\sql_handler\auditlog_table.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    // Check if user is logged in
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        throw new Exception('Not authenticated', 401);
    }
    
    // Only pharmacist can view logs (or adjust as needed)
    if (!in_array($user['role'], ['pharmacist', 'admin'])) {
        throw new Exception('Unauthorized', 403);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed', 405);
    }

    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection failed', 500);
    }

    // For pharmacists: only show pharmacist role logs
    // For admins: show all logs
    if ($user['role'] === 'pharmacist') {
        $stmt = $conn->prepare("
            SELECT logID, userID, role, action, details, createdAt
            FROM auditlog
            WHERE role = 'pharmacist'
            ORDER BY createdAt DESC
            LIMIT 500
        ");
    } else {
        // Admin sees all
        $stmt = $conn->prepare("
            SELECT logID, userID, role, action, details, createdAt
            FROM auditlog
            ORDER BY createdAt DESC
            LIMIT 500
        ");
    }
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error, 500);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    
    $stmt->close();
    respond($rows);
    
} catch (Throwable $e) {
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    respond(['error' => true, 'message' => $e->getMessage()], $code);
}
?>