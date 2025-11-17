<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
$activePage = 'prescriptions';

$prescriptionID = intval($_GET['id'] ?? 0);
if ($prescriptionID <= 0) {
    header("Location: view_prescription.php");
    exit;
}

/* ---------------- Fetch header ---------------- */
$presStmt = $conn->prepare("SELECT * FROM prescription WHERE prescriptionID = ?");
$presStmt->bind_param("i", $prescriptionID);
$presStmt->execute();
$presData = $presStmt->get_result()->fetch_assoc();
$presStmt->close();

if (!$presData) {
    header("Location: view_prescription.php");
    exit;
}

/* ---------------- Medication list ---------------- */
$medications = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

/* ---------------- Fetch prescription items ---------------- */
$itemsStmt = $conn->prepare("
    SELECT prescriptionItemID, medicationID, dosage, frequency, duration, refill_count, instructions
    FROM prescriptionitem
    WHERE prescriptionID = ?
");
$itemsStmt->bind_param("i", $prescriptionID);
$itemsStmt->execute();
$itemsRes = $itemsStmt->get_result();
$itemsStmt->close();

$errors = [];
$success = false;

/* Determine doctorID */
$doctorID = $_SESSION['doctor_id'] ?? 1;

/* ======================= UPDATE LOGIC ======================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    mysqli_begin_transaction($conn);

    try {

        $status         = $_POST['status'] ?? $presData['status'];
        $refillInterval = $_POST['refillInterval'] ?? $presData['refillInterval'];

        /* -------- Update prescription header -------- */
        $u = $conn->prepare("
            UPDATE prescription
            SET status = ?, refillInterval = ?
            WHERE prescriptionID = ?
        ");
        $u->bind_param("ssi", $status, $refillInterval, $prescriptionID);
        if (!$u->execute()) throw new Exception("Error updating prescription: " . $u->error);
        $u->close();

        /* -------- Collect medication rows -------- */
        $medIDs        = $_POST['medicationID'] ?? [];
        $dosages       = $_POST['dosage'] ?? [];
        $freqs         = $_POST['frequency'] ?? [];
        $durs          = $_POST['duration'] ?? [];
        $instrs        = $_POST['instructions'] ?? [];
        $refillCounts  = $_POST['refillCount'] ?? [];
        $itemIDs       = $_POST['itemID'] ?? [];

        $submittedIDs = [];

        /* ---------------- UPSERT ITEMS ---------------- */
        foreach ($medIDs as $i => $m) {

            $med  = intval($m);
            $dos  = trim($dosages[$i]);
            $frq  = trim($freqs[$i]);
            $dur  = trim($durs[$i]);
            $ins  = trim($instrs[$i]);
            $refc = intval($refillCounts[$i]);
            $itid = isset($itemIDs[$i]) ? intval($itemIDs[$i]) : 0;

            if ($itid > 0) {

                /* --- UPDATE existing item --- */
                $stmt = $conn->prepare("
                    UPDATE prescriptionitem
                    SET medicationID = ?, dosage = ?, frequency = ?, duration = ?, refill_count = ?, instructions = ?
                    WHERE prescriptionItemID = ? AND prescriptionID = ?
                ");

                $stmt->bind_param(
                        "isssisii",
                        $med, $dos, $frq, $dur, $refc, $ins,
                        $itid, $prescriptionID
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error updating item: " . $stmt->error);
                }

                $submittedIDs[] = $itid;
                $stmt->close();

            } else {

                /* --- INSERT new item --- */
                $stmt = $conn->prepare("
                    INSERT INTO prescriptionitem
                    (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions)
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)
                ");

                $stmt->bind_param(
                        "iiisssis",
                        $doctorID, $prescriptionID, $med, $dos, $frq, $dur, $refc, $ins
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error inserting item: " . $stmt->error);
                }

                $submittedIDs[] = $stmt->insert_id;
                $stmt->close();
            }
        }

        /* -------- Delete removed items -------- */
        if (!empty($submittedIDs)) {
            $idList = implode(",", array_map("intval", $submittedIDs));
            $del = $conn->prepare("
                DELETE FROM prescriptionitem
                WHERE prescriptionID = ?
                AND prescriptionItemID NOT IN ($idList)
            ");
            $del->bind_param("i", $prescriptionID);
            $del->execute();
            $del->close();

        } else {
            // If user removed every item
            $delAll = $conn->prepare("DELETE FROM prescriptionitem WHERE prescriptionID = ?");
            $delAll->bind_param("i", $prescriptionID);
            $delAll->execute();
            $delAll->close();
        }

        mysqli_commit($conn);
        $success = true;

        /* Reload items for display */
        $itemsStmt = $conn->prepare("
            SELECT prescriptionItemID, medicationID, dosage, frequency, duration, refill_count, instructions
            FROM prescriptionitem
            WHERE prescriptionID = ?
        ");
        $itemsStmt->bind_param("i", $prescriptionID);
        $itemsStmt->execute();
        $itemsRes = $itemsStmt->get_result();
        $itemsStmt->close();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = $e->getMessage();
    }
}

/* ======================= VIEW LAYER ======================= */

ob_start();
?>

    <div class="card">
        <h2>Edit Prescription #<?= htmlspecialchars($prescriptionID) ?></h2>

        <?php if ($success): ?>
            <div style="color:green;">✔ Updated successfully.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="color:red;">
                <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Status</label><br>
            <select name="status">
                <option <?= $presData['status']=='Active' ? 'selected' : '' ?>>Active</option>
                <option <?= $presData['status']=='Completed' ? 'selected' : '' ?>>Completed</option>
                <option <?= $presData['status']=='Expired' ? 'selected' : '' ?>>Expired</option>
            </select><br><br>

            <label>Refill Interval</label><br>
            <input type="text" name="refillInterval" value="<?= htmlspecialchars($presData['refillInterval']) ?>"><br><br>

            <h3>Medications</h3>

            <div id="meds">
                <?php while ($it = $itemsRes->fetch_assoc()): ?>
                    <div style="border:1px solid #eee; padding:8px; margin-bottom:8px;">

                        <input type="hidden" name="itemID[]" value="<?= $it['prescriptionItemID'] ?>">

                        <select name="medicationID[]">
                            <?php mysqli_data_seek($medications, 0); ?>
                            <?php while ($m = $medications->fetch_assoc()): ?>
                                <option value="<?= $m['medicationID'] ?>"
                                        <?= ($m['medicationID'] == $it['medicationID']) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br>

                        <input type="text" name="dosage[]" value="<?= htmlspecialchars($it['dosage']) ?>" placeholder="Dosage"><br>
                        <input type="text" name="frequency[]" value="<?= htmlspecialchars($it['frequency']) ?>" placeholder="Frequency"><br>
                        <input type="text" name="duration[]" value="<?= htmlspecialchars($it['duration']) ?>" placeholder="Duration"><br>
                        <input type="number" name="refillCount[]" min="0" value="<?= $it['refill_count'] ?>" placeholder="Refill count"><br>
                        <input type="text" name="instructions[]" value="<?= htmlspecialchars($it['instructions']) ?>" placeholder="Instructions"><br>

                        <button type="button" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                <?php endwhile; ?>
            </div>

            <button type="button" onclick="addRow()">+ Add Medication</button><br><br>

            <button class="btn" type="submit">Update Prescription</button>
        </form>
    </div>

    <script>
        function addRow() {
            const meds = document.getElementById("meds");
            const div = document.createElement("div");
            div.style = "border:1px solid #eee; padding:8px; margin-bottom:8px;";

            div.innerHTML = `
        <select name="medicationID[]">
        <?php
            mysqli_data_seek($medications, 0);
            while ($m = $medications->fetch_assoc()) {
                echo "<option value='{$m['medicationID']}'>" .
                        htmlspecialchars($m['genericName'] . " — " . $m['brandName']) .
                        "</option>";
            }
            ?>
        </select><br>

        <input type="text" name="dosage[]" placeholder="Dosage"><br>
        <input type="text" name="frequency[]" placeholder="Frequency"><br>
        <input type="text" name="duration[]" placeholder="Duration"><br>
        <input type="number" name="refillCount[]" min="0" value="0" placeholder="Refill count"><br>
        <input type="text" name="instructions[]" placeholder="Instructions"><br>

        <button type="button" onclick="this.parentElement.remove()">Remove</button>
    `;

            meds.appendChild(div);
        }
    </script>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
