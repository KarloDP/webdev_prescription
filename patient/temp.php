<?php
// ---------- Prescription viewer (single patient textbox + filters) ----------
// Paste this into medication.php to replace the previous prescription display block.
// Assumes session_start() was already called earlier in the file. If not, uncomment below.
// session_start();

require_once __DIR__ . '/../includes/db_connect.php';

// ---------- Determine current patient ID (priority: POST override -> session -> nothing) ----------
$sessionPatientID = isset($_SESSION['patientID']) ? intval($_SESSION['patientID']) : null;
$patientID = $sessionPatientID;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID']) && $_POST['patientID'] !== '') {
    $entered = intval($_POST['patientID']);
    if ($entered > 0) {
        $patientID = $entered;
        // Optionally persist override to session: uncomment if you want changes to stick
        // $_SESSION['patientID'] = $patientID;
    }
} elseif (isset($_GET['patientID']) && $_GET['patientID'] !== '') {
    $entered = intval($_GET['patientID']);
    if ($entered > 0) $patientID = $entered;
}

// Read filters (keep them tied to the same single form)
$prescriptionID = null;
$q = '';
$attr = 'all';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescriptionID = (isset($_POST['prescriptionID']) && $_POST['prescriptionID'] !== '') ? intval($_POST['prescriptionID']) : null;
    $q = isset($_POST['q']) ? trim($_POST['q']) : '';
    $attr = isset($_POST['attr']) ? $_POST['attr'] : 'all';
} else {
    $prescriptionID = (isset($_GET['prescriptionID']) && $_GET['prescriptionID'] !== '') ? intval($_GET['prescriptionID']) : null;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $attr = isset($_GET['attr']) ? $_GET['attr'] : 'all';
}

// Simple whitelist for attributes you can arrange by
$searchable = [
    'medicationID'      => 'Medication ID',
    'dosage'            => 'Dosage',
    'frequency'         => 'Frequency',
    'duration'          => 'Duration',
    'instructions'      => 'Instructions',
    'doctorID'          => 'Doctor ID',
    'refill_count'      => 'Refill Count',
    'prescribed_amount' => 'Prescribed Amount'
];
if ($attr !== 'all' && !array_key_exists($attr, $searchable)) $attr = 'all';

// ---------- Render the single top form (patient textbox + filters) ----------
?>
<form method="POST" id="patientFilterForm" style="margin-bottom:16px;">
    <label for="patientID">Patient ID:</label>
    <input type="text" name="patientID" id="patientID"
           value="<?php echo htmlspecialchars($patientID ?? ''); ?>"
           placeholder="Enter patient ID (type & press Enter or wait)..."
           oninput="clearTimeout(window._pidTimer); window._pidTimer=setTimeout(function(){ document.getElementById('patientFilterForm').submit(); }, 450);"
           style="padding:6px; width:120px;" />

    &nbsp;&nbsp;

    <label for="prescriptionID">Prescription ID:</label>
    <input type="number" name="prescriptionID" id="prescriptionID" value="<?php echo htmlspecialchars($prescriptionID ?? ''); ?>" min="1" style="padding:6px; width:90px;">

    &nbsp;&nbsp;

    <label for="attr">Arrange by:</label>
    <select name="attr" id="attr" style="padding:6px;">
        <option value="all"<?php echo ($attr === 'all') ? ' selected' : ''; ?>>All</option>
        <?php foreach ($searchable as $col => $label): ?>
            <option value="<?php echo htmlspecialchars($col); ?>"<?php echo ($attr === $col) ? ' selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;

    <label for="q">Search:</label>
    <input type="text" name="q" id="q" placeholder="keyword or ID" value="<?php echo htmlspecialchars($q); ?>" style="padding:6px; width:200px;">

    &nbsp;
    <button type="submit" style="padding:6px 10px;">Apply</button>
    <button type="button" onclick="document.getElementById('patientFilterForm').reset(); setTimeout(function(){ document.getElementById('patientFilterForm').submit(); }, 10);" style="padding:6px 10px;">Reset</button>
</form>
<?php

// If no patient selected show message but allow the form to remain (no DB query)
if (!isset($patientID) || !is_numeric($patientID) || $patientID <= 0) {
    echo '<p class="no-results">No patient selected. Enter a Patient ID in the box above to load prescriptions.</p>';
    return; // stop here — user can type patient ID and submit
}

// Optionally fetch and show patient name
$patientName = '';
$pnStmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
if ($pnStmt) {
    $pnStmt->bind_param('i', $patientID);
    $pnStmt->execute();
    $pnRes = $pnStmt->get_result();
    if ($pnRes && $pnRes->num_rows > 0) {
        $pRow = $pnRes->fetch_assoc();
        $patientName = trim($pRow['firstName'] . ' ' . $pRow['lastName']);
    }
    $pnStmt->close();
}
echo '<h3>Prescription history for ' . ($patientName ? htmlspecialchars($patientName) : 'Patient ID ' . htmlspecialchars($patientID)) . '.</h3>';

// ---------- Build main query: join prescriptionitem -> prescription -> medication -> doctor
$select = "pi.doctorID,
           pi.prescriptionItemID,
           pi.prescriptionID,
           pi.medicationID,
           m.genericName AS medicine,
           m.brandName AS brandName,
           pi.dosage,
           pi.frequency,
           pi.duration,
           pi.prescribed_amount,
           pi.refill_count,
           pi.instructions,
           p.issueDate,
           CONCAT('Dr ', COALESCE(d.firstName,''), ' ', COALESCE(d.lastName,'')) AS doctorName";

