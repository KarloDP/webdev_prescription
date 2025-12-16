<?php
require_once "../includes/db_connect.php";
require_once "../includes/logs_functions.php";
require_once "../includes/logs_table.php";

$hasLogs = checkLogsTable($conn);
if ($hasLogs) {
    $result = getLogs($conn);
}

$title = "System Logs";
include "logs.html";
