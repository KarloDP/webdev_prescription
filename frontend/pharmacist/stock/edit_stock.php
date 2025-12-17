<?php
// Start session for data loading (pharmacy_standard.php will handle authentication)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = 'stock';

// Validate medicationID
$medicationID = $_GET['medicationID'] ?? null;
if (!$medicationID || !is_numeric($medicationID)) {
    die("Invalid medication ID.");
}

// Fetch Medication Details via backend handler
$_GET['medicationID'] = $medicationID; // Set for handler
$med = include __DIR__ . '/../../../backend/pharmacist/stock/get_medication.php';

if (!$med || !is_array($med)) {
    die("Medication not found.");
}

// Handle Form Submission via backend handler
$successMsg = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['medicationID'] = $medicationID; // Ensure medicationID is in POST
    $result = include __DIR__ . '/../../../backend/pharmacist/stock/update_stock.php';
    
    if (is_array($result) && isset($result['success'])) {
        if ($result['success']) {
            $successMsg = $result['message'] ?? "Stock updated successfully.";
            // Refresh medication data
            $med = include __DIR__ . '/../../../backend/pharmacist/stock/get_medication.php';
        } else {
            $errorMsg = $result['message'] ?? "Failed to update stock.";
        }
    } else {
        $errorMsg = "An error occurred while processing your request.";
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
