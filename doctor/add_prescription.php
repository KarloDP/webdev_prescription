<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$activePage = 'prescriptions';

/* ----------------------------------------
   GET PATIENT ID IF COMING FROM "ADD RX"
-------------------------------------------*/
$selectedPatientID = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* ----------------------------------------
   FETCH PATIENTS & MEDICATIONS
-------------------------------------------*/
$patients = $conn->query("SELECT patientID, firstName, lastName FROM patient ORDER BY firstName");
$medications = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

$errors = [];
$success = false;

/* ----------------------------------------
   GET LOGGED-IN DOCTOR ID
   (fallback to ID=1 if no login system yet)
-------------------------------------------*/
if (isset($_SESSION['doctor_id']) && intval($_SESSION['doctor_id']) > 0) {
    $doctorID = intval($_SESSION['doctor_id']);
} else {
    $doctorID = 1; // fallback — safe until login is implemented
}

/* ----------------------------------------
   FORM SUBMITTED
-------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prescriptionID   = intval($_POST['prescriptionID']);
    $patientID        = intval($_POST['patientID']);
    $issueDate        = $_POST['issueDate'];
    $expirationDate   = $_POST['expirationDate'];
    $refillInterval   = $_POST['refillInterval'];
    $status           = $_POST['status'];

    $medIDs  = $_POST['medicationID'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $freqs   = $_POST['frequency'] ?? [];
    $durs    = $_POST['duration'] ?? [];
    $instrs  = $_POST['instructions'] ?? [];

    if ($prescriptionID <= 0) $errors[] = "Enter a valid Prescription ID.";
    if ($patientID <= 0)     $errors[] = "Select a patient.";
    if (empty($medIDs))      $errors[] = "Add at least one medication.";

    if (empty($errors)) {

        mysqli_begin_transaction($conn);

        try {

            /* ----------------------------------------
               Insert into prescription (NO medicationID)
            -------------------------------------------*/
            $stmt = $conn->prepare("
                INSERT INTO prescription
                (prescriptionID, patientID, issueDate, expirationDate, refillInterval, status, doctorID)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                    "iissssi",
                    $prescriptionID,
                    $patientID,
                    $issueDate,
                    $expirationDate,
                    $refillInterval,
                    $status,
                    $doctorID
            );

            if (!$stmt->execute()) {
                throw new Exception("Error inserting prescription: " . $stmt->error);
            }
            $stmt->close();


            /* ----------------------------------------
               Insert prescription items
            -------------------------------------------*/
            foreach ($medIDs as $i => $m) {

                $med = intval($m);
                $dos = $conn->real_escape_string($dosages[$i]);
                $frq = $conn->real_escape_string($freqs[$i]);
                $dur = $conn->real_escape_string($durs[$i]);
                $ins = $conn->real_escape_string($instrs[$i]);

                $sql = "
                    INSERT INTO prescriptionitem
                    (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions)
                    VALUES ($doctorID, $prescriptionID, $med, '$dos', '$frq', '$dur', 0, 0, '$ins')
                ";

                if (!$conn->query($sql)) {
                    throw new Exception("Error inserting item: " . $conn->error);
                }
            }

            mysqli_commit($conn);
            $success = true;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!-- ============================ HTML OUTPUT ============================= -->

<div class="card">
    <h2>Add Prescription</h2>

    <?php if ($success): ?>
        <div style="color:green;">Prescription added successfully.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color:red;">
            <ul>
                <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">

        <label>Prescription ID</label><br>
        <input type="number" name="prescriptionID" required><br><br>

        <label>Doctor ID:</label><br>
        <strong><?= $doctorID ?></strong><br><br>

        <label>Patient</label><br>
        <select name="patientID" required <?php if ($selectedPatientID) echo "disabled"; ?>>
            <option value="">Select Patient</option>

            <?php while ($p = $patients->fetch_assoc()): ?>
                <option value="<?= $p['patientID'] ?>"
                        <?= ($selectedPatientID == $p['patientID']) ? 'selected' : '' ?>>

                    <?= htmlspecialchars($p['firstName'] . " " . $p['lastName']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <?php if ($selectedPatientID): ?>
            <input type="hidden" name="patientID" value="<?= $selectedPatientID ?>">
        <?php endif; ?>

        <br><br>

        <label>Issue Date</label><br>
        <input type="date" name="issueDate" required><br><br>

        <label>Expiration Date</label><br>
        <input type="date" name="expirationDate" required><br><br>

        <label>Refill Interval (days or date)</label><br>
        <input type="text" name="refillInterval" value="30" required><br><br>

        <label>Status</label><br>
        <select name="status">
            <option>Active</option>
            <option>Expired</option>
            <option>Cancelled</option>
        </select><br><br>

        <h3>Medications</h3>

        <div id="meds">
            <div class="med" style="margin-bottom:10px;">

                <select name="medicationID[]" required>
                    <option value="">Select Medication</option>

                    <?php mysqli_data_seek($medications, 0); ?>
                    <?php while ($m = $medications->fetch_assoc()): ?>
                        <option value="<?= $m['medicationID'] ?>">
                            <?= htmlspecialchars($m['genericName'] . ' — ' . $m['brandName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="dosage[]" placeholder="Dosage" required>
                <input type="text" name="frequency[]" placeholder="Frequency" required>
                <input type="text" name="duration[]" placeholder="Duration" required>
                <input type="text" name="instructions[]" placeholder="Instructions" required>

            </div>
        </div>

        <button type="button" onclick="addMed()">+ Add Another Medication</button><br><br>

        <button class="btn" type="submit">Save Prescription</button>
    </form>
</div>

<script>
    function addMed() {
        const div = document.createElement('div');
        div.className = "med";
        div.style = "margin-top:8px;";

        div.innerHTML = `
        <select name="medicationID[]" required>
            <?php
        mysqli_data_seek($medications, 0);
        while ($m = $medications->fetch_assoc()) {
            echo "<option value='{$m['medicationID']}'>"
                    . htmlspecialchars($m['genericName'] . " — " . $m['brandName'])
                    . "</option>";
        }
        ?>
        </select>

        <input type="text" name="dosage[]" placeholder="Dosage" required>
        <input type="text" name="frequency[]" placeholder="Frequency" required>
        <input type="text" name="duration[]" placeholder="Duration" required>
        <input type="text" name="instructions[]" placeholder="Instructions" required>
        <button type="button" onclick="this.parentElement.remove()">Remove</button>
    `;

        document.getElementById('meds').appendChild(div);
    }
</script>

<?php include('../includes/footer.php'); ?>
