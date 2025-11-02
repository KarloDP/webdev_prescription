<?php
include '../includes/db_connect.php';

if (!isset($_POST['medicationID'], $_POST['amountGiven'])) {
    echo "Missing fields";
    exit;
}

$medicationID = (int)$_POST['medicationID'];
$amountGiven = (int)$_POST['amountGiven'];

if ($amountGiven <= 0) {
    echo "Invalid amount";
    exit;
}

$conn->begin_transaction();
$stmt = $conn->prepare("SELECT stock FROM medication WHERE medicationID = ?");
$stmt->bind_param("i", $medicationID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Error: Medication not found.";
    $conn->rollback();
    exit;
}

$row = $result->fetch_assoc();
$currentStock = (int)$row['stock'];

if ($amountGiven > $currentStock) {
    echo "Error: Not enough stock available.";
    $conn->rollback();
    exit;
}

$newStock = $currentStock - $amountGiven;
$update = $conn->prepare("UPDATE medication SET stock = ? WHERE medicationID = ?");
$update->bind_param("ii", $newStock, $medicationID);

if ($update->execute()) {
    $conn->commit();
    echo "Stock updated successfully.";
} else {
    $conn->rollback();
    echo "Error updating stock.";
}

$stmt->close();
$update->close();
$conn->close();
?>
