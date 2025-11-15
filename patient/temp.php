<?php
// show_prescriptions.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connect.php';

// Whitelist of allowed searchable / sortable columns (map to actual column names)
$searchable = [
    'medicationID' => 'Medication ID',
    'dosage'       => 'Dosage',
    'frequency'    => 'Frequency',
    'duration'     => 'Duration',
    'instructions' => 'Instructions',
    'doctorID'     => 'Doctor ID',
    'refill_count' => 'Refill_Count',
    'prescribed_amount' => 'Prescribed_Amount'
];

// Read input
$prescriptionID = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescriptionID = isset($_POST['prescriptionID']) && $_POST['prescriptionID'] !== '' ? intval($_POST['prescriptionID']) : null;
    $q = isset($_POST['q']) ? trim($_POST['q']) : '';
    $attr = isset($_POST['attr']) ? $_POST['attr'] : 'all';
} else {
    $prescriptionID = isset($_GET['prescriptionID']) && $_GET['prescriptionID'] !== '' ? intval($_GET['prescriptionID']) : null;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $attr = isset($_GET['attr']) ? $_GET['attr'] : 'all';
}

// Validate attribute (must be in whitelist) — else fallback to all
if ($attr !== 'all' && !array_key_exists($attr, $searchable)) {
    $attr = 'all';
}

// Build SELECT and WHERE parts
$select = "doctorID, prescriptionItemID, prescriptionID, medicationID, dosage, frequency, duration, prescribed_amount, refill_count, instructions";
$sql = "SELECT $select FROM prescriptionitem";

$where = [];
$params = [];
$types = '';

// Filter by prescriptionID if provided
if ($prescriptionID !== null) {
    $where[] = "prescriptionID = ?";
    $types .= 'i';
    $params[] = $prescriptionID;
}

// Build search conditions
if ($q !== '') {
    if ($attr !== 'all') {
        // Search only in selected attribute
        if (in_array($attr, ['medicationID', 'doctorID']) && ctype_digit($q)) {
            $where[] = "$attr = ?";
            $types .= 'i';
            $params[] = intval($q);
        } else {
            $where[] = "$attr LIKE ?";
            $types .= 's';
            $params[] = '%' . $q . '%';
        }
    } else {
        // Search across multiple columns
        $sub = [];
        foreach (['dosage', 'frequency', 'duration', 'instructions'] as $c) {
            $sub[] = "$c LIKE ?";
            $types .= 's';
            $params[] = '%' . $q . '%';
        }
        if (ctype_digit($q)) {
            $sub[] = "medicationID = ?";
            $types .= 'i';
            $params[] = intval($q);

            $sub[] = "doctorID = ?";
            $types .= 'i';
            $params[] = intval($q);
        }
        if (!empty($sub)) {
            $where[] = '(' . implode(' OR ', $sub) . ')';
        }
    }
} else {
    // q is empty
    if ($attr !== 'all') {
        // attribute-only selection: show rows where attribute is not null/empty
        if (in_array($attr, ['medicationID', 'doctorID'])) {
            $where[] = "$attr IS NOT NULL";
        } else {
            $where[] = "($attr IS NOT NULL AND $attr <> '')";
        }
    }
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// ORDER BY logic:
// - Always group by prescriptionID (we will output grouped tables).
// - If attr != 'all', order rows within the prescription by that attribute (asc).
//   Use a whitelist to inject the column name safely.
$orderClause = '';
if ($attr !== 'all') {
    // safe column name from whitelist
    $orderByColumn = $attr;
    // For text columns use COLLATE to have consistent ordering; numeric columns are fine
    if (in_array($orderByColumn, ['dosage', 'frequency', 'duration', 'prescribed_amount', 'refill_count', 'instructions'])) {
        $orderClause = "ORDER BY prescriptionID ASC, $orderByColumn COLLATE utf8mb4_general_ci ASC, prescriptionItemID ASC";
    } else {
        // numeric columns
        $orderClause = "ORDER BY prescriptionID ASC, $orderByColumn ASC, prescriptionItemID ASC";
    }
} else {
    $orderClause = "ORDER BY prescriptionID ASC, prescriptionItemID ASC";
}

$sql .= ' ' . $orderClause;

// Prepare statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error) . "<br>SQL: " . htmlspecialchars($sql));
}

