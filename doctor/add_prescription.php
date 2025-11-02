<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch dropdowns
$patients = mysqli_query($conn, "SELECT patientID, firstName, lastName FROM patient ORDER BY firstName");
$medications = mysqli_query($conn, "SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

function post($key) {
  return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prescriptionID = post('prescriptionID'); // manual ID field
    $patientID = post('patientID');
    $medicationID = post('medicationID');
    $issueDate = post('issueDate');
    $expirationDate = post('expirationDate');
    $refillCount = post('refillCount');
    $refillInterval = post('refillInterval');
    $status = post('status');

    if (empty($prescriptionID)) $errors[] = "Please enter a Prescription ID.";
    if (empty($patientID)) $errors[] = "Please select a patient.";
    if (empty($medicationID)) $errors[] = "Please select a medication.";

    if (empty($errors)) {
        // Prepare insert
        $stmt = $conn->prepare("INSERT INTO prescription (prescriptionID, patientID, medicationID, issueDate, expirationDate, refillCount, refillInterval, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiissiis", $prescriptionID, $patientID, $medicationID, $issueDate, $expirationDate, $refillCount, $refillInterval, $status);

            try {
                $stmt->execute();
                $success = true;
            } catch (mysqli_sql_exception $e) {
                // Duplicate key error code = 1062
                if ($e->getCode() == 1062) {
                    $errors[] = "❌ Prescription ID already exists. Please use a different ID.";
                } else {
                    $errors[] = "Database error: " . $e->getMessage();
                }
            }

            $stmt->close();
        } else {
            $errors[] = "Prepare failed: " . $conn->error;
        }
    }
}
?>

<div class="main-content">
  <h2>Add Prescription</h2>

  <?php if ($success): ?>
    <div style="padding:10px;background:#e6ffed;border:1px solid #1f8a3f;margin-bottom:12px;">
      ✅ Prescription added successfully.
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div style="padding:10px;background:#ffe6e6;border:1px solid #d62b2b;margin-bottom:12px;">
      <strong>Errors:</strong>
      <ul>
        <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" style="display:flex; flex-direction:column; width:450px; gap:8px;">
    <label>Prescription ID:</label>
    <input type="number" name="prescriptionID" value="<?php echo isset($prescriptionID) ? htmlspecialchars($prescriptionID) : ''; ?>" required>

    <label>Patient:</label>
    <select name="patientID" required>
      <option value="">Select Patient</option>
      <?php
      if ($patients) {
        while ($p = mysqli_fetch_assoc($patients)) {
          $sel = (isset($patientID) && $patientID == $p['patientID']) ? 'selected' : '';
          echo "<option value='{$p['patientID']}' $sel>" . htmlspecialchars($p['firstName'] . " " . $p['lastName']) . "</option>";
        }
      }
      ?>
    </select>

    <label>Medication:</label>
    <select name="medicationID" required>
      <option value="">Select Medication</option>
      <?php
      if ($medications) {
        while ($m = mysqli_fetch_assoc($medications)) {
          $sel = (isset($medicationID) && $medicationID == $m['medicationID']) ? 'selected' : '';
          echo "<option value='{$m['medicationID']}' $sel>" . htmlspecialchars($m['genericName'] . " — " . $m['brandName']) . "</option>";
        }
      }
      ?>
    </select>

    <label>Issue Date:</label>
    <input type="date" name="issueDate" required>

    <label>Expiration Date:</label>
    <input type="date" name="expirationDate" required>

    <label>Refill Count:</label>
    <input type="number" name="refillCount" min="0" value="1" required>

    <label>Refill Interval (days):</label>
    <input type="number" name="refillInterval" min="1" value="30" required>

    <label>Status:</label>
    <select name="status" required>
      <option value="Active">Active</option>
      <option value="Expired">Expired</option>
      <option value="Cancelled">Cancelled</option>
    </select>

    <button type="submit" style="padding:10px; background-color:#4CAF50; color:white; border:none; border-radius:5px;">
      Save Prescription
    </button>
  </form>
</div>
