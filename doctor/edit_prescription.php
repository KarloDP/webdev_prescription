<?php
include('../includes/db_connect.php');
$activePage = 'prescriptions';

$prescriptionID = intval($_GET['id'] ?? 0);
if ($prescriptionID <= 0) { header("Location: view_prescription.php"); exit; }

$pres = $conn->prepare("SELECT * FROM prescription WHERE prescriptionID = ?");
$pres->bind_param("i", $prescriptionID);
$pres->execute();
$presData = $pres->get_result()->fetch_assoc();
$pres->close();

$medications = $conn->query("SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

// fetch items
$items = $conn->prepare("SELECT prescriptionItemID, medicationID, dosage, frequency, duration, instructions FROM prescriptionitem WHERE prescriptionID = ?");
$items->bind_param("i", $prescriptionID);
$items->execute();
$itemsRes = $items->get_result();
$items->close();

$success = false; $errors=[];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $status = $_POST['status'] ?? $presData['status'];
        $refillInterval = $_POST['refillInterval'] ?? $presData['refillInterval'];

        $stmt = $conn->prepare("UPDATE prescription SET status=?, refillInterval=? WHERE prescriptionID=?");
        $stmt->bind_param("ssi", $status, $refillInterval, $prescriptionID);
        $stmt->execute();
        $stmt->close();

        $medIDs = $_POST['medicationID'] ?? [];
        $dosages = $_POST['dosage'] ?? [];
        $freqs = $_POST['frequency'] ?? [];
        $durs = $_POST['duration'] ?? [];
        $instrs = $_POST['instructions'] ?? [];
        $itemIDs = $_POST['itemID'] ?? [];

        foreach ($medIDs as $i => $m) {
            $m = intval($m);
            $dos = $conn->real_escape_string($dosages[$i] ?? '');
            $fr = $conn->real_escape_string($freqs[$i] ?? '');
            $du = $conn->real_escape_string($durs[$i] ?? '');
            $in = $conn->real_escape_string($instrs[$i] ?? '');
            $itid = intval($itemIDs[$i] ?? 0);

            if ($itid > 0) {
                $sql = "UPDATE prescriptionitem SET medicationID=$m, dosage='$dos', frequency='$fr', duration='$du', instructions='$in' WHERE prescriptionItemID=$itid";
                if (!$conn->query($sql)) throw new Exception($conn->error);
            } else {
                $sql = "INSERT INTO prescriptionitem (doctorID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions) VALUES (1, $prescriptionID, $m, '$dos', '$fr', '$du', 0, 0, '$in')";
                if (!$conn->query($sql)) throw new Exception($conn->error);
            }
        }

        mysqli_commit($conn);
        $success = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = $e->getMessage();
    }
    // reload items
    $items = $conn->prepare("SELECT prescriptionItemID, medicationID, dosage, frequency, duration, instructions FROM prescriptionitem WHERE prescriptionID = ?");
    $items->bind_param("i", $prescriptionID);
    $items->execute();
    $itemsRes = $items->get_result();
    $items->close();
}

ob_start();
?>
    <div class="card">
        <h2>Edit Prescription #<?= $prescriptionID ?></h2>
        <?php if ($success): ?><div style="color:green;">Updated.</div><?php endif; ?>
        <?php if (!empty($errors)): ?><div style="color:red;"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div><?php endif; ?>

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
                            <?php mysqli_data_seek($medications,0); while ($m = $medications->fetch_assoc()): $sel = ($m['medicationID']==$it['medicationID'])?'selected':''; ?>
                                <option value="<?= $m['medicationID'] ?>" <?= $sel ?>><?= htmlspecialchars($m['genericName'].' — '.$m['brandName']) ?></option>
                            <?php endwhile; ?>
                        </select><br>
                        <input type="text" name="dosage[]" value="<?= htmlspecialchars($it['dosage']) ?>"><br>
                        <input type="text" name="frequency[]" value="<?= htmlspecialchars($it['frequency']) ?>"><br>
                        <input type="text" name="duration[]" value="<?= htmlspecialchars($it['duration']) ?>"><br>
                        <input type="text" name="instructions[]" value="<?= htmlspecialchars($it['instructions']) ?>"><br>
                        <button type="button" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                <?php endwhile; ?>
            </div>

            <button type="button" onclick="addRow()">+ Add Medication</button><br><br>
            <button class="btn" type="submit">Update Prescription</button>
        </form>
    </div>

    <script>
        function addRow(){
            const meds = document.getElementById('meds');
            const div = document.createElement('div');
            div.style = 'border:1px solid #eee; padding:8px; margin-bottom:8px;';
            div.innerHTML = `<?php
            mysqli_data_seek($medications,0);
            $opt = '<select name="medicationID[]">';
            while ($m = $medications->fetch_assoc()) {
                $opt .= "<option value=\"{$m['medicationID']}\">".htmlspecialchars($m['genericName'].' — '.$m['brandName'])."</option>";
            }
            $opt .= '</select>';
            echo str_replace(["\n","\r"],["",""], $opt);
            ?> <br>
    <input type="text" name="dosage[]" placeholder="Dosage"><br>
    <input type="text" name="frequency[]" placeholder="Frequency"><br>
    <input type="text" name="duration[]" placeholder="Duration"><br>
    <input type="text" name="instructions[]" placeholder="Instructions"><br>
    <button type="button" onclick="this.parentElement.remove()">Remove</button>`;
            meds.appendChild(div);
        }
    </script>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
