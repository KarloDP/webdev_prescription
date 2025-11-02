<?php
include '../includes/db_connect.php';

if (!isset($_POST['medicationID']) || trim($_POST['medicationID']) === '') {
    http_response_code(400);
    echo "Error: medicationID is required.";
    exit;
}

$medicationID = (int)$_POST['medicationID'];

$stmt = $conn->prepare("DELETE FROM medication WHERE medicationID = ?");
$stmt->bind_param("i", $medicationID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Medication ID $medicationID deleted successfully.";
    } else {
        echo "No row found with medicationID $medicationID.";
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>