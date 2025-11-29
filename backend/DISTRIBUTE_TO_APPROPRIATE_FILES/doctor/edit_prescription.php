<?php
// edit_prescription.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
//backend\DISTRIBUTE_TO_APPROPRIATE_FILES\doctor\edit_prescription.php
include('../includes/db_connect.php'); // expects $conn (mysqli)
$activePage = 'prescriptions';

/* ---------- validate prescription id ---------- */
$prescriptionID = intval($_GET['id'] ?? 0);
if ($prescriptionID <= 0) {
    header("Location: view_prescription.php");
    exit;
}

/* ---------- load prescription header ---------- */
$presStmt = $conn->prepare("SELECT * FROM prescription WHERE prescriptionID = ?");
$presStmt->bind_param("i", $prescriptionID);
$presStmt->execute();
$presData = $presStmt->get_result()->fetch_assoc();
$presStmt->close();

if (!$presData) {
    header("Location: view_prescription.php");
    exit;
}

/* ---------- load medication list for selects ---------- */
$medications = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

/* ---------- load current items ---------- */
$itemsStmt = $conn->prepare("SELECT prescriptionItemID, medicationID, dosage, frequency, duration, refill_count, instructions FROM prescriptionitem WHERE prescriptionID = ?");
$itemsStmt->bind_param("i", $prescriptionID);
$itemsStmt->execute();
$itemsRes = $itemsStmt->get_result();
$itemsStmt->close();

/* ---------- optional: list doctors for selection (if you want to allow changing doctor) ---------- */
$doctors = $conn->query("SELECT doctorID, firstName, lastName FROM doctor ORDER BY firstName, lastName");

