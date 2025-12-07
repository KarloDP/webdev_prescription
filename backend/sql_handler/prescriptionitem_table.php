<?php
//CODE BELOW IS CHATGPT GENERATED. NEEDS TO BE REVIEWED AND REFINED
//backend\sql_handler\prescriptionitem_table.php
// handles add, retrieval, and deletion for prescriptionitem table

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$user = require_user();
$role  = $user['role'];
$userID = (int)$user['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET' && isset($_GET['prescriptionID'])) {
        $prescriptionID = (int)$_GET['prescriptionID'];

        $stmt = $conn->prepare("
            SELECT 
                pi.prescriptionItemID,
                pi.prescriptionID,
                pi.medicationID,
                pi.dosage,
                pi.prescribed_amount,
                pi.instructions,
                med.genericName AS medicationName,
                med.strength AS medicationStrength
            FROM prescriptionitem pi
            JOIN medication med ON pi.medicationID = med.medicationID
            WHERE pi.prescriptionID = ?
            ORDER BY pi.prescriptionItemID ASC
        ");
        
        if (!$stmt) throw new Exception("SQL Prepare Failed: " . $conn->error);

        $stmt->bind_param('i', $prescriptionID);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        respond($data);
    } else {
        respond(['error' => 'Method Not Allowed or Invalid Parameters'], 405);
    }
} catch (Exception $e) {
    respond(['error' => 'An internal server error occurred.', 'details' => $e->getMessage()], 500);
}
?>