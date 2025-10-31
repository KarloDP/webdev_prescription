<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch all prescriptions with patient + medication details
$query = "
  SELECT
    p.prescriptionID,
    pat.firstName,
    pat.lastName,
    m.genericName,
    m.brandName,
    p.issueDate,
    p.expirationDate,
    p.refillCount,
    p.refillInterval,
    p.status
  FROM prescription p
  JOIN patient pat ON p.patientID = pat.patientID
  JOIN medication m ON p.medicationID = m.medicationID
  ORDER BY p.issueDate DESC
";

$result = mysqli_query($conn, $query);
?>

<div class="main-content">
  <h2>View Prescriptions</h2>

  <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr style="background-color:#f2f2f2;">
      <th>ID</th>
      <th>Patient</th>
      <th>Medication</th>
      <th>Brand</th>
      <th>Issue Date</th>
      <th>Expiration Date</th>
      <th>Refills</th>
      <th>Interval (days)</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>

    <?php
    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
          <td>{$row['prescriptionID']}</td>
          <td>{$row['firstName']} {$row['lastName']}</td>
          <td>{$row['genericName']}</td>
          <td>{$row['brandName']}</td>
          <td>{$row['issueDate']}</td>
          <td>{$row['expirationDate']}</td>
          <td>{$row['refillCount']}</td>
          <td>{$row['refillInterval']}</td>
          <td>{$row['status']}</td>
          <td>
            <a href='edit_prescription.php?id={$row['prescriptionID']}' style='color:blue;'>Edit</a> |
            <a href='delete_prescription.php?id={$row['prescriptionID']}' style='color:red;' onclick='return confirm(\"Are you sure you want to delete this prescription?\");'>Delete</a>
          </td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='10' style='text-align:center;'>No prescriptions found.</td></tr>";
    }
    ?>
  </table>
</div>
