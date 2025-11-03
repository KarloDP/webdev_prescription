<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$patients = mysqli_query($conn, "SELECT * FROM patients");
?>

<div class="main-content">
  <h2>Patient List</h2>
  <table>
    <tr>
      <th>Name</th><th>Age</th><th>Gender</th><th>Condition</th><th>Prescription</th><th>Last Visit</th><th>Next Appointment</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($patients)) { ?>
      <tr>
        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
        <td><?= $row['age'] ?></td>
        <td><?= $row['gender'] ?></td>
        <td><?= $row['condition_name'] ?></td>
        <td><?= $row['prescription_code'] ?></td>
        <td><?= $row['last_visit'] ?></td>
        <td><?= $row['next_appointment'] ?></td>
      </tr>
    <?php } ?>
  </table>
</div>
