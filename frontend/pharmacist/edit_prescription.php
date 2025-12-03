<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
require_once __DIR__ . '/../../backend/includes/db_connect.php';

// Protect the page and get user data
$user = require_role(['pharmacist']);

$prescriptionId = $_GET['id'] ?? null;
$prescription = null;
$prescriptionItems = [];
$message = '';
$messageType = ''; // 'success' or 'error'

if (!$prescriptionId) {
    header('Location: prescription.php');
    exit();
}

// Fetch prescription details
$sqlPrescription = "
    SELECT
        p.prescriptionID,
        p.issueDate,
        p.expirationDate,
        p.status,
        pat.firstName AS patientFirstName,
        pat.lastName AS patientLastName,
        doc.firstName AS doctorFirstName,
        doc.lastName AS doctorLastName
    FROM prescription AS p
    JOIN patient AS pat ON p.patientID = pat.patientID
    JOIN doctor AS doc ON p.doctorID = doc.doctorID
    WHERE p.prescriptionID = ?
";
$stmtPrescription = $conn->prepare($sqlPrescription);
$stmtPrescription->bind_param('i', $prescriptionId);
$stmtPrescription->execute();
$resultPrescription = $stmtPrescription->get_result();
if ($resultPrescription->num_rows > 0) {
    $prescription = $resultPrescription->fetch_assoc();
} else {
    // Prescription not found, redirect back
    header('Location: prescription.php');
    exit();
}
$stmtPrescription->close();

// Fetch prescription items
$sqlItems = "
    SELECT
        pi.prescriptionItemID,
        pi.dosage,
        pi.prescribed_amount,
        pi.instructions,
        med.genericName AS medicationName,
        med.strength AS medicationStrength
    FROM prescriptionitem AS pi
    JOIN medication AS med ON pi.medicationID = med.medicationID
    WHERE pi.prescriptionID = ?
";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param('i', $prescriptionId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();
if ($resultItems) {
    $prescriptionItems = $resultItems->fetch_all(MYSQLI_ASSOC);
}
$stmtItems->close();


// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $newStatus = trim($_POST['status'] ?? '');
    $newExpirationDate = trim($_POST['expirationDate'] ?? '');

    // Validate new status
    $allowedStatuses = ['Active', 'Dispensed', 'Expired', 'Cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        $message = "Invalid status selected.";
        $messageType = 'error';
    } else {
        // Update prescription main details
        $updatePrescriptionSql = "UPDATE prescription SET status = ?, expirationDate = ? WHERE prescriptionID = ?";
        $stmtUpdatePrescription = $conn->prepare($updatePrescriptionSql);
        $stmtUpdatePrescription->bind_param('ssi', $newStatus, $newExpirationDate, $prescriptionId);

        if ($stmtUpdatePrescription->execute()) {
            $message = "Prescription updated successfully.";
            $messageType = 'success';
            // Update the $prescription array with new values for immediate display
            $prescription['status'] = $newStatus;
            $prescription['expirationDate'] = $newExpirationDate;

            // Update prescription items
            foreach ($prescriptionItems as $index => $item) {
                $itemId = $item['prescriptionItemID'];
                $newAmount = $_POST['item_amount'][$itemId] ?? null;
                $newInstructions = trim($_POST['item_instructions'][$itemId] ?? '');

                if ($newAmount !== null && is_numeric($newAmount) && $newAmount >= 0) {
                    $updateItemSql = "UPDATE prescriptionitem SET prescribed_amount = ?, instructions = ? WHERE prescriptionItemID = ?";
                    $stmtUpdateItem = $conn->prepare($updateItemSql);
                    $stmtUpdateItem->bind_param('isi', $newAmount, $newInstructions, $itemId);

                    if ($stmtUpdateItem->execute()) {
                        // Update the $prescriptionItems array with new values
                        $prescriptionItems[$index]['prescribed_amount'] = $newAmount;
                        $prescriptionItems[$index]['instructions'] = $newInstructions;
                    } else {
                        $message = "Error updating medication item ID " . $itemId . ": " . $stmtUpdateItem->error;
                        $messageType = 'error';
                        break; // Stop on first item error
                    }
                    $stmtUpdateItem->close();
                } else {
                    $message = "Invalid amount for medication item ID " . $itemId . ".";
                    $messageType = 'error';
                    break;
                }
            }
        } else {
            $message = "Error updating prescription: " . $stmtUpdatePrescription->error;
            $messageType = 'error';
        }
        $stmtUpdatePrescription->close();
    }
}


