<?php
session_start();

include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');

// Redirect if pharmacist is not logged in
if (!isset($_SESSION['pharmacistID'])) {
    header("Location: ../TestLoginPharmacist.php");
    exit;
}

$activePage = 'stock';
$pharmacistName = $_SESSION['pharmacist_name'] ?? 'Pharmacist';

// Validate medicationID
$medicationID = $_GET['medicationID'] ?? null;
if (!$medicationID || !is_numeric($medicationID)) {
    die("Invalid medication ID.");
}

// Fetch medication details
$query = "
    SELECT medicationID, genericName, brandName, form, strength, manufacturer, stock
    FROM medication
    WHERE medicationID = ?
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $medicationID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Medication not found.");
}

$med = $result->fetch_assoc();
$successMsg = "";
$errorMsg = "";

// Handle stock update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedStock = $_POST['stock'] ?? null;

    if ($updatedStock === null || !is_numeric($updatedStock) || $updatedStock < 0) {
        $errorMsg = "Invalid stock value.";
    } else {
        $updateSQL = "UPDATE medication SET stock = ? WHERE medicationID = ?";
        $updateStmt = $conn->prepare($updateSQL);
        $updateStmt->bind_param("ii", $updatedStock, $medicationID);

        if ($updateStmt->execute()) {
            $successMsg = "Stock updated successfully.";
            $med['stock'] = $updatedStock; // Refresh displayed value
        } else {
            $errorMsg = "Failed to update stock.";
        }
    }
}

// -----------------------------
// BEGIN PAGE CONTENT
// -----------------------------
ob_start();
?>

<link rel="stylesheet" href="../../assets/css/table.css">
<link rel="stylesheet" href="../../assets/css/role-pharmacist.css">
<link rel="stylesheet" href="stock.css">
<link rel="stylesheet" href="edit_stock.css">

<div class="edit-stock-page">

    <h2>Edit Medication Stock</h2>
    <p>Review the medication details and adjust the stock level as needed.</p>

    <?php if (!empty($successMsg)): ?>
        <div class="success-message"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="error-message"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="edit-stock-container">
        <form method="POST" class="edit-stock-form">

            <table class="table-base">
                <tbody>
                <tr>
                    <th>Medication ID</th>
                    <td class="value-cell"><?= $med['medicationID'] ?></td>
                </tr>

                <tr>
                    <th>Generic Name</th>
                    <td class="value-cell"><?= htmlspecialchars($med['genericName']) ?></td>
                </tr>

                <tr>
                    <th>Brand Name</th>
                    <td class="value-cell"><?= htmlspecialchars($med['brandName']) ?></td>
                </tr>

                <tr>
                    <th>Form</th>
                    <td class="value-cell"><?= htmlspecialchars($med['form']) ?></td>
                </tr>

                <tr>
                    <th>Strength</th>
                    <td class="value-cell"><?= htmlspecialchars($med['strength']) ?></td>
                </tr>

                <tr>
                    <th>Manufacturer</th>
                    <td class="value-cell"><?= htmlspecialchars($med['manufacturer']) ?></td>
                </tr>

                <tr>
                    <th>Stock</th>
                    <td class="value-cell">
                        <input type="number" name="stock" min="0"
                               value="<?= htmlspecialchars($med['stock']) ?>">
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="edit-stock-actions">
                <button type="submit" class="btn-save">Update Stock</button>
                <a href="stock.php" class="btn-cancel">Cancel</a>
            </div>

        </form>
    </div>

    <div class="edit-stock-back">
        <a href="stock.php" class="btn-back-outline">← Back to Stock</a>
    </div>

</div>

<script src="edit_stock.js"></script>

<?php
$pageContent = ob_get_clean();

// Render with global pharmacist layout
$layout = __DIR__ . '/../pharmacist_standard.php';
if (file_exists($layout)) {
    include $layout;
} else {
    echo $pageContent;
}
?>
