<?php
include 'includes/db_connect.php';

$medicationID = $_POST['medicationID'];
$amountGiven = $_POST['amountGiven'];

$sql = "SELECT stock FROM stock WHERE medicationID = '$medicationID'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) { // checks if medication exists
    $row = mysqli_fetch_assoc($result);
    $currentStock = $row['stock'];

    if($amountGiven > $currentStock) { // checks if enough stock is available
        echo "Error: Not enough stock available.";
        exit();
    } else {
        $newStockAmount = $currentStock - $amountGiven;
        $newStockAmount = $currentStock - $amountGiven;
        $sql = "UPDATE stock SET stock_amount='$newStockAmount' WHERE medicationID='$medicationID'";
        if ($conn->query($sql) === TRUE) {
            echo "Stock updated successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    echo "Error: Medication not found.";
}
?>