<?php
// Start session for data loading (pharmacy_standard.php will handle authentication)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = 'stock';
$successMsg = "";
$errorMsg = "";

// Handle form submission via backend handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = include __DIR__ . '/../../../backend/pharmacist/stock/add_stock_handler.php';
    
    if (is_array($result) && isset($result['success'])) {
        if ($result['success']) {
            $successMsg = $result['message'] ?? "Medication added successfully!";
        } else {
            $errorMsg = $result['message'] ?? "Failed to add medication.";
        }
    } else {
        $errorMsg = "An error occurred while processing your request.";
    }
}

// Page-specific CSS
$pageStyles = '
    <link rel="stylesheet" href="../../assets/css/role-pharmacist.css">
    <link rel="stylesheet" href="../../assets/css/table.css">
    <link rel="stylesheet" href="stock.css">
    <link rel="stylesheet" href="add_stock.css">
';

// -----------------------------
// BEGIN PAGE CONTENT
// -----------------------------
ob_start();
?>

    <div class="add-stock-page">

        <h2>Add New Medication Stock</h2>
        <p>Fill in the details below to register a new medication in the inventory.</p>

        <?php if ($successMsg): ?>
            <div class="success-message"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="error-message"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="add-stock-container">
            <form method="POST" class="add-stock-form" id="addStockForm">

                <div class="form-group">
                    <label>Generic Name</label>
                    <input type="text" name="genericName" required>
                </div>

                <div class="form-group">
                    <label>Brand Name</label>
                    <input type="text" name="brandName" required>
                </div>

                <div class="form-group">
                    <label>Form</label>
                    <select name="form" required>
                        <option value="">Select form...</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Drop">Drop</option>
                        <option value="Ointment">Ointment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Strength</label>
                    <input type="number" name="strength" min="1" required>
                </div>

                <div class="form-group">
                    <label>Manufacturer</label>
                    <input type="text" name="manufacturer" required>
                </div>

                <div class="form-group">
                    <label>Initial Stock</label>
                    <input type="number" name="stock" min="0" required>
                </div>

                <div class="add-stock-actions">
                    <button type="submit" class="btn-save">Add Medication</button>
                    <a href="stock.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>

        <div class="add-stock-back">
            <a href="stock.php" class="btn-back-outline">← Back to Stock</a>
        </div>

    </div>

    <script src="add_stock.js"></script>

<?php
$pageContent = ob_get_clean();

// 2. MATCHING LAYOUT FILE
// Switched from 'pharmacist_standard.php' to 'pharmacy_standard.php'
// to match stock.php and edit_stock.php
include __DIR__ . '/../pharmacy_standard.php';
?>