// Bind parameters dynamically if present
if (!empty($params)) {
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// Execute and fetch
if (!$stmt->execute()) {
    die("Execute failed: " . htmlspecialchars($stmt->error));
}
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Prescription Items — Search & Arrange</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    form { margin-bottom: 16px; }
    input[type="text"], input[type="number"], select { padding: 6px; font-size: 14px; }
    button { padding: 7px 12px; }
    .prescription { margin-bottom: 28px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
    .prescription h2 { margin: 0; padding: 10px 14px; background:#f8f8f8; border-bottom:1px solid #e1e1e1; font-size:16px; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #fafafa; font-weight: 600; }
    .controls { margin-bottom: 12px; }
    .no-results { color:#666; }
    .note { font-size: 13px; color:#333; margin-bottom:10px; }
</style>
</head>
<body>
<h1>Prescription Items — Search & Arrange</h1>

<form method="POST" class="controls">
    <label for="prescriptionID">Prescription ID:</label>
    <input type="number" name="prescriptionID" id="prescriptionID" value="<?php echo htmlspecialchars($prescriptionID ?? '') ?>" min="1">

    &nbsp;&nbsp;

    <label for="attr">Attribute (arrange by):</label>
    <select name="attr" id="attr">
        <option value="all"<?php echo ($attr === 'all') ? ' selected' : '' ?>>All attributes (default)</option>
        <?php foreach ($searchable as $col => $label): ?>
            <option value="<?php echo htmlspecialchars($col) ?>"<?php echo ($attr === $col) ? ' selected' : '' ?>><?php echo htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;

    <label for="q">Search:</label>
    <input type="text" name="q" id="q" placeholder="keyword or ID" value="<?php echo htmlspecialchars($q) ?>">

    <button type="submit">Apply</button>
    <button type="button" onclick="document.getElementById('resetForm').submit();">Reset</button>
</form>

<form id="resetForm" method="POST" style="display:none">
    <input type="hidden" name="prescriptionID" value="">
    <input type="hidden" name="attr" value="all">
    <input type="hidden" name="q" value="">
</form>

<div class="note">
    Tip: choose an attribute to arrange rows inside each prescription table by that column.
</div>

<?php
// Output grouped tables (rows already ordered by the selected attribute due to SQL ORDER BY)
$currentPrescription = null;
$rowsFound = false;

while ($row = $result->fetch_assoc()) {
    $rowsFound = true;

    $doctorID = $row['doctorID'];
    $prescriptionItemID = $row['prescriptionItemID'];
    $prescriptionID = $row['prescriptionID'];
    $medicationID = $row['medicationID'];
    $dosage = $row['dosage'];
    $frequency = $row['frequency'];
    $duration = $row['duration'];
    $prescribed_amount = $row['prescribed_amount'];
    $refill_count = $row['refill_count'];
    $instructions = $row['instructions'];

    if ($currentPrescription !== $prescriptionID) {
        if ($currentPrescription !== null) {
            echo "</tbody></table></div>\n";
        }
        $currentPrescription = $prescriptionID;
        echo '<div class="prescription">';
        echo '<h2>Prescription ID: ' . htmlspecialchars($prescriptionID) . ' &nbsp; | &nbsp; Doctor ID: ' . htmlspecialchars($doctorID) . '</h2>';
        echo '<table><thead><tr>
                <th>Item ID</th>
                <th>Medication ID</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Prescribed Amount</th>
                <th>Refill Count</th>
                <th>Instructions</th>
              </tr></thead><tbody>';
    }

    echo '<tr>';
    echo '<td>' . htmlspecialchars($prescriptionItemID) . '</td>';
    echo '<td>' . htmlspecialchars($medicationID) . '</td>';
    echo '<td>' . htmlspecialchars($dosage) . '</td>';
    echo '<td>' . htmlspecialchars($frequency) . '</td>';
    echo '<td>' . htmlspecialchars($duration) . '</td>';
    echo '<td>'. htmlspecialchars($prescribed_amount) . '</td>';
    echo '<td>' . htmlspecialchars($refill_count) . '</td>';
    echo '<td>' . nl2br(htmlspecialchars($instructions)) . '</td>';
    echo '</tr>';
}

if ($currentPrescription !== null) {
    echo "</tbody></table></div>\n";
}

if (!$rowsFound) {
    echo '<p class="no-results">No items found matching your criteria.</p>';
}

$stmt->close();
$conn->close();
?>

</body>
</html>
