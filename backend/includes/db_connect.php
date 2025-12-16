<?php

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $servername = getenv('DB_HOST') ?: '127.0.0.1';
    $username   = getenv('DB_USER') ?: 'root';
    $password   = getenv('DB_PASS') ?: '';
    $database   = getenv('DB_NAME') ?: 'webdev_prescription';
    $port       = getenv('DB_PORT') ?: 3306;

    try {
        $conn = new mysqli($servername, $username, $password, $database, (int)$port);
        $conn->set_charset('utf8mb4');
    } catch (Throwable $e) {
        error_log('DB connect error: ' . $e->getMessage());
        http_response_code(500);
        exit('Database connection error.');
    }
