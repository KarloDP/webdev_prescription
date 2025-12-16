<?php
session_start();

// 1. MATCHING AUTHENTICATION
// Replaced manual check with the standard require_login used in stock.php
require_once __DIR__ . '/../../../backend/includes/auth.php';
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

require_once __DIR__ . '/../../../backend/includes/db_connect.php';

$activePage = 'stock';
$successMsg = "";
$errorMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $genericName  = trim($_POST['genericName'] ?? '');
    $brandName    = trim($_POST['brandName'] ?? '');
    $form         = trim($_POST['form'] ?? '');
    $strength     = trim($_POST['strength'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $stock        = trim($_POST['stock'] ?? '');

    if ($genericName === '' || $brandName === '' || $form === '' ||
        $strength === '' || $manufacturer === '' || $stock === '' || !is_numeric($stock) || $stock < 0) {

        $errorMsg = "Please fill in all fields correctly.";

    } else {

        $sql = "INSERT INTO medication 
                (genericName, brandName, form, strength, manufacturer, stock)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi",
            $genericName, $brandName, $form, $strength, $manufacturer, $stock
        );

        if ($stmt->execute()) {
            $successMsg = "Medication added successfully!";
        } else {
            $errorMsg = "Failed to add medication.";
        }
    }
}

// -----------------------------
// BEGIN PAGE CONTENT
// -----------------------------
ob_start();
?>

    <link rel="stylesheet" href="../../assets/css/role-pharmacist.css">
    <link rel="stylesheet" href="../../assets/css/table.css">
    <link rel="stylesheet" href="stock.css">
    <link rel="stylesheet" href="add_stock.css">

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
                    <input type="text" name="strength" required>
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
