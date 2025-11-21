<?php
// handles add data retrieval and insertion to the database
include(__DIR__ . '/../includes/db_connect.php');
include(__DIR__.'/../includes/auth.php');

function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];

$user = require_user();
$role = $user['role'];
$userID = (int)$user['id'];

if ($method === 'GET') {}
else if ($method === 'POST') {}
else if ($method === 'DELETE') {}
?>