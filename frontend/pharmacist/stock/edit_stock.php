<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/auth.php';
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

$activePage = 'stock';

// DB CONNECTION
require_once __DIR__ . '/../../../backend/includes/db_connect.php';

// Validate medicationID
$medicationID = $_GET['medicationID'] ?? null;
if (!$medicationID || !is_numeric($medicationID)) {
    die("Invalid medication ID.");
}

// Fetch Medication Details
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

// Handle Form Submission
$successMsg = "";
$errorMsg = "";

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
            $med['stock'] = $updatedStock; // Refresh display
        } else {
            $errorMsg = "Failed to update stock.";
        }
    }
}


// PAGE-SPECIFIC CSS
$pageStyles = '
    <link rel="stylesheet" href="stock.css">
    <link rel="stylesheet" href="edit_stock.css">
';

// START PAGE CONTENT

ob_start();
?>

    <div class="edit-stock-page">

        <h2>Edit Medication Stock</h2>
        <p>Update medication stock and review existing information.</p>

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

// -------------------------------------------------------------
// RENDER WITH PHARMACY LAYOUT
// -------------------------------------------------------------
include __DIR__ . '/../pharmacy_standard.php';
