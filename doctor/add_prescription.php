<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch dropdowns
$patients = mysqli_query($conn, "SELECT patientID, firstName, lastName FROM patient ORDER BY firstName");
$medications = mysqli_query($conn, "SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

$errors = [];
$success = false;

function post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescriptionID = post('prescriptionID');
    $patientID = post('patientID');
    $issueDate = post('issueDate');
    $expirationDate = post('expirationDate');
    $refillCount = post('refillCount');
    $refillInterval = post('refillInterval');
    $status = post('status');

    $medicationIDs = $_POST['medicationID'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $frequencies = $_POST['frequency'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $instructions = $_POST['instructions'] ?? [];

    if (empty($prescriptionID)) $errors[] = "Please enter a Prescription ID.";
    if (empty($patientID)) $errors[] = "Please select a patient.";
    if (empty($medicationIDs)) $errors[] = "Please select at least one medication.";

    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert into prescription
            $stmt = $conn->prepare("INSERT INTO prescription (prescriptionID, patientID, issueDate, expirationDate, refillCount, refillInterval, status, doctorID)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)"); // assuming doctorID=1 for now
            $stmt->bind_param("iississ", $prescriptionID, $patientID, $issueDate, $expirationDate, $refillCount, $refillInterval, $status);
            $stmt->execute();
            $stmt->close();

            // Insert multiple prescription items
            $stmtItem = $conn->prepare("INSERT INTO prescriptionitem (prescriptionItemID, prescriptionID, medicationID, dosage, frequency, duration, instructions, doctorID)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

            $itemID = 1;
            foreach ($medicationIDs as $index => $medID) {
                $dosage = $dosages[$index] ?? '';
                $frequency = $frequencies[$index] ?? '';
                $duration = $durations[$index] ?? '';
                $instr = $instructions[$index] ?? '';

                $stmtItem->bind_param("iiissss", $itemID, $prescriptionID, $medID, $dosage, $frequency, $duration, $instr);
                $stmtItem->execute();
                $itemID++;
            }
            $stmtItem->close();

            mysqli_commit($conn);
            $success = true;

        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="main-content">
    <h2>Add Prescription</h2>

    <?php if ($success): ?>
        <div style="padding:10px;background:#e6ffed;border:1px solid #1f8a3f;margin-bottom:12px;">
            ✅ Prescription added successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="padding:10px;background:#ffe6e6;border:1px solid #d62b2b;margin-bottom:12px;">
            <strong>Errors:</strong>
            <ul>
                <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" style="display:flex; flex-direction:column; width:600px; gap:8px;">
        <label>Prescription ID:</label>
        <input type="number" name="prescriptionID" required>

        <label>Patient:</label>
        <select name="patientID" required>
            <option value="">Select Patient</option>
            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                <option value="<?= $p['patientID'] ?>"><?= htmlspecialchars($p['firstName'].' '.$p['lastName']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Issue Date:</label>
        <input type="date" name="issueDate" required>

        <label>Expiration Date:</label>
        <input type="date" name="expirationDate" required>

        <label>Refill Count:</label>
        <input type="number" name="refillCount" min="0" value="1" required>

        <label>Refill Interval (days):</label>
        <input type="number" name="refillInterval" min="1" value="30" required>

        <label>Status:</label>
        <select name="status" required>
            <option value="Active">Active</option>
            <option value="Expired">Expired</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <hr>
        <h3>Medications</h3>
        <div id="medications-container">
            <div class="medication-item" style="margin-bottom:8px; border-bottom:1px dashed #ccc; padding-bottom:4px;">
                <select name="medicationID[]" required>
                    <option value="">Select Medication</option>
                    <?php mysqli_data_seek($medications, 0); while ($m = mysqli_fetch_assoc($medications)): ?>
                        <option value="<?= $m['medicationID'] ?>"><?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="dosage[]" placeholder="Dosage (e.g., 1 tablet)" required>
                <input type="text" name="frequency[]" placeholder="Frequency (e.g., Twice daily)" required>
                <input type="text" name="duration[]" placeholder="Duration (e.g., 7 days)" required>
                <input type="text" name="instructions[]" placeholder="Instructions" required>
            </div>
        </div>

        <button type="button" onclick="addMedication()">+ Add Another Medication</button>

        <button type="submit" style="padding:10px; background-color:#4CAF50; color:white; border:none; border-radius:5px;">
            Save Prescription
        </button>
    </form>
</div>

<script>
function addMedication() {
    const container = document.getElementById('medications-container');
    const newItem = document.createElement('div');
    newItem.classList.add('medication-item');
    newItem.style = "margin-bottom:8px; border-bottom:1px dashed #ccc; padding-bottom:4px;";
    newItem.innerHTML = `
        <select name="medicationID[]" required>
            <option value="">Select Medication</option>
            <?php mysqli_data_seek($medications, 0); while ($m = mysqli_fetch_assoc($medications)): ?>
                <option value="<?= $m['medicationID'] ?>"><?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="text" name="dosage[]" placeholder="Dosage" required>
        <input type="text" name="frequency[]" placeholder="Frequency" required>
        <input type="text" name="duration[]" placeholder="Duration" required>
        <input type="text" name="instructions[]" placeholder="Instructions" required>
        <button type="button" onclick="this.parentElement.remove()">Remove</button>
    `;
    container.appendChild(newItem);
}
</script>
