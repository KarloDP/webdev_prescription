<?php
session_start();
include(__DIR__ . '/../includes/auth.php');
include(__DIR__ . '/../includes/db_connect.php');

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = (int) $_SESSION['patientID'];
$activePage = 'medications';

// Fetch patient name for header/profile display (safe fallback)
$patientName = $_SESSION['patient_name'] ?? 'Patient';
if (empty($_SESSION['patient_name'])) {
    $s = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ? LIMIT 1");
    if ($s) {
        $s->bind_param("i", $patientID);
        $s->execute();
        $r = $s->get_result();
        if ($r && $r->num_rows === 1) {
            $row = $r->fetch_assoc();
            $patientName = trim($row['firstName'] . ' ' . $row['lastName']);
            $_SESSION['patient_name'] = $patientName;
        }
        $s->close();
    }
}

// --- Capture page-specific content into $content so patient_standard.php can render it ---
ob_start();
?>

<!-- Page-specific styles: shared table.css + medication page overrides -->
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/medication_patient.css">

<div class="medication-page">
  <h2>Medications</h2>
  <p>Prescription history for <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.</p>

<?php
// Build query: join prescription -> prescriptionitem -> medication -> doctor
// If you have a dispenseRecord table, uncomment the LEFT JOIN and replace the placeholder.
$sql = "
  SELECT
    p.prescriptionID,
    m.genericName AS medicine,
    m.brandName AS brandName,
    CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
    CONCAT(pi.dosage, ' ', pi.frequency, ' for ', pi.duration) AS prescribedAmount,
    COALESCE(SUM(dr.quantityDispensed), 0) AS totalBought,
    p.issueDate
  FROM prescription p
  JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
  JOIN medication m ON pi.medicationID = m.medicationID
  JOIN doctor d ON p.doctorID = d.doctorID
  /* If you have a dispenseRecord table, uncomment the next line and remove the placeholder join below
     LEFT JOIN dispenseRecord dr ON pi.prescriptionItemID = dr.prescriptionItemID
  */
  LEFT JOIN (SELECT 0 AS prescriptionItemID, 0 AS quantityDispensed) dr ON 1=0
  WHERE p.patientID = ?
  GROUP BY p.prescriptionID, m.genericName, m.brandName, d.firstName, d.lastName, pi.dosage, pi.frequency, pi.duration, p.issueDate
  ORDER BY p.issueDate DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // prepare failed: log and show friendly message
    error_log('Medication query prepare failed: ' . $conn->error);
    echo '<div class="empty-state"><p>Unable to load prescriptions at this time.</p></div>';
} else {
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo '<div class="table-frame">';
        echo "<table class='table-base'>";
        echo "<thead>
                <tr>
                  <th>Medicine</th>
                  <th>Medication Brand</th>
                  <th>Doctor Name</th>
                  <th>Prescribed Amount</th>
                  <th>Total Amount Bought</th>
                  <th>Date Issued</th>
                </tr>
              </thead>";
        echo "<tbody>";

        while ($row = $result->fetch_assoc()) {
            $medicine = htmlspecialchars($row['medicine'] ?? '-', ENT_QUOTES, 'UTF-8');
            $brand = htmlspecialchars($row['brandName'] ?? '-', ENT_QUOTES, 'UTF-8');
            $doctor = htmlspecialchars($row['doctorName'] ?? '-', ENT_QUOTES, 'UTF-8');
            $prescribed = htmlspecialchars($row['prescribedAmount'] ?? '-', ENT_QUOTES, 'UTF-8');

            $totalBought = is_numeric($row['totalBought']) ? (int)$row['totalBought'] : 0;
            $totalDisplay = $totalBought > 0 ? $totalBought . ' pcs' : '-';

            $issueDate = !empty($row['issueDate']) ? date("F j, Y", strtotime($row['issueDate'])) : '-';

            echo "<tr>
                    <td>{$medicine}</td>
                    <td>{$brand}</td>
                    <td>{$doctor}</td>
                    <td>{$prescribed}</td>
                    <td>{$totalDisplay}</td>
                    <td>{$issueDate}</td>
                  </tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo '<div class="empty-state"><p>No prescriptions found.</p></div>';
    }

    $stmt->close();
}
?>
</div>

<?php
// End buffer and set $content
$content = ob_get_clean();

// patient_standard.php should render header, sidebar (using $activePage) and echo $content in the main area
include __DIR__ . '/patient_standard.php';
