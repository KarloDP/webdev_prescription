<?php
require_once "db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getDashboardTotals($conn) {
    return [
        'doctors_active'  => $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='active'")->fetch_assoc()['total'],
        'doctors_pending' => $conn->query("SELECT COUNT(*) AS total FROM doctor WHERE status='pending'")->fetch_assoc()['total'],
        'medicines'       => $conn->query("SELECT COUNT(*) AS total FROM medication")->fetch_assoc()['total'],
        'prescriptions'   => $conn->query("SELECT COUNT(*) AS total FROM prescription")->fetch_assoc()['total'],
        'pharmacys'       => $conn->query("SELECT COUNT(*) AS total FROM pharmacy")->fetch_assoc()['total'],
    ];
}
