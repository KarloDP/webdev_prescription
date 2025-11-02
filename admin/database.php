<?php
require_once "../includes/db_connect.php";

$view = $_GET['view'] ?? 'medicines';

if ($view === 'medicines') {
    $query = "SELECT * FROM medication";
    $title = "Medicines";
} elseif ($view === 'prescriptions') {
    $query = "SELECT * FROM prescription";
    $title = "Prescriptions";
} elseif ($view === 'hospitals') {
    $query = "SELECT * FROM pharmacy";
    $title = "Hospitals";
} else {
    $query = "SELECT * FROM medication";
    $title = "Medicines";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head><title><?= $title ?></title></head>
<body>
  <h2><?= $title ?></h2>
  <table border="1" cellpadding="8">
    <?php if ($view === 'medicines'): ?>
      <tr><th>ID</th><th>Generic</th><th>Brand</th><th>Form</th><th>Strength</th><th>Stock</th></tr>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><button id="arrow-med-<?= $row['medicationID'] ?>" onclick="toggleActions('med-<?= $row['medicationID'] ?>')">▶</button> <?= $row['medicationID'] ?></td>
          <td><?= $row['genericName'] ?></td>
          <td><?= $row['brandName'] ?></td>
          <td><?= $row['form'] ?></td>
          <td><?= $row['strength'] ?></td>
          <td><?= $row['stock'] ?></td>
        </tr>
        <tr id="actions-med-<?= $row['medicationID'] ?>" style="display:none;">
          <td colspan="6">
            <a href="edit_medicine.php?id=<?= $row['medicationID'] ?>" class="btn btn-warning">Edit</a>
            <a href="delete_medicine.php?id=<?= $row['medicationID'] ?>" onclick="return confirm('Delete this medicine?');" class="btn btn-danger">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>

    <?php elseif ($view === 'prescriptions'): ?>
      <tr><th>ID</th><th>MedicationID</th><th>PatientID</th><th>Issue Date</th><th>Expiration</th><th>Status</th></tr>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><button id="arrow-presc-<?= $row['prescriptionID'] ?>" onclick="toggleActions('presc-<?= $row['prescriptionID'] ?>')">▶</button> <?= $row['prescriptionID'] ?></td>
          <td><?= $row['medicationID'] ?></td>
          <td><?= $row['patientID'] ?></td>
          <td><?= $row['issueDate'] ?></td>
          <td><?= $row['expirationDate'] ?></td>
          <td><?= $row['status'] ?></td>
        </tr>
        <tr id="actions-presc-<?= $row['prescriptionID'] ?>" style="display:none;">
          <td colspan="6">
            <a href="edit_prescription.php?id=<?= $row['prescriptionID'] ?>" class="btn btn-warning">Edit</a>
            <a href="delete_prescription.php?id=<?= $row['prescriptionID'] ?>" onclick="return confirm('Delete this prescription?');" class="btn btn-danger">