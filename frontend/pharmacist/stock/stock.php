<?php
session_start();

require_once __DIR__ . '/../../../backend/includes/auth.php';
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

// Sidebar highlight
$activePage = 'stock';

// Load stock list from backend handler
$stockList = include __DIR__ . '/../../../backend/pharmacist/stock/get_stock.php';

// Page-specific CSS
$pageStyles = '
    <link rel="stylesheet" href="stock.css">
';

// -----------------------------
// START PAGE CONTENT CAPTURE
// -----------------------------
ob_start();
?>

    <div class="page-header">
        <h1>Medication Stock Inventory</h1>
        <p class="subtitle">Manage, monitor, and update medication availability.</p>
    </div>

    <!-- Add Stock Button -->
    <div class="actions-bar">
        <a href="add_stock.php" class="btn-green large">
            + Add Stock
        </a>
    </div>

    <!-- Stock Table -->
    <div class="table-container">
        <table class="table-base">
            <thead>
            <tr>
                <th>ID</th>
                <th>Generic Name</th>
                <th>Brand Name</th>
                <th>Form</th>
                <th>Strength</th>
                <th>Manufacturer</th>
                <th>Stock</th>
                <th style="text-align:center;">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($stockList)) : ?>
                <?php foreach ($stockList as $row) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['medicationID']) ?></td>
                        <td><?= htmlspecialchars($row['genericName']) ?></td>
                        <td><?= htmlspecialchars($row['brandName']) ?></td>
                        <td><?= htmlspecialchars($row['form']) ?></td>
                        <td><?= htmlspecialchars($row['strength']) ?></td>
                        <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>

                        <td class="action-col">
                            <a href="edit_stock.php?medicationID=<?= $row['medicationID'] ?>"
                               class="btn-green small">
                                Edit
                            </a>

                            <a href="#"
                               class="btn-gray small view-history"
                               data-id="<?= $row['medicationID'] ?>">
                                History
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else : ?>
                <tr>
                    <td colspan="8" class="empty-message">
                        No medication stock records found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- JS Controller -->
    <script src="stock.js"></script>

<?php
$pageContent = ob_get_clean();

// Render inside pharmacist layout
include __DIR__ . '/../pharmacy_standard.php';
