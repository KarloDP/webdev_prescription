<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');


if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;

@@ -11,9 +10,6 @@ if (!isset($_SESSION['patientID'])) {

$patientID = $_SESSION['patientID'];




// Fetch patient name
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");

@@ -21,116 +17,161 @@ $stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $patientName = $row['firstName'] . ' ' . $row['lastName'];
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescriptions & Medication Details</title>
    <link rel="stylesheet" href="../assets/css/role-patient.css">




    <style>
        .prescription-table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        .prescription-table th, .prescription-table td {

            padding: 8px;
            border: 1px solid #ccc;



        }
        .prescription-table th {
            background: #f4f4f4;
        }
    </style>
</head>

<body>


<!-- Sidebar -->
<div class="sidebar">
    <h3>Welcome To MediSync</h3>
    <div class="profile-section">
        <img src="../assets/img/profile.png" class="profile-icon">
        <p><?= strtoupper($patientName) ?></p>
    </div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="patient.php">History</a></li>
        <li><a href="medication.php" class="active">Medications</a></li>
        <li><a href="pharmacies.php">Pharmacies</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Prescriptions & Medication Details</h2>
    <p>Below are all medications prescribed to <?= $patientName ?>.</p>

    <?php
    // ---- FIXED QUERY ----
    // Subquery aggregates dispense records per prescriptionItemID (avoids GROUP BY errors)
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

    if ($result->num_rows > 0) {

        echo "<table class='prescription-table'>


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
            </tr>";

        while ($row = $result->fetch_assoc()) {


            echo "<tr>
                <td>{$row['prescriptionID']}</td>
                <td>{$row['genericName']}</td>
                <td>{$row['brandName']}</td>
                <td>{$row['form']}</td>
                <td>{$row['strength']}</td>

                <td>Dr. {$row['doctorFirst']} {$row['doctorLast']}</td>

                <td>{$row['dosage']}</td>
                <td>{$row['frequency']}</td>
                <td>{$row['duration']}</td>
                <td>{$row['instructions']}</td>

                <td>{$row['prescriptionStatus']}</td>
                <td>{$row['totalDispensed']} unit(s)</td>

                <td>" . ($row['nextRefillDate'] ? date("F j, Y", strtotime($row['nextRefillDate'])) : "N/A") . "</td>

                <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
                <td>" . date("F j, Y", strtotime($row['expirationDate'])) . "</td>
            </tr>";
        }


        echo "</table>";

    } else {
        echo "<p>No medication records found.</p>";
    }

    $stmt->close();
    ?>


</div>
</body>
</html>
