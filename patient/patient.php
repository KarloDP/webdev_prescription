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

// Set active page so the patient sidebar highlights the right item
$activePage = 'history';

// Fetch patient name
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
  <!-- Page-specific styles: include reusable table.css and patient/history_patient.css (correct paths) -->
  <!-- Use dirname($_SERVER['PHP_SELF']) so paths resolve regardless of include entrypoint -->
  <link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/table.css">
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/history_patient.css">
  <style>
    /* Small history-page container padding to match layout spacing */
    .history-page {
      padding: 18px;
    }

    /* Fallback if table.css is not yet present; keeps previous look */
    .standard-history {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 30px;
    }
    .standard-history th, .standard-history td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    .standard-history th {
      background-color: #f4f4f4;
    }
    .standard-history td {
      background-color: #fff;
    }

    /* Ensure table-frame and table-base are full width inside main content */
    .table-frame { width: 100%; }

    /* Minor responsive tweaks */
    @media (max-width: 600px) {
      .history-page { padding: 12px; }
    }
  </style>

  <div class="history-page">
    <h2>Prescription History</h2>
    <p>Here are your prescriptions listed row by row.</p>

    <?php
    // Prepared statement: fetch prescription history for the patient
    $stmt = $conn->prepare("
      SELECT
        m.genericName AS medicine,
        p.prescriptionID,
        CONCAT('Dr ', d.firstName, ' ', d.lastName) AS doctorName,
        p.status,
        CONCAT(pi.dosage, ' ', pi.frequency, ' for ', pi.duration) AS qty,
        p.issueDate
      FROM prescription p
      JOIN medication m ON p.medicationID = m.medicationID
      JOIN doctor d ON p.doctorID = d.doctorID
      JOIN prescriptionItem pi ON p.prescriptionID = pi.prescriptionID
      WHERE p.patientID = ?
      ORDER BY p.issueDate DESC
    ");
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      // Wrap table with .table-frame and use .table-base for consistent styling (table.css)
      echo '<div class="table-frame">';
      echo "<table class='table-base standard-history'>";
      echo "<thead>
              <tr>
                <th>Medicine</th>
                <th>Prescription ID</th>
                <th>Doctor Name</th>
                <th>Status</th>
                <th>QTY</th>
                <th>Date Issued</th>
              </tr>
            </thead>";
      echo "<tbody>";

      while ($row = $result->fetch_assoc()) {
        // Format an RX ID similar to your prior logic
        $rxID = 'D060203243454' . str_pad($row['prescriptionID'], 2, '0', STR_PAD_LEFT);
        echo "<tr>
                <td>" . htmlspecialchars($row['medicine']) . "</td>
                <td>" . htmlspecialchars($rxID) . "</td>
                <td>" . htmlspecialchars($row['doctorName']) . "</td>
                <td>" . htmlspecialchars($row['status']) . "</td>
                <td>" . htmlspecialchars($row['qty']) . "</td>
                <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
              </tr>";
      }

      echo "</tbody>";
      echo "</table>";
      echo "</div>";
    } else {
      echo "<p>No prescription history found.</p>";
    }
    ?>
  </div>

<?php
// End buffer and set $content
$content = ob_get_clean();

// patient_standard.php should render header, sidebar (using $activePage) and echo $content in the main area
include __DIR__ . '/patient_standard.php';
