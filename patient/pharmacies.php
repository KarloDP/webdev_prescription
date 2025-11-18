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
$activePage = 'pharmacies';

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
<link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/../assets/css/pharmacies_patient.css">

<div class="pharmacies-page">
  <h2>Pharmacies</h2>
  <p>Find nearby pharmacies and their contact details.</p>

  <?php
  /*
    NOTE:
    - The original query selected "operatingHours" which caused a SQL error because that column
      doesn't exist in your pharmacy table.
    - If you later add operatingHours to the DB, change the SELECT below to include it:
        SELECT pharmacyID, name, contactNumber, address, operatingHours
    - For now we select only existing columns and provide a placeholder value for display.
  */

  // Use a placeholder column for operating hours until the real column exists
  $stmt = $conn->prepare("
    SELECT pharmacyID, name, contactNumber, address
    /* , operatingHours  <-- uncomment this when the column exists */
    FROM pharmacy
    ORDER BY name ASC
  ");
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    echo '<div class="table-frame">';
    echo "<table class='table-base'>";
    echo "<thead>
            <tr>
              <th>Pharmacy</th>
              <th>Contact Info</th>
              <th>Address</th>
              <th>Operating Hours</th>
            </tr>
          </thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {
      // Safely escape output
      $name = htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8');
      $contact = htmlspecialchars($row['contactNumber'] ?? '-', ENT_QUOTES, 'UTF-8');
      $address = htmlspecialchars($row['address'] ?? '-', ENT_QUOTES, 'UTF-8');

      // Placeholder for hours: replace with $row['operatingHours'] when column exists
      $hours = '<span class="open-hours">Mon–Sat, 8:00–18:00</span>'; // example placeholder
      // $hours = htmlspecialchars($row['operatingHours'] ?? '-', ENT_QUOTES, 'UTF-8');

      echo "<tr>
              <td>{$name}</td>
              <td>{$contact}</td>
              <td>{$address}</td>
              <td>{$hours}</td>
            </tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
  } else {
    echo '<div class="empty-state"><p>No pharmacies found.</p></div>';
  }
  ?>
</div>

<?php
// End buffer and set $content
$content = ob_get_clean();

// patient_standard.php should render header, sidebar (using $activePage) and echo $content in the main area
include __DIR__ . '/patient_standard.php';