/* ---------- form handling ---------- */
$errors = [];
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // header fields
    $status = trim($_POST['status'] ?? $presData['status']);
    $issueDate = trim($_POST['issueDate'] ?? $presData['issueDate']);
    $expirationDate = trim($_POST['expirationDate'] ?? $presData['expirationDate']);
    $refillInterval = trim($_POST['refillInterval'] ?? $presData['refillInterval']);

    // doctor selection: prefer session doctor if available, else posted doctor or original
    $sessionDoctorID = isset($_SESSION['doctor_id']) ? intval($_SESSION['doctor_id']) : 0;
    $doctorID = $sessionDoctorID ?: (intval($_POST['doctorID'] ?? 0) ?: intval($presData['doctorID']));

    // items arrays
    $medIDs = $_POST['medicationID'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $freqs = $_POST['frequency'] ?? [];
    $durs = $_POST['duration'] ?? [];
    $refillCounts = $_POST['refillCount'] ?? [];
    $instructions = $_POST['instructions'] ?? [];
    $itemIDs = $_POST['itemID'] ?? []; // existing item IDs for updates (may be missing for new rows)

    // basic validation
    if (empty($medIDs) || !is_array($medIDs)) {
        $errors[] = "Please include at least one medication.";
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            // 1) update prescription header (status, refillInterval, dates, doctor)
            $u = $conn->prepare("UPDATE prescription SET status = ?, issueDate = ?, expirationDate = ?, refillInterval = ?, doctorID = ? WHERE prescriptionID = ?");
            if (!$u) throw new Exception("Prepare failed (update prescription): " . $conn->error);
            $u->bind_param("ssssii", $status, $issueDate, $expirationDate, $refillInterval, $doctorID, $prescriptionID);
            if (!$u->execute()) {
                $err = $u->error;
                $u->close();
                throw new Exception("Error updating prescription: " . $err);
            }
            $u->close();

            // 2) upsert items
            $processedItemIDs = []; // track existing item ids handled (for deletion of removed ones)

            foreach ($medIDs as $i => $midRaw) {
                $medID = intval($midRaw);
                $dos = trim($dosages[$i] ?? '');
                $frq = trim($freqs[$i] ?? '');
                $dur = trim($durs[$i] ?? '');
                $refc = intval($refillCounts[$i] ?? 0);
                $instr = trim($instructions[$i] ?? '');
                $itid = isset($itemIDs[$i]) && intval($itemIDs[$i]) > 0 ? intval($itemIDs[$i]) : 0;

                if ($itid > 0) {
                    // update existing item
                    $stmt = $conn->prepare("
                        UPDATE prescriptionitem
                        SET medicationID = ?, dosage = ?, frequency = ?, duration = ?, refill_count = ?, instructions = ?
                        WHERE prescriptionItemID = ? AND prescriptionID = ?
                    ");
                    if (!$stmt) throw new Exception("Prepare failed (update item): " . $conn->error);
                    $stmt->bind_param("isssisii", $medID, $dos, $frq, $dur, $refc, $instr, $itid, $prescriptionID);
                    if (!$stmt->execute()) {
                        $err = $stmt->error;
                        $stmt->close();
                        throw new Exception("Error updating item: " . $err);
                    }
                    $stmt->close();
                    $processedItemIDs[] = $itid;
                } else {
                    // insert new item (prescriptionitem has AUTO_INCREMENT on prescriptionItemID)
                    $ins = $conn->prepare("
                        INSERT INTO prescriptionitem
                        (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions)
                        VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)
                    ");
                    if (!$ins) throw new Exception("Prepare failed (insert item): " . $conn->error);
                    $ins->bind_param("iiisssis", $doctorID, $prescriptionID, $medID, $dos, $frq, $dur, $refc, $instr);
                    if (!$ins->execute()) {
                        $err = $ins->error;
                        $ins->close();
                        throw new Exception("Error inserting item: " . $err);
                    }
                    $newId = $ins->insert_id;
                    $ins->close();
                    $processedItemIDs[] = $newId;
                }
            }

            // 3) delete items that were removed from the form (not present in $processedItemIDs)
            if (!empty($processedItemIDs)) {
                // sanitize ids (ints) and build IN clause
                $ids = array_map('intval', $processedItemIDs);
                $in = implode(',', $ids);
                $delSql = "DELETE FROM prescriptionitem WHERE prescriptionID = ? AND prescriptionItemID NOT IN ($in)";
                if ($stmtDel = $conn->prepare($delSql)) {
                    $stmtDel->bind_param("i", $prescriptionID);
                    if (!$stmtDel->execute()) {
                        $err = $stmtDel->error;
                        $stmtDel->close();
                        throw new Exception("Error deleting removed items: " . $err);
                    }
                    $stmtDel->close();
                } else {
                    throw new Exception("Prepare failed (delete removed items): " . $conn->error);
                }
            } else {
                // no items submitted -> delete all items for this prescription
                $delAll = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
                if (!$delAll) throw new Exception("Prepare failed (delete all items): " . $conn->error);
                $delAll->bind_param("i", $prescriptionID);
                if (!$delAll->execute()) {
                    $err = $delAll->error;
                    $delAll->close();
                    throw new Exception("Error deleting items: " . $err);
                }
                $delAll->close();
            }

            // 4) keep prescription.medicationID compatibility: set to first item medicationID if exists
            if (!empty($medIDs)) {
                $firstMed = intval($medIDs[0]);
                $upd = $conn->prepare("UPDATE prescription SET medicationID = ? WHERE prescriptionID = ?");
                if ($upd) {
                    $upd->bind_param("ii", $firstMed, $prescriptionID);
                    $upd->execute();
                    $upd->close();
                }
            }

            mysqli_commit($conn);
            $success = true;
            $message = "<div style='padding:12px;background:#e6ffed;border:1px solid #2a8a4a;color:#0a7a2a;border-radius:6px;margin-bottom:12px;'>✔ Prescription updated. Redirecting...</div>";

            // reload items for display after commit
            $itemsStmt = $conn->prepare("SELECT prescriptionItemID, medicationID, dosage, frequency, duration, refill_count, instructions FROM prescriptionitem WHERE prescriptionID = ?");
            $itemsStmt->bind_param("i", $prescriptionID);
            $itemsStmt->execute();
            $itemsRes = $itemsStmt->get_result();
            $itemsStmt->close();

            // refresh presData header (to reflect possible doctor change)
            $presStmt2 = $conn->prepare("SELECT * FROM prescription WHERE prescriptionID = ?");
            $presStmt2->bind_param("i", $prescriptionID);
            $presStmt2->execute();
            $presData = $presStmt2->get_result()->fetch_assoc();
            $presStmt2->close();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}

/* ---------- render ---------- */
ob_start();
?>

    <div class="card">
        <h2>Edit Prescription #<?= htmlspecialchars($prescriptionID) ?></h2>

        <?php if ($message) echo $message; ?>

        <?php if (!empty($errors)): ?>
            <div style="padding:10px;background:#ffe6e6;border:1px solid #d9534f;color:#a30000;border-radius:6px;margin-bottom:12px;">
                <strong>Errors:</strong>
                <ul style="margin:6px 0 0 18px;"><?php foreach ($errors as $er) echo "<li>" . htmlspecialchars($er) . "</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" id="edit-prescription-form" style="max-width:1000px;">
            <label><strong>Doctor</strong></label><br>
            <select name="doctorID" required>
                <option value="">Select doctor</option>
                <?php
                $sessionDoctorID = isset($_SESSION['doctor_id']) ? intval($_SESSION['doctor_id']) : 0;
                if ($doctors) {
                    mysqli_data_seek($doctors, 0);
                    while ($d = $doctors->fetch_assoc()) {
                        $did = (int)$d['doctorID'];
                        $sel = ($sessionDoctorID ? ($sessionDoctorID === $did) : ($presData['doctorID'] == $did)) ? 'selected' : '';
                        echo "<option value=\"$did\" $sel>" . htmlspecialchars($d['firstName'] . ' ' . $d['lastName']) . "</option>";
                    }
                }
                ?>
            </select>
            <br><br>

            <label><strong>Status</strong></label><br>
            <select name="status">
                <option <?= $presData['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                <option <?= $presData['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option <?= $presData['status'] === 'Expired' ? 'selected' : '' ?>>Expired</option>
            </select>
            <br><br>

            <label>Issue Date</label><br>
            <input type="date" name="issueDate" value="<?= htmlspecialchars($presData['issueDate']) ?>"><br><br>

            <label>Expiration Date</label><br>
            <input type="date" name="expirationDate" value="<?= htmlspecialchars($presData['expirationDate']) ?>"><br><br>

            <label>Refill Interval</label><br>
            <input type="text" name="refillInterval" value="<?= htmlspecialchars($presData['refillInterval']) ?>"><br><br>

            <hr>

            <h3>Medications</h3>

            <div id="meds">
                <?php
                // show existing items
                if ($itemsRes && $itemsRes->num_rows > 0) {
                    mysqli_data_seek($medications, 0); // ensure medications result pointer reset for selects below
                    while ($it = $itemsRes->fetch_assoc()):
                        ?>
                        <div style="border:1px solid #eee; padding:8px; margin-bottom:8px; display:flex; gap:8px; align-items:flex-start;">
                            <input type="hidden" name="itemID[]" value="<?= (int)$it['prescriptionItemID'] ?>">
                            <select name="medicationID[]">
                                <?php
                                mysqli_data_seek($medications, 0);
                                while ($m = $medications->fetch_assoc()) {
                                    $sel = ((int)$m['medicationID'] === (int)$it['medicationID']) ? 'selected' : '';
                                    echo "<option value=\"" . (int)$m['medicationID'] . "\" $sel>" . htmlspecialchars($m['genericName'] . ' — ' . $m['brandName']) . "</option>";
                                }
                                ?>
                            </select>

                            <input type="text" name="dosage[]" value="<?= htmlspecialchars($it['dosage']) ?>" placeholder="Dosage" style="min-width:160px;">
                            <input type="text" name="frequency[]" value="<?= htmlspecialchars($it['frequency']) ?>" placeholder="Frequency" style="min-width:140px;">
                            <input type="text" name="duration[]" value="<?= htmlspecialchars($it['duration']) ?>" placeholder="Duration" style="min-width:120px;">
                            <input type="number" name="refillCount[]" min="0" value="<?= (int)$it['refill_count'] ?>" placeholder="Refill count" style="width:110px;">
                            <input type="text" name="instructions[]" value="<?= htmlspecialchars($it['instructions']) ?>" placeholder="Instructions" style="min-width:160px;">
                            <button type="button" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                    <?php
                    endwhile;
                } else {
                    // if there are no existing items, show one empty row
                    mysqli_data_seek($medications, 0);
                    ?>
                    <div style="border:1px solid #eee; padding:8px; margin-bottom:8px; display:flex; gap:8px; align-items:flex-start;">
                        <select name="medicationID[]">
                            <?php mysqli_data_seek($medications, 0); while ($m = $medications->fetch_assoc()): ?>
                                <option value="<?= (int)$m['medicationID'] ?>"><?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <input type="text" name="dosage[]" placeholder="Dosage" style="min-width:160px;">
                        <input type="text" name="frequency[]" placeholder="Frequency" style="min-width:140px;">
                        <input type="text" name="duration[]" placeholder="Duration" style="min-width:120px;">
                        <input type="number" name="refillCount[]" min="0" value="0" placeholder="Refill count" style="width:110px;">
                        <input type="text" name="instructions[]" placeholder="Instructions" style="min-width:160px;">
                        <button type="button" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                <?php } ?>
            </div>

            <button type="button" onclick="addRow()" style="margin-top:10px;">+ Add Medication</button><br><br>

            <button type="submit" class="btn">Update Prescription</button>
            <a href="view_prescription.php" class="btn" style="background:#6c757d;">Cancel</a>
        </form>
    </div>

    <script>
        function addRow() {
            const meds = document.getElementById('meds');
            const div = document.createElement('div');
            div.style = 'border:1px solid #eee; padding:8px; margin-bottom:8px; display:flex; gap:8px; align-items:flex-start;';

            // medication options generated server-side and escaped
            div.innerHTML = `<?php
            mysqli_data_seek($medications, 0);
            $opt = '<select name="medicationID[]">';
            while ($m = $medications->fetch_assoc()) {
                $opt .= "<option value=\"{$m['medicationID']}\">".htmlspecialchars($m['genericName'].' — '.$m['brandName'])."</option>";
            }
            $opt .= '</select>';
            echo str_replace(["\n","\r"],["",""], addslashes($opt));
            ?>
    <input type="text" name="dosage[]" placeholder="Dosage" style="min-width:160px;">
    <input type="text" name="frequency[]" placeholder="Frequency" style="min-width:140px;">
    <input type="text" name="duration[]" placeholder="Duration" style="min-width:120px;">
    <input type="number" name="refillCount[]" min="0" value="0" placeholder="Refill count" style="width:110px;">
    <input type="text" name="instructions[]" placeholder="Instructions" style="min-width:160px;">
    <button type="button" onclick="this.parentElement.remove()">Remove</button>`;

            meds.appendChild(div);
        }

        // If update succeeded server-side, redirect after 2 seconds to view_prescription.php
        <?php if ($success): ?>
        setTimeout(function(){ window.location = 'view_prescription.php'; }, 2000);
        <?php endif; ?>
    </script>

<?php
$content = ob_get_clean();
include('doctor_standard.php');