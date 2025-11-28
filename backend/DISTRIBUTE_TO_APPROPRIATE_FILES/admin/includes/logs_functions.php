<?php
require_once "db_connect.php";

function checkLogsTable($conn) {
    return ($conn->query("SHOW TABLES LIKE 'logs'")->num_rows > 0);
}

function getLogs($conn) {
    return $conn->query("SELECT * FROM logs ORDER BY timestamp DESC");
}
