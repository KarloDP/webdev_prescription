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
$activePage = 'history';

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

<!-- Page-specific styles: shared table.css + page overrides -->
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/history_patient.css">

<div class="history-page">
  <h2>Patient History</h2>
  <p>Below are all prescriptions and medical history records for <?php echo htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8'); ?>.</p>

  <?php
  $query = "
    SELECT 
        p.prescriptionID,
        p.issueDate,
        p.expirationDate,
        p.refillInterval,
        p.status AS prescriptionStatus,
        m.genericName,
        m.brandName,
        m.form,
        m.strength,
        d.firstName AS doctorFirst,
        d.lastName AS doctorLast,
        pi.dosage,
        pi.frequency,
        pi.duration,
        pi.instructions,
        pi.refill_count,
        COALESCE(dr.totalDispensed, 0) AS totalDispensed,
        dr.nextRefillDate
    FROM prescription p
    INNER JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
    INNER JOIN medication m ON pi.medicationID = m.medicationID
    INNER JOIN doctor d ON p.doctorID = d.doctorID
    LEFT JOIN (
        SELECT 
            prescriptionItemID,
            SUM(quantityDispensed) AS totalDispensed,
            MAX(nextAvailableDates) AS nextRefillDate
        FROM dispenserecord
        GROUP BY prescriptionItemID
    ) dr ON pi.prescriptionItemID = dr.prescriptionItemID
    WHERE p.patientID = ?
    ORDER BY p.issueDate DESC, p.prescriptionID DESC
  ";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $patientID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
      echo '<div class="table-frame">';
      echo "<table class='table-base'>";
      echo "<thead>
              <tr>
                <th>Prescription #</th>
                <th>Medicine</th>
                <th>Brand</th>
                <th>Form</th>
                <th>Strength</th>
                <th>Doctor</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Instructions</th>
                <th>Status</th>
                <th>Total Dispensed</th>
                <th>Next Refill</th>
                <th>Issued</th>
                <th>Expires</th>
              </tr>
            </thead>";
      echo "<tbody>";

      while ($row = $result->fetch_assoc()) {
          $prescriptionID = htmlspecialchars($row['prescriptionID'], ENT_QUOTES, 'UTF-8');
          $genericName    = htmlspecialchars($row['genericName'], ENT_QUOTES, 'UTF-8');
          $brandName      = htmlspecialchars($row['brandName'], ENT_QUOTES, 'UTF-8');
          $form           = htmlspecialchars($row['form'], ENT_QUOTES, 'UTF-8');
          $strength       = htmlspecialchars($row['strength'], ENT_QUOTES, 'UTF-8');
          $doctor         = "Dr. " . htmlspecialchars($row['doctorFirst'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['doctorLast'], ENT_QUOTES, 'UTF-8');
          $dosage         = htmlspecialchars($row['dosage'], ENT_QUOTES, 'UTF-8');
          $frequency      = htmlspecialchars($row['frequency'], ENT_QUOTES, 'UTF-8');
          $duration       = htmlspecialchars($row['duration'], ENT_QUOTES, 'UTF-8');
          $instructions   = htmlspecialchars($row['instructions'], ENT_QUOTES, 'UTF-8');
          $status         = htmlspecialchars($row['prescriptionStatus'], ENT_QUOTES, 'UTF-8');
          $dispensed      = htmlspecialchars($row['totalDispensed'], ENT_QUOTES, 'UTF-8') . " unit(s)";
          $nextRefill     = $row['nextRefillDate'] ? date("F j, Y", strtotime($row['nextRefillDate'])) : "N/A";
          $issued         = date("F j, Y", strtotime($row['issueDate']));
          $expires        = date("F j, Y", strtotime($row['expirationDate']));

          echo "<tr>
                  <td>{$prescriptionID}</td>
                  <td>{$genericName}</td>
                  <td>{$brandName}</td>
                  <td>{$form}</td>
                  <td>{$strength}</td>
                  <td>{$doctor}</td>
                  <td>{$dosage}</td>
                  <td>{$frequency}</td>
                  <td>{$duration}</td>
                  <td>{$instructions}</td>
                  <td>{$status}</td>
                  <td>{$dispensed}</td>
                  <td>{$nextRefill}</td>
                  <td>{$issued}</td>
                  <td>{$expires}</td>
                </tr>";
      }

      echo "</tbody>";
      echo "</table>";
      echo "</div>";
  } else {
      echo '<div class="empty-state"><p>No history records found.</p></div>';
  }
  ?>
</div>

<?php
// End buffer and set $content
$content = ob_get_clean();

// patient_standard.php should render header, sidebar (using $activePage) and echo $content in the main area
include __DIR__ . '/patient_standard.php';