// Set the active page for the sidebar and add page-specific styles
$activePage = 'prescription';
$pageStyles = '<link rel="stylesheet" href="../css/pharmacist/edit_prescription.css">';

// Start output buffering
ob_start();
?>

<div class="edit-prescription-container">
    <header class="edit-prescription-header">
        <h1>Edit Prescription RX-<?= htmlspecialchars($prescription['prescriptionID']) ?></h1>
        <div class="action-buttons">
            <a href="prescription.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>
    </header>

    <?php if ($message): ?>
        <p class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_prescription.php?id=<?= htmlspecialchars($prescription['prescriptionID']) ?>" class="edit-form">
        <section class="prescription-details-card">
            <h2>Prescription Details</h2>
            <div class="form-group-grid">
                <div class="form-group">
                    <label>Patient Name:</label>
                    <p><?= htmlspecialchars($prescription['patientFirstName'] . ' ' . $prescription['patientLastName']) ?></p>
                </div>
                <div class="form-group">
                    <label>Doctor Name:</label>
                    <p>Dr. <?= htmlspecialchars($prescription['doctorLastName']) ?></p>
                </div>
                <div class="form-group">
                    <label for="issueDate">Issue Date:</label>
                    <p><?= htmlspecialchars(date('Y-m-d', strtotime($prescription['issueDate']))) ?></p>
                </div>
                <div class="form-group">
                    <label for="expirationDate">Expiration Date:</label>
                    <input type="date" id="expirationDate" name="expirationDate" value="<?= htmlspecialchars($prescription['expirationDate']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="Active" <?= ($prescription['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Dispensed" <?= ($prescription['status'] === 'Dispensed') ? 'selected' : '' ?>>Dispensed</option>
                        <option value="Expired" <?= ($prescription['status'] === 'Expired') ? 'selected' : '' ?>>Expired</option>
                        <option value="Cancelled" <?= ($prescription['status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="medication-items-card">
            <h2>Medication Items</h2>
            <?php if (empty($prescriptionItems)): ?>
                <p>No medication items for this prescription.</p>
            <?php else: ?>
                <?php foreach ($prescriptionItems as $item): ?>
                    <div class="medication-item-form">
                        <h3><?= htmlspecialchars($item['medicationName'] . ' ' . $item['medicationStrength']) ?></h3>
                        <div class="form-group-inline">
                            <div class="form-group">
                                <label for="item_amount_<?= htmlspecialchars($item['prescriptionItemID']) ?>">Quantity:</label>
                                <input type="number" id="item_amount_<?= htmlspecialchars($item['prescriptionItemID']) ?>"
                                       name="item_amount[<?= htmlspecialchars($item['prescriptionItemID']) ?>]"
                                       value="<?= htmlspecialchars($item['prescribed_amount']) ?>" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="item_dosage_<?= htmlspecialchars($item['prescriptionItemID']) ?>">Dosage:</label>
                                <input type="text" id="item_dosage_<?= htmlspecialchars($item['prescriptionItemID']) ?>"
                                       value="<?= htmlspecialchars($item['dosage']) ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="item_instructions_<?= htmlspecialchars($item['prescriptionItemID']) ?>">Instructions:</label>
                            <textarea id="item_instructions_<?= htmlspecialchars($item['prescriptionItemID']) ?>"
                                      name="item_instructions[<?= htmlspecialchars($item['prescriptionItemID']) ?>]"><?= htmlspecialchars($item['instructions']) ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div class="form-actions">
            <button type="submit" name="save_changes" class="btn-save-changes">Save Changes</button>
        </div>
    </form>
</div>

<?php
// Get the captured content
$pageContent = ob_get_clean();

// Include the standard layout
require_once 'pharmacy_standard.php';
?>