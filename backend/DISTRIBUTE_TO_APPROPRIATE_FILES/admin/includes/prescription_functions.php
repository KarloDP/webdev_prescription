<?php
require_once "db_connect.php";

function deletePrescription($id, $conn) {
    $id = intval($id);
    if ($id > 0) {
        $conn->query("DELETE FROM prescription WHERE prescriptionID = $id");
    }
}

function savePrescription($post, $conn) {
    $id        = intval($post['id'] ?? 0);
    $med       = intval($post['medicationID'] ?? 0);
    $patient   = intval($post['patientID'] ?? 0);
    $issueDate = $conn->real_escape_string($post['issueDate'] ?? '');
    $expDate   = $conn->real_escape_string($post['expirationDate'] ?? '');
    $status    = $conn->real_escape_string($post['status'] ?? '');

    if ($id > 0) {
        $conn->query("UPDATE prescription 
                      SET medicationID=$med, patientID=$patient, issueDate='$issueDate', 
                          expirationDate='$expDate', status='$status'
                      WHERE prescriptionID=$id");
    } else {
        $conn->query("INSERT INTO prescription (medicationID, patientID, issueDate, expirationDate, status)
                      VALUES ($med, $patient, '$issueDate', '$expDate', '$status')");
    }
}
