<?php
// add_prescription.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php'); // expects $conn (mysqli)
$activePage = 'prescriptions';

/* If user clicked "Add RX" from a patient list, that patient's ID can be passed as ?id=123 */
$selectedPatientID = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* Load lists */
$patients = $conn->query("SELECT patientID, firstName, lastName FROM patient ORDER BY firstName, lastName");
$doctors  = $conn->query("SELECT doctorID, firstName, lastName FROM doctor ORDER BY firstName, lastName");
$meds     = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

/* Preselect doctor from session if available */
$sessionDoctorID = isset($_SESSION['doctor_id']) ? intval($_SESSION['doctor_id']) : 0;

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Basic POST sanitation
    $patientID = intval($_POST['patientID'] ?? 0);
    $doctorID  = intval($_POST['doctorID'] ?? 0) ?: $sessionDoctorID;
    $issueDate = trim($_POST['issueDate'] ?? '');
    $expirationDate = trim($_POST['expirationDate'] ?? '');
    $refillInterval  = trim($_POST['refillInterval'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');

    $medIDs = $_POST['medicationID'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $freqs = $_POST['frequency'] ?? [];
    $durs = $_POST['duration'] ?? [];
    $refillCounts = $_POST['refillCount'] ?? [];
    $instructions = $_POST['instructions'] ?? [];

    // Validate
    if ($patientID <= 0) $errors[] = "Please select a patient.";
    if ($doctorID <= 0) $errors[] = "Please select a doctor.";
    if (empty($issueDate)) $errors[] = "Issue date is required.";
    if (empty($medIDs) || !is_array($medIDs)) $errors[] = "Add at least one medication.";

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            // Use first medication as prescription.medicationID for compatibility with current schema
            $firstMed = intval($medIDs[0]) ?: 0;

            $insertPres = $conn->prepare("
                INSERT INTO prescription
                (medicationID, patientID, issueDate, expirationDate, refillInterval, status, doctorID)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$insertPres) {
                throw new Exception("Prepare failed (prescription): " . $conn->error);
            }

            // types: i i s s s s i  => "iissssi"
            $insertPres->bind_param(
                    "iissssi",
                    $firstMed,
                    $patientID,
                    $issueDate,
                    $expirationDate,
                    $refillInterval,
                    $status,
                    $doctorID
            );

            if (!$insertPres->execute()) {
                $err = $insertPres->error;
                $insertPres->close();
                throw new Exception("Error inserting prescription: " . $err);
            }

            $prescriptionID = $conn->insert_id;
            $insertPres->close();

            // Prepare prescriptionitem insert
            $insItem = $conn->prepare("
                INSERT INTO prescriptionitem
                (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions)
                VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)
            ");
            if (!$insItem) {
                throw new Exception("Prepare failed (prescriptionitem): " . $conn->error);
            }

            // bind types: i i i s s s i s => "iiisssis"
            foreach ($medIDs as $i => $midRaw) {
                $medID = intval($midRaw);
                $dos = trim($dosages[$i] ?? '');
                $frq = trim($freqs[$i] ?? '');
                $dur = trim($durs[$i] ?? '');
                $refc = intval($refillCounts[$i] ?? 0);
                $instr = trim($instructions[$i] ?? '');

                // Basic safety: required fields per medication
                if ($medID <= 0) {
                    throw new Exception("Invalid medication selected for item #" . ($i+1));
                }

                $insItem->bind_param("iiisssis",
                        $doctorID,
                        $prescriptionID,
                        $medID,
                        $dos,
                        $frq,
                        $dur,
                        $refc,
                        $instr
                );

                if (!$insItem->execute()) {
                    $err = $insItem->error;
                    $insItem->close();
                    throw new Exception("Error inserting prescription item: " . $err);
                }
            }

            $insItem->close();
            mysqli_commit($conn);

            // Success message + redirect after 2 seconds (Option A + 2)
            $message = "<div style='padding:12px;background:#e6ffed;border:1px solid #2a8a4a;color:#0a7a2a;border-radius:6px;margin-bottom:12px;'>✔ Prescription saved successfully. Redirecting...</div>";
            // We'll include a small JS redirect below when rendering the page

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}

/* ---------------- RENDER ---------------- */
ob_start();
?>

    <div class="card">
        <h2>Add Prescription</h2>

        <?php if ($message) echo $message; ?>

        <?php if (!empty($errors)): ?>
            <div style="padding:10px;background:#ffe6e6;border:1px solid #d9534f;color:#a30000;border-radius:6px;margin-bottom:12px;">
                <strong>Errors:</strong>
                <ul style="margin:6px 0 0 18px;"><?php foreach ($errors as $er) echo "<li>" . htmlspecialchars($er) . "</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" id="add-prescription-form" style="max-width:900px;">
            <label><strong>Doctor</strong></label><br>
            <select name="doctorID" required>
                <option value="">Select Doctor</option>
                <?php
                // Reset pointer and print doctors
                if ($doctors) {
                    mysqli_data_seek($doctors, 0);
                    while ($d = $doctors->fetch_assoc()) {
                        $sel = ($sessionDoctorID && $sessionDoctorID == $d['doctorID']) ? 'selected' : '';
                        echo "<option value=\"" . (int)$d['doctorID'] . "\" $sel>" . htmlspecialchars($d['firstName'] . ' ' . $d['lastName']) . "</option>";
                    }
                }
                ?>
            </select>
            <br><br>

            <label><strong>Patient</strong></label><br>
            <select name="patientID" <?= $selectedPatientID ? 'disabled' : 'required' ?>>
                <option value="">Select Patient</option>
                <?php
                if ($patients) {
                    mysqli_data_seek($patients, 0);
                    while ($p = $patients->fetch_assoc()) {
                        $sel = ($selectedPatientID && $selectedPatientID == $p['patientID']) ? 'selected' : '';
                        echo "<option value=\"" . (int)$p['patientID'] . "\" $sel>" . htmlspecialchars($p['firstName'] . ' ' . $p['lastName']) . "</option>";
                    }
                }
                ?>
            </select>
            <?php if ($selectedPatientID): ?>
                <input type="hidden" name="patientID" value="<?= $selectedPatientID ?>">
            <?php endif; ?>
            <br><br>

            <label>Issue Date</label><br>
            <input type="date" name="issueDate" required value="<?= date('Y-m-d') ?>"><br><br>

            <label>Expiration Date</label><br>
            <input type="date" name="expirationDate" required><br><br>

            <label>Refill Interval (date or days)</label><br>
            <input type="text" name="refillInterval" value="30"><br><br>

            <label>Status</label><br>
            <select name="status">
                <option>Active</option>
                <option>Expired</option>
                <option>Cancelled</option>
            </select>

            <hr>

            <h3>Medications</h3>

            <div id="meds">
                <div class="med" style="margin-bottom:10px; display:flex; gap:8px; align-items:flex-start;">
                    <select name="medicationID[]" required>
                        <option value="">Select Medication</option>
                        <?php
                        if ($meds) {
                            mysqli_data_seek($meds, 0);
                            while ($m = $meds->fetch_assoc()) {
                                echo "<option value=\"" . (int)$m['medicationID'] . "\">" . htmlspecialchars($m['genericName'] . ' — ' . $m['brandName']) . "</option>";
                            }
                        }
                        ?>
                    </select>

                    <input type="text" name="dosage[]" placeholder="Dosage (e.g., 500 mg)" required style="min-width:160px;">
                    <input type="text" name="frequency[]" placeholder="Frequency (e.g., Twice daily)" required style="min-width:140px;">
                    <input type="text" name="duration[]" placeholder="Duration (e.g., 7 days)" required style="min-width:120px;">
                    <input type="number" name="refillCount[]" min="0" value="0" placeholder="Refill count" style="width:110px;">
                    <input type="text" name="instructions[]" placeholder="Instructions" required style="min-width:160px;">
                    <button type="button" onclick="this.parentElement.remove()">Remove</button>
                </div>
            </div>

            <button type="button" onclick="addMed()" style="margin-top:10px;">+ Add Another Medication</button>
            <br><br>

            <button type="submit" class="btn">Save Prescription</button>
            <a href="view_prescription.php" class="btn" style="background:#6c757d;">Cancel</a>
        </form>
    </div>

    <script>
        function addMed(){
            const meds = document.getElementById('meds');
            const div = document.createElement('div');
            div.className = 'med';
            div.style = "margin-top:8px; display:flex; gap:8px; align-items:flex-start;";

            // build options string server-side once
            div.innerHTML = `<?php
            // build medication options
            $opts = '<select name="medicationID[]" required><option value="">Select Medication</option>';
            if ($meds) {
                mysqli_data_seek($meds, 0);
                while ($m = $meds->fetch_assoc()) {
                    $opts .= "<option value=\"{$m['medicationID']}\">".htmlspecialchars($m['genericName']." — ".$m['brandName'])."</option>";
                }
            }
            $opts .= '</select>';
            echo str_replace(["\n","\r"], ["",""], addslashes($opts));
            ?>
    <input type="text" name="dosage[]" placeholder="Dosage (e.g., 500 mg)" required style="min-width:160px;">
    <input type="text" name="frequency[]" placeholder="Frequency (e.g., Twice daily)" required style="min-width:140px;">
    <input type="text" name="duration[]" placeholder="Duration (e.g., 7 days)" required style="min-width:120px;">
    <input type="number" name="refillCount[]" min="0" value="0" placeholder="Refill count" style="width:110px;">
    <input type="text" name="instructions[]" placeholder="Instructions" required style="min-width:160px;">
    <button type="button" onclick="this.parentElement.remove()">Remove</button>`;

            meds.appendChild(div);
        }

        // If success message was set server-side, redirect after 2 seconds to view_prescription.php
        <?php if ($message): ?>
        setTimeout(function(){
            window.location = 'view_prescription.php';
        }, 2000);
        <?php endif; ?>
    </script>

<?php
$content = ob_get_clean();
include('doctor_standard.php');