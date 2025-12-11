<?php
/**
 * HANDLER: Get Stock List
 * Returns an array of medication stock records.
 * Used by: frontend/pharmacist/stock/stock.php
 */

if (!defined('DB_HANDLER_INCLUDED')) {
    define('DB_HANDLER_INCLUDED', true);
    include_once(__DIR__ . '/../../includes/db_connect.php');
}

$stockData = [];

$sql = "
    SELECT 
        medicationID,
        genericName,
        brandName,
        form,
        strength,
        manufacturer,
        stock
    FROM medication
    ORDER BY genericName ASC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $stockData[] = $row;
    }

    $stmt->close();
}

return $stockData;
