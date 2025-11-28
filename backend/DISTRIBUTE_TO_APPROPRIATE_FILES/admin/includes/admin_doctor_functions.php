<?php
require_once "db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

//accept doctor
function acceptDoctor($id, $conn) {
    $id = intval($id);
    if ($id > 0) {
        $conn->query("UPDATE doctor SET status='active' WHERE doctorID = $id");
    }
    header("Location: users.php?filter=pending");
    exit;
}

//delete doctor
function deleteDoctor($id, $ref, $conn) {
    $id = intval($id);
    if ($id > 0) {
        $conn->query("DELETE FROM doctor WHERE doctorID = $id");
    }
    header("Location: users.php?filter=" . urlencode($ref));
    exit;
}

//update doctor
function updateDoctor($post, $conn) {
    $id       = intval($post['doctorID']);
    $first    = $conn->real_escape_string($post['firstName']);
    $last     = $conn->real_escape_string($post['lastName']);
    $spec     = $conn->real_escape_string($post['specialization']);
    $license  = $conn->real_escape_string($post['licenseNumber']);
    $email    = $conn->real_escape_string($post['email']);
    $clinic   = $conn->real_escape_string($post['clinicAddress']);

    $sql = "UPDATE doctor 
            SET firstName='$first', lastName='$last', specialization='$spec',
                licenseNumber='$license', email='$email', clinicAddress='$clinic'
            WHERE doctorID=$id";
    $conn->query($sql);

    header("Location: users.php?filter=" . urlencode($_GET['filter'] ?? 'active'));
    exit;
}
