<?php
session_start();

include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');

// Redirect if not logged in as pharmacist
if (!isset($_SESSION['pharmacistID'])) {
    header("Location: ../TestLoginPharmacist.php");
    exit;
}

$pharmacistName = $_SESSION['pharmacist_name'] ?? 'Pharmacist';
$activePage = 'stock';

// Fetch stock list
$stockQuery = "
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

$stmt = $conn->prepare($stockQuery);
$stmt->execute();
$stockResult = $stmt->get_result();

// Start capturing content for pharmacist_standard.php
ob_start();
?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="../../assets/css/table.css">
<link rel="stylesheet" href="../../assets/css/role-pharmacist.css">

<div class="pharmacist-stock-page">

    <h2>Medication Stock</h2>
    <p>Below is the complete list of medication stock levels.</p>

    <!-- Add Stock Button -->
    <div style="margin: 15px 0;">
        <a href="add_stock.php" class="btn-view"
           style="padding:10px 15px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;">
            + Add Stock
        </a>
    </div>

    <!-- Table container -->
    <div class="table-frame">
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
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($stockResult && $stockResult->num_rows > 0): ?>
                <?php while ($row = $stockResult->fetch_assoc()): ?>
                    <?php $id = $row['medicationID']; ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= htmlspecialchars($row['genericName']) ?></td>
                        <td><?= htmlspecialchars($row['brandName']) ?></td>
                        <td><?= htmlspecialchars($row['form']) ?></td>
                        <td><?= htmlspecialchars($row['strength']) ?></td>
                        <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>

                        <td>
                            <!-- Edit Button -->
                            <a href="edit_stock.php?medicationID=<?= $id ?>"
                               class="btn-view"
                               style="background:#1e3d2f;color:#fff;padding:5px 10px;border-radius:4px;text-decoration:none;">
                                Edit
                            </a>

                            <!-- View History Button -->
                            <a href="#" class="btn-view view-history"
                               data-id="<?= $id ?>"
                               style="background:#6c757d;color:#fff;padding:5px 10px;border-radius:4px;text-decoration:none;margin-left:5px;">
                                View History
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>

            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">No stock records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- JS for View History behaviour -->
<script src="stock.js"></script>

<?php
// final captured content
$content = ob_get_clean();


$standard = __DIR__ . '/../pharmacist_standard.php';

if (file_exists($standard)) {
    include $standard;
} else {
    echo $content;
}
?>
