<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$prescriptionID = $_GET['id'] ?? 0;
$prescriptionID = (int)$prescriptionID;

// Fetch prescription info
$presResult = mysqli_query($conn, "SELECT * FROM prescription WHERE prescriptionID = '$prescriptionID'");
$prescription = mysqli_fetch_assoc($presResult);

// Fetch all prescription items (medications)
$itemsResult = mysqli_query($conn, "
    SELECT pi.prescriptionItemID, pi.medicationID, pi.dosage, pi.frequency, pi.duration, pi.instructions,
           m.genericName, m.brandName
    FROM prescriptionitem pi
    JOIN medication m ON pi.medicationID = m.medicationID
    WHERE pi.prescriptionID = '$prescriptionID'
");

// Fetch all medications for dropdown
$medications = mysqli_query($conn, "SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        // Update prescription main fields
        $status = $_POST['status'];
        $refillCount = $_POST['refillCount'];
        $refillInterval = $_POST['refillInterval'];

        $stmt = $conn->prepare("UPDATE prescription SET status=?, refillCount=?, refillInterval=? WHERE prescriptionID=?");
        $stmt->bind_param("siis", $status, $refillCount, $refillInterval, $prescriptionID);
        $stmt->execute();
        $stmt->close();

        // Handle prescription items
        $medIDs = $_POST['medicationID'] ?? [];
        $dosages = $_POST['dosage'] ?? [];
        $frequencies = $_POST['frequency'] ?? [];
        $durations = $_POST['duration'] ?? [];
        $instructions = $_POST['instructions'] ?? [];
        $itemIDs = $_POST['itemID'] ?? [];

        foreach ($medIDs as $i => $medID) {
            $medID = (int)$medID;
            $dosage = trim($dosages[$i]);
            $frequency = trim($frequencies[$i]);
            $duration = trim($durations[$i]);
            $instruction = trim($instructions[$i]);
            $itemID = isset($itemIDs[$i]) ? (int)$itemIDs[$i] : 0;

            if ($itemID > 0) {
                // Update existing item
                $stmt = $conn->prepare("UPDATE prescriptionitem SET medicationID=?, dosage=?, frequency=?, duration=?, instructions=? WHERE prescriptionItemID=?");
                $stmt->bind_param("issssi", $medID, $dosage, $frequency, $duration, $instruction, $itemID);
                $stmt->execute();
                $stmt->close();
            } else {
                // Insert new item
                $stmt = $conn->prepare("INSERT INTO prescriptionitem (prescriptionID, medicationID, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $prescriptionID, $medID, $dosage, $frequency, $duration, $instruction);
                $stmt->execute();
                $stmt->close();
            }
        }

        mysqli_commit($conn);
        $success = true;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = "Error updating prescription: " . $e->getMessage();
    }
}
?>

<div class="main-content">
    <h2>Edit Prescription #<?= htmlspecialchars($prescriptionID) ?></h2>

    <?php if ($success): ?>
        <div style="padding:10px;background:#e6ffed;border:1px solid #1f8a3f;margin-bottom:12px;">
            ✅ Prescription updated successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="padding:10px;background:#ffe6e6;border:1px solid #d62b2b;margin-bottom:12px;">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Status:</label>
        <select name="status" required>
            <option value="Active" <?= $prescription['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Completed" <?= $prescription['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="Expired" <?= $prescription['status'] == 'Expired' ? 'selected' : '' ?>>Expired</option>
        </select><br><br>

        <label>Refill Count:</label>
        <input type="number" name="refillCount" value="<?= $prescription['refillCount'] ?>" required><br><br>

        <label>Refill Interval:</label>
        <input type="text" name="refillInterval" value="<?= $prescription['refillInterval'] ?>" required><br><br>

        <h3>Medications</h3>
        <div id="medications-container">
            <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>
                <div class="med-item" style="border:1px solid #ccc; padding:8px; margin-bottom:8px;">
                    <input type="hidden" name="itemID[]" value="<?= $item['prescriptionItemID'] ?>">
                    <label>Medication:</label>
                    <select name="medicationID[]" required>
                        <?php
                        mysqli_data_seek($medications, 0); // reset result pointer
                        while ($med = mysqli_fetch_assoc($medications)) {
                            $sel = ($med['medicationID'] == $item['medicationID']) ? 'selected' : '';
                            echo "<option value='{$med['medicationID']}' $sel>" . htmlspecialchars($med['genericName'] . " — " . $med['brandName']) . "</option>";
                        }
                        ?>
                    </select><br>
                    <label>Dosage:</label>
                    <input type="text" name="dosage[]" value="<?= htmlspecialchars($item['dosage']) ?>" required><br>
                    <label>Frequency:</label>
                    <input type="text" name="frequency[]" value="<?= htmlspecialchars($item['frequency']) ?>" required><br>
                    <label>Duration:</label>
                    <input type="text" name="duration[]" value="<?= htmlspecialchars($item['duration']) ?>" required><br>
                    <label>Instructions:</label>
                    <input type="text" name="instructions[]" value="<?= htmlspecialchars($item['instructions']) ?>" required><br>
                    <button type="button" onclick="this.parentElement.remove();">Remove</button>
                </div>
            <?php endwhile; ?>
        </div>

        <button type="button" onclick="addMedicationRow();">+ Add Medication</button><br><br>
        <button type="submit">Update Prescription</button>
    </form>
</div>

<script>
function addMedicationRow() {
    const container = document.getElementById('medications-container');
    const div = document.createElement('div');
    div.className = 'med-item';
    div.style.border = '1px solid #ccc';
    div.style.padding = '8px';
    div.style.marginBottom = '8px';
    div.innerHTML = `
        <label>Medication:</label>
        <select name="medicationID[]" required>
            <?php
            mysqli_data_seek($medications, 0);
            while ($med = mysqli_fetch_assoc($medications)) {
                echo "<option value='{$med['medicationID']}'>" . htmlspecialchars($med['genericName'] . " — " . $med['brandName']) . "</option>";
            }
            ?>
        </select><br>
        <label>Dosage:</label>
        <input type="text" name="dosage[]" required><br>
        <label>Frequency:</label>
        <input type="text" name="frequency[]" required><br>
        <label>Duration:</label>
        <input type="text" name="duration[]" required><br>
        <label>Instructions:</label>
        <input type="text" name="instructions[]" required><br>
        <button type="button" onclick="this.parentElement.remove();">Remove</button>
    `;
    container.appendChild(div);
}
</script>
