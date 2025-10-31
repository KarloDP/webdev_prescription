<?php
// show errors while developing
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch patients and medications for dropdowns (used for the form)
$patients = mysqli_query($conn, "SELECT patientID, firstName, lastName FROM patient ORDER BY firstName");
$medications = mysqli_query($conn, "SELECT medicationID, genericName, brandName FROM medication ORDER BY brandName");

// Helper: safe POST get
function post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // gather and validate
    $patientID = post('patientID');
    $medicationID = post('medicationID');
    $issueDate = post('issueDate');            // e.g. 2025-10-31
    $expirationDate = post('expirationDate');  // must be a date or can be calculated
    $refillCount = post('refillCount');
    $refillInterval = post('refillInterval');
    $status = post('status');

    // Basic validation
    if (empty($patientID)) $errors[] = "Please select a patient.";
    if (empty($medicationID)) $errors[] = "Please select a medication.";
    if (empty($issueDate)) $errors[] = "Please select an issue date.";
    if (empty($expirationDate)) $errors[] = "Please select an expiration date.";
    if ($refillCount === null || $refillCount === '') $errors[] = "Please enter refill count (0 if none).";
    if ($refillInterval === null || $refillInterval === '') $errors[] = "Please enter refill interval (days).";
    if (empty($status)) $errors[] = "Please select a status.";

    // Validate numeric fields
    if ($refillCount !== null && $refillCount !== '' && !ctype_digit($refillCount)) $errors[] = "Refill count must be an integer.";
    if ($refillInterval !== null && $refillInterval !== '' && !ctype_digit($refillInterval)) $errors[] = "Refill interval must be an integer.";

    // Validate medication exists (prevent FK error)
    if (empty($errors)) {
        $med_check = mysqli_query($conn, "SELECT medicationID FROM medication WHERE medicationID = '".mysqli_real_escape_string($conn, $medicationID)."' LIMIT 1");
        if (!$med_check || mysqli_num_rows($med_check) === 0) {
            $errors[] = "Selected medication does not exist. Please choose a valid medication.";
        }
    }

    // If valid, insert using prepared statement
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO prescription (patientID, medicationID, issueDate, expirationDate, refillCount, refillInterval, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iissiis", $patientID, $medicationID, $issueDate, $expirationDate, $refillCount, $refillInterval, $status);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Database error: " . $stmt->error;
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

  <form method="POST" action="" style="display:flex; flex-direction:column; width:450px; gap:8px;">
    <label>Patient:</label>
    <select name="patientID" required>
      <option value="">Select Patient</option>
      <?php
      // rewind result pointer if needed
      if ($patients) { mysqli_data_seek($patients, 0); }
      while ($p = mysqli_fetch_assoc($patients)) {
        $sel = (isset($patientID) && $patientID == $p['patientID']) ? 'selected' : '';
        echo "<option value='{$p['patientID']}' $sel>" . htmlspecialchars($p['firstName'] . " " . $p['lastName']) . "</option>";
      }
      ?>
    </select>

    <label>Medication:</label>
    <select name="medicationID" required>
      <option value="">Select Medication</option>
      <?php
      if ($medications) { mysqli_data_seek($medications, 0); }
      while ($m = mysqli_fetch_assoc($medications)) {
        $label = htmlspecialchars($m['genericName'] . " — " . $m['brandName']);
        $sel = (isset($medicationID) && $medicationID == $m['medicationID']) ? 'selected' : '';
        echo "<option value='{$m['medicationID']}' $sel>{$label}</option>";
      }
      ?>
    </select>

    <label>Issue Date:</label>
    <input type="date" name="issueDate" value="<?php echo isset($issueDate) ? htmlspecialchars($issueDate) : ''; ?>" required>

    <label>Expiration Date:</label>
    <input type="date" name="expirationDate" value="<?php echo isset($expirationDate) ? htmlspecialchars($expirationDate) : ''; ?>" required>

    <label>Refill Count:</label>
    <input type="number" name="refillCount" min="0" value="<?php echo isset($refillCount) ? htmlspecialchars($refillCount) : '1'; ?>" required>

    <label>Refill Interval (days):</label>
    <input type="number" name="refillInterval" min="1" value="<?php echo isset($refillInterval) ? htmlspecialchars($refillInterval) : '30'; ?>" required>

    <label>Status:</label>
    <select name="status" required>
      <option value="Active" <?php echo (isset($status) && $status == 'Active') ? 'selected' : ''; ?>>Active</option>
      <option value="Expired" <?php echo (isset($status) && $status == 'Expired') ? 'selected' : ''; ?>>Expired</option>
      <option value="Cancelled" <?php echo (isset($status) && $status == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
    </select>

    <button type="submit" style="padding:10px; background-color:#4CAF50; color:white; border:none; border-radius:5px;">
      Save Prescription
    </button>
  </form>
</div>
