<?php
/**
 * HANDLER: Get Single Medication
 * Returns medication details by ID.
 * Used by: frontend/pharmacist/stock/edit_stock.php
 */

if (!defined('DB_HANDLER_INCLUDED')) {
    define('DB_HANDLER_INCLUDED', true);
    include_once(__DIR__ . '/../../includes/db_connect.php');
    include_once(__DIR__ . '/../../includes/auth.php');
}

// Authentication check
if (!is_logged_in()) {
    return null;
}
$user = $_SESSION['user'] ?? [];
$role = strtolower($user['role'] ?? '');
if ($role !== 'pharmacist') {
    return null;
}

// Get pharmacyID from session (for pharmacist, id = pharmacyID)
$pharmacyID = (int)($user['id'] ?? 0);
if ($pharmacyID <= 0) {
    return null;
}

$medicationID = isset($_GET['medicationID']) ? (int)$_GET['medicationID'] : 0;

if ($medicationID <= 0) {
    return null;
}

// Query medication with pharmacy-specific stock from pharmacy_medication
$query = "
    SELECT 
        m.medicationID, 
        m.genericName, 
        m.brandName, 
        m.form, 
        m.strength, 
        m.manufacturer, 
        COALESCE(pm.stock, 0) AS stock
    FROM medication m
    LEFT JOIN pharmacy_medication pm 
        ON m.medicationID = pm.medicationID 
        AND pm.pharmacyID = ?
    WHERE m.medicationID = ?
    LIMIT 1
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    return null;
}

$stmt->bind_param("ii", $pharmacyID, $medicationID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    return null;
}

$medication = $result->fetch_assoc();
$stmt->close();

return $medication;

