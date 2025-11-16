<?php
include('../includes/db_connect.php');
$activePage = 'prescriptions';

$patients = $conn->query("SELECT patientID, firstName, lastName FROM patient ORDER BY firstName");
$medications = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescriptionID = intval($_POST['prescriptionID'] ?? 0);
    $patientID = intval($_POST['patientID'] ?? 0);
    $issueDate = $_POST['issueDate'] ?? null;
    $expirationDate = $_POST['expirationDate'] ?? null;
    // We'll store refillInterval as a date (to keep compatibility). User can input days or date; keep as provided.
    $refillInterval = $_POST['refillInterval'] ?? null;
    $status = $_POST['status'] ?? 'Active';

    $medIDs = $_POST['medicationID'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $frequencies = $_POST['frequency'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $instructions = $_POST['instructions'] ?? [];

    if ($prescriptionID <= 0) $errors[] = "Enter a valid Prescription ID.";
    if ($patientID <= 0) $errors[] = "Select a patient.";
    if (empty($medIDs)) $errors[] = "Add at least one medication.";

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            // use first medication as prescription.medicationID for compatibility
            $firstMed = intval($medIDs[0]);

            $stmt = $conn->prepare("INSERT INTO prescription (prescriptionID, medicationID, patientID, issueDate, expirationDate, refillInterval, status, doctorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $doctorID = 1;
            $stmt->bind_param("iiisssii", $prescriptionID, $firstMed, $patientID, $issueDate, $expirationDate, $refillInterval, $status, $doctorID);
            $stmt->execute();
            $stmt->close();

            // Insert items (prescriptionitem)
            $itemStmt = $conn->prepare("INSERT INTO prescriptionitem (doctorID, prescriptionItemID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $nextItemId = null;
            // attempt to use AUTO_INCREMENT value: because your prescriptionitem has PK as prescriptionItemID with auto_increment when altered in DB; if it's auto-inc we should pass NULL to use AI. But earlier dump sets AUTO_INCREMENT. To be safe, pass 0 and let DB set; but prepared statement requires an int: use NULL via binding as string 'NULL' can't. Simpler approach: set prescriptionItemID to 0 and let DB auto increment by using INSERT ... VALUES (NULL,...). We'll instead use dynamic query per item simpler below.
            $pdoUsePrepared = false;

            // Insert items individually (safer if auto_increment)
            foreach ($medIDs as $i => $m) {
                $m = intval($m);
                $dos = $conn->real_escape_string($dosages[$i] ?? '');
                $freq = $conn->real_escape_string($frequencies[$i] ?? '');
                $dur = $conn->real_escape_string($durations[$i] ?? '');
                $instr = $conn->real_escape_string($instructions[$i] ?? '');
                // prescribed_amount and refill_count default 0
                $sql = "INSERT INTO prescriptionitem (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions)
                        VALUES (1, $prescriptionID, $m, '$dos', '$freq', '$dur', 0, 0, '$instr')";
                if (!$conn->query($sql)) throw new Exception($conn->error);
            }

            mysqli_commit($conn);
            $message = "<div style='color:green;'>Prescription added successfully.</div>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "DB error: " . $e->getMessage();
        }
    }
}

ob_start();
?>

    <div class="card">
        <h2>Add Prescription</h2>
        <?= $message ?>
        <?php if (!empty($errors)): ?>
            <div style="color:red;"><ul><?php foreach($errors as $er) echo "<li>".htmlspecialchars($er)."</li>"; ?></ul></div>
        <?php endif; ?>

        <form method="post" style="max-width:900px;">
            <label>Prescription ID</label><br>
            <input type="number" name="prescriptionID" required><br><br>

            <label>Patient</label><br>
            <select name="patientID" required>
                <option value="">Select Patient</option>
                <?php mysqli_data_seek($patients,0); while ($p = $patients->fetch_assoc()): ?>
                    <option value="<?= $p['patientID'] ?>"><?= htmlspecialchars($p['firstName'].' '.$p['lastName']) ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <label>Issue Date</label><br>
            <input type="date" name="issueDate" required><br><br>

            <label>Expiration Date</label><br>
            <input type="date" name="expirationDate" required><br><br>

            <label>Refill Interval (date or days)</label><br>
            <input type="text" name="refillInterval" value="30"><br><br>

            <label>Status</label><br>
            <select name="status"><option>Active</option><option>Expired</option><option>Cancelled</option></select><br><br>

            <hr>
            <h3>Medications</h3>
            <div id="meds">
                <div class="med">
                    <select name="medicationID[]" required>
                        <option value="">Select Medication</option>
                        <?php mysqli_data_seek($medications,0); while ($m = $medications->fetch_assoc()): ?>
                            <option value="<?= $m['medicationID'] ?>"><?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?></option>
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
        function addMed(){
            const container = document.getElementById('meds');
            const div = document.createElement('div');
            div.className = 'med';
            div.style = 'margin-top:8px;';
            div.innerHTML = `<?php
            // generate options markup once server-side (string)
            $opt = "<select name=\"medicationID[]\" required><option value=\"\">Select Medication</option>";
            mysqli_data_seek($medications,0);
            while ($m = $medications->fetch_assoc()) {
                $opt .= "<option value=\"{$m['medicationID']}\">".htmlspecialchars($m['genericName']." — ".$m['brandName'])."</option>";
            }
            $opt .= "</select>";
            // escape for JS string
            echo str_replace(["\n","\r","'"],["","","\'"], $opt);
            ?>
    <input type="text" name="dosage[]" placeholder="Dosage" required>
    <input type="text" name="frequency[]" placeholder="Frequency" required>
    <input type="text" name="duration[]" placeholder="Duration" required>
    <input type="text" name="instructions[]" placeholder="Instructions" required>
    <button type="button" onclick="this.parentElement.remove()">Remove</button>`;
            container.appendChild(div);
        }
    </script>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