$sql = "SELECT $select
        FROM prescriptionitem pi
        JOIN prescription p ON pi.prescriptionID = p.prescriptionID
        JOIN medication m ON pi.medicationID = m.medicationID
        LEFT JOIN doctor d ON pi.doctorID = d.doctorID
        WHERE p.patientID = ?";

$params = [];
$types = 'i';
$params[] = $patientID;

// optional prescriptionID filter
if ($prescriptionID !== null) {
    $sql .= " AND pi.prescriptionID = ?";
    $types .= 'i';
    $params[] = $prescriptionID;
}

// search handling
if ($q !== '') {
    if ($attr !== 'all') {
        if (in_array($attr, ['medicationID', 'doctorID']) && ctype_digit($q)) {
            $sql .= " AND pi.$attr = ?";
            $types .= 'i';
            $params[] = intval($q);
        } else {
            $sql .= " AND pi.$attr LIKE ?";
            $types .= 's';
            $params[] = '%' . $q . '%';
        }
    } else {
        // search many text columns; include numeric checks if q is numeric
        $sql .= " AND (pi.dosage LIKE ? OR pi.frequency LIKE ? OR pi.duration LIKE ? OR pi.instructions LIKE ?";
        $types .= 's'; $params[] = '%' . $q . '%';
        $types .= 's'; $params[] = '%' . $q . '%';
        $types .= 's'; $params[] = '%' . $q . '%';
        $types .= 's'; $params[] = '%' . $q . '%';
        if (ctype_digit($q)) {
            $sql .= " OR pi.medicationID = ? OR pi.doctorID = ?";
            $types .= 'i'; $params[] = intval($q);
            $types .= 'i'; $params[] = intval($q);
        }
        $sql .= ")";
    }
}

// ORDER BY: group by prescriptionID then by attribute (if chosen)
if ($attr !== 'all') {
    $orderCol = $attr;
    if (in_array($orderCol, ['dosage','frequency','duration','instructions','prescribed_amount','refill_count'])) {
        $sql .= " ORDER BY pi.prescriptionID ASC, pi.$orderCol COLLATE utf8mb4_general_ci ASC, pi.prescriptionItemID ASC";
    } else {
        $sql .= " ORDER BY pi.prescriptionID ASC, pi.$orderCol ASC, pi.prescriptionItemID ASC";
    }
} else {
    $sql .= " ORDER BY pi.prescriptionID ASC, pi.prescriptionItemID ASC";
}

// prepare and bind
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo '<p class="no-results">Query prepare failed: ' . htmlspecialchars($conn->error) . '</p>';
    // debug: echo '<pre>' . htmlspecialchars($sql) . '</pre>';
    return;
}
if (!empty($params)) {
    // dynamic bind
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = "b$i";
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

if (!$stmt->execute()) {
    echo '<p class="no-results">Execute failed: ' . htmlspecialchars($stmt->error) . '</p>';
    return;
}

$result = $stmt->get_result();

// ---------- Output grouped prescription tables ----------
$currentPrescription = null;
$rowsFound = false;

echo '<div class="prescription-list">';
while ($row = $result->fetch_assoc()) {
    $rowsFound = true;

    $prescID = $row['prescriptionID'];
    if ($currentPrescription !== $prescID) {
        // close previous
        if ($currentPrescription !== null) {
            echo "</tbody></table></div>\n";
        }
        $currentPrescription = $prescID;

        // header (prescription id, doctor name, date issued)
        echo '<div class="prescription" style="margin-bottom:16px;border:1px solid #ddd;border-radius:6px;padding:0;overflow:hidden;">';
        echo '<h4 style="margin:0;padding:10px;background:#f6f6f6;border-bottom:1px solid #e6e6e6;">'
             .'Prescription ID: '.htmlspecialchars($prescID)
             .' &nbsp; | &nbsp; '.htmlspecialchars($row['doctorName'])
             .' &nbsp; | &nbsp; '.($row['issueDate'] ? date("F j, Y", strtotime($row['issueDate'])) : '')
             .'</h4>';
        echo '<table style="width:100%;border-collapse:collapse;"><thead><tr>
                <th style="padding:8px;border-bottom:1px solid #eee;">Item ID</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Medicine (ID)</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Brand</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Dosage</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Frequency</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Duration</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Amount</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Refills</th>
                <th style="padding:8px;border-bottom:1px solid #eee;">Instructions</th>
              </tr></thead><tbody>';
    }

    echo '<tr>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['prescriptionItemID']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['medicine']) . ' (' . htmlspecialchars($row['medicationID']) . ')</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['brandName']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['dosage']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['frequency']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['duration']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['prescribed_amount']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($row['refill_count']) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #f0f0f0;">' . nl2br(htmlspecialchars($row['instructions'])) . '</td>';
    echo '</tr>';
}

if ($currentPrescription !== null) {
    echo "</tbody></table></div>\n";
}

if (!$rowsFound) {
    echo '<p class="no-results">No prescription items found for this patient.</p>';
}

$stmt->close();
$conn->close();
?>
