<?php
session_start();
include(__DIR__ . '/../includes/auth.php');
include(__DIR__ . '/../includes/db_connect.php');

// Redirect if not logged in as patient
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = $_SESSION['patientID'];
$activePage = 'medications';

// Fetch patient name for header/profile display
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patientName = $patient['firstName'] . ' ' . $patient['lastName'];
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
  /*
    Query notes:
    - The query attempts to show medication-level prescription records for the patient.
    - If your schema differs (column or table names), adjust the SELECT / JOINs accordingly.
    - The calculation for totalBought uses a LEFT JOIN to a dispense table; if you don't have
      dispenseRecord (or differently named columns), the SELECT below leaves a placeholder.
  */

  $stmt = $conn->prepare("
    SELECT
      p.prescriptionID,
      m.genericName AS medicine,
      m.brandName AS brandName,
      CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
      CONCAT(pi.dosage, ' ', pi.frequency, ' for ', pi.duration) AS prescribedAmount,
      COALESCE(SUM(dr.quantityDispensed), 0) AS totalBought,
      p.issueDate
    FROM prescription p
    JOIN medication m ON p.medicationID = m.medicationID
    JOIN doctor d ON p.doctorID = d.doctorID
    JOIN prescriptionItem pi ON p.prescriptionID = pi.prescriptionID
    /* LEFT JOIN dispenseRecord dr ON pi.prescriptionItemID = dr.prescriptionItemID
       Uncomment the line above if you have a dispenseRecord table with
       prescriptionItemID and quantityDispensed columns. */
    LEFT JOIN dispenseRecord dr ON (0=1) /* placeholder join when dispenseRecord isn't present */
    WHERE p.patientID = ?
    GROUP BY p.prescriptionID, m.genericName, m.brandName, d.firstName, d.lastName, pi.dosage, pi.frequency, pi.duration, p.issueDate
    ORDER BY p.issueDate DESC
  ");
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

      // If totalBought is not computed due to placeholder join, show dash or a friendly note
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
  ?>
</div>

<?php
// End buffer and set $content
$content = ob_get_clean();

// patient_standard.php should render header, sidebar (using $activePage) and echo $content in the main area
include __DIR__ . '/patient_standard.php';
