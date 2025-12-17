<?php
// backend/sql_handler/medication_table.php
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__ . '/../includes/auth.php');

header('Content-Type: application/json; charset=utf-8');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user   = require_user();
$role   = $user['role'];
$userID = (int)$user['id'];

if ($method === 'GET') {
    // Return full medication list for dropdown/search
    $sql = "
        SELECT 
            medicationID,
            genericName,
            brandName,
            strength,
            form,
            manufacturer,
            stock
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
} else {
    respond(['error' => 'Unsupported request method'], 405);
}
