<?php
session_start();

include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');

// Redirect if pharmacist not logged in
if (!isset($_SESSION['pharmacistID'])) {
    header("Location: ../TestLoginPharmacist.php");
    exit;
}

$activePage = 'stock';
$pharmacistName = $_SESSION['pharmacist_name'] ?? 'Pharmacist';

$successMsg = "";
$errorMsg = "";

// Handle Add Medication Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $genericName = trim($_POST['genericName'] ?? '');
    $brandName = trim($_POST['brandName'] ?? '');
    $form = trim($_POST['form'] ?? '');
    $strength = trim($_POST['strength'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $stock = $_POST['stock'] ?? null;

    if (
        $genericName === "" ||
        $brandName === "" ||
        $form === "" ||
        $strength === "" ||
        $manufacturer === "" ||
        $stock === null || !is_numeric($stock) || $stock < 0
    ) {
        $errorMsg = "Please fill in all fields correctly.";
    } else {
        $insertSQL = "
            INSERT INTO medication 
                (genericName, brandName, form, strength, manufacturer, stock) 
            VALUES 
                (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($insertSQL);
        $stmt->bind_param("sssssi", $genericName, $brandName, $form, $strength, $manufacturer, $stock);

        if ($stmt->execute()) {
            $successMsg = "Medication added successfully.";
        } else {
            $errorMsg = "Failed to add medication.";
        }
    }
}

// Capture page content
ob_start();
?>

<link rel="stylesheet" href="../../assets/css/table.css">
<link rel="stylesheet" href="../../assets/css/role-pharmacist.css">
<link rel="stylesheet" href="stock.css">
<link rel="stylesheet" href="edit_stock.css">

<div class="edit-stock-page">
    <h2>Add New Medication</h2>
    <p>Fill out the form below to add a new medication to the inventory.</p>

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
                    <th>Generic Name</th>
                    <td class="value-cell">
                        <input type="text" name="genericName" required>
                    </td>
                </tr>

                <tr>
                    <th>Brand Name</th>
                    <td class="value-cell">
                        <input type="text" name="brandName" required>
                    </td>
                </tr>

                <tr>
                    <th>Form</th>
                    <td class="value-cell">
                        <select name="form" required>
                            <option value="">Select Form</option>
                            <option>Tablet</option>
                            <option>Capsule</option>
                            <option>Syrup</option>
                            <option>Injection</option>
                            <option>Ointment</option>
                            <option>Cream</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>Strength</th>
                    <td class="value-cell">
                        <input type="text" name="strength" required>
                    </td>
                </tr>

                <tr>
                    <th>Manufacturer</th>
                    <td class="value-cell">
                        <input type="text" name="manufacturer" required>
                    </td>
                </tr>

                <tr>
                    <th>Initial Stock</th>
                    <td class="value-cell">
                        <input type="number" name="stock" min="0" required>
                    </td>
                </tr>

                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-save">Add Medication</button>
                <a href="stock.php" class="btn-cancel">Cancel</a>
            </div>
        </form>

        <a href="stock.php" class="btn-back-outline">← Back to Stock</a>
    </div>
</div>

<?php
$content = ob_get_clean();

// Render with global pharmacist layout
$standard = __DIR__ . '/../pharmacist_standard.php';
if (file_exists($standard)) {
    include $standard;
} else {
    echo $content;
}
?>
