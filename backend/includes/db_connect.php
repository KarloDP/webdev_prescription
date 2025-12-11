<?php
$servername = "127.0.0.1";  // match the service name in docker-compose
$username   = "root";
$password   = "";
$database   = "webdev_prescription";
$port       = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log('DB connect error: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection error.');
}
