<?php
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

// Fetch total patients
$patient_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM patient");
$patient_row = mysqli_fetch_assoc($patient_query);
$total_patients = $patient_row['total'];

// Fetch total prescriptions
$appt_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM prescription");
$appt_row = mysqli_fetch_assoc($appt_query);
$total_appts = $appt_row['total'];
?>

<div class="main-content">
  <h2>Dashboard</h2>
  <p>Welcome, Dr. Christina!</p>

  <div class="dashboard-stats">
    <div class="stat-card">
      <h3>Patients</h3>
      <p><?php echo $total_patients; ?></p>
    </div>

    <div class="stat-card">
      <h3>Prescriptions</h3>
      <p><?php echo $total_appts; ?></p>
    </div>
  </div>

  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Contact</th>
    </tr>

    <?php
    // Correct query matching your real column names
    $patients = mysqli_query($conn, "
      SELECT patientID AS id, firstName AS first_name, lastName AS last_name, contactNumber AS contact
      FROM patient

    ");

    while ($row = mysqli_fetch_assoc($patients)) {
      echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['first_name']}</td>
              <td>{$row['last_name']}</td>
              <td>{$row['contact']}</td>
            </tr>";
    }
    ?>
  </table>
</div>
