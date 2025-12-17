<?php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$user = require_user();
$role = $user['role'];

if (!in_array($role, ['doctor', 'admin', 'pharmacist'], true)) {
    respond(['error' => 'Forbidden'], 403);
}

$sql = "
    SELECT medicationID, genericName, brandName, form, strength, manufacturer, stock
    FROM medication
    ORDER BY genericName ASC
";

$result = $conn->query($sql);   
if ($result) {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
} else {
    respond(['error' => 'Query failed: ' . $conn->error], 500);
}