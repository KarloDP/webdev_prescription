<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $user = require_role(['pharmacist']);
    $pharmacistID = (int)$user['id'];

    $stats = [
        'pending_count'   => 0,
        'dispensed_count' => 0,
        'active_count'    => 0,
        'expiring_count'  => 0
    ];

    // 1. Pending Prescriptions Count (Active status)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM prescription 
        WHERE status = 'Active'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $stats['pending_count'] = (int) $result->fetch_assoc()['count'];
    }

    // 2. Dispensed Prescriptions Count (by this pharmacy)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM dispenserecord 
        WHERE pharmacyID = ?
    ");
    $stmt->bind_param('i', $pharmacistID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $stats['dispensed_count'] = (int) $result->fetch_assoc()['count'];
    }

    // 3. Active Prescriptions Count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM prescription 
        WHERE status = 'Active'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $stats['active_count'] = (int) $result->fetch_assoc()['count'];
    }

    // 4. Expiring Soon (within next 7 days)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM prescription 
        WHERE status = 'Active' 
        AND expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $stats['expiring_count'] = (int) $result->fetch_assoc()['count'];
    }

    echo json_encode($stats);
    
    if (isset($stmt)) $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}
?>