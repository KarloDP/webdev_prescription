<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$id = $_GET['id'] ?? 0;
$result = mysqli_query($conn, "
  SELECT * FROM prescription WHERE prescriptionID = '$id'
");
$row = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $_POST['status'];
  $refillCount = $_POST['refillCount'];
  $refillInterval = $_POST['refillInterval'];

  $update = "
    UPDATE prescription
    SET status='$status', refillCount='$refillCount', refillInterval='$refillInterval'
    WHERE prescriptionID='$id'
  ";

  if (mysqli_query($conn, $update)) {
    echo "<script>alert('Prescription updated successfully!'); window.location='view_prescription.php';</script>";
  } else {
    echo "<p style='color:red;'>Error updating prescription.</p>";
  }
}
?>

<div class='main-content'>
  <h2>Edit Prescription</h2>

  <?php if ($row) { ?>
    <form method='POST'>
      <label>Refill Count:</label>
      <input type='number' name='refillCount' value='<?= $row['refillCount'] ?>' required><br><br>

      <label>Refill Interval (days):</label>
      <input type='text' name='refillInterval' value='<?= $row['refillInterval'] ?>' required><br><br>

      <label>Status:</label>
      <select name='status'>
        <option value='Active' <?= $row['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
        <option value='Completed' <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
        <option value='Expired' <?= $row['status'] == 'Expired' ? 'selected' : '' ?>>Expired</option>
      </select><br><br>

      <button type='submit'>Update</button>
    </form>
  <?php } else { ?>
    <p>Prescription not found.</p>
  <?php } ?>
</div>
