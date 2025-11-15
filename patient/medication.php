<?php
session_start();
include('../includes/auth.php');
include('../includes/db_connect.php');


// Check if patient is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: ../TestLoginPatient.php");
    exit;
}

$patientID = $_SESSION['patientID'];

// Fetch patient name
$patientName = "Patient";
$stmt = $conn->prepare("SELECT firstName, lastName FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $p = $result->fetch_assoc();
    $patientName = $p['firstName'] . ' ' . $p['lastName'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Prescriptions</title>
    <link rel="stylesheet" href="../assets/css/role-patient.css">
    <style>
        .prescription-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }
        .prescription-table th, .prescription-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .prescription-table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>Welcome To MediSync</h3>
    <div class="profile-section">
        <img src="../assets/img/profile.png" alt="Profile" class="profile-icon" />
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
    // FIXED QUERY:
    // - Correct table names: prescriptionitem, dispenserecord
    // - Correct JOIN flow: prescription -> prescriptionitem -> medication -> doctor -> dispenserecord
    // - One row per medication prescribed
    // - Includes dosage, frequency, duration, instructions, status, refill info, dispense totals

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

            COALESCE(SUM(dr.quantityDispensed), 0) AS totalDispensed,
            MAX(dr.nextAvailableDates) AS nextRefillDate
        FROM prescription p
        INNER JOIN prescriptionitem pi ON p.prescriptionID = pi.prescriptionID
        INNER JOIN medication m ON pi.medicationID = m.medicationID
        INNER JOIN doctor d ON p.doctorID = d.doctorID
        LEFT JOIN dispenserecord dr ON pi.prescriptionItemID = dr.prescriptionItemID
        WHERE p.patientID = ?
        GROUP BY pi.prescriptionItemID
        ORDER BY p.issueDate DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        echo "<table class='prescription-table'>";
        echo "
            <tr>
                <th>Prescription ID</th>
                <th>Medicine</th>
                <th>Brand</th>
                <th>Form</th>
                <th>Strength</th>
                <th>Doctor</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Instructions</th>
                <th>Prescription Status</th>
                <th>Total Dispensed</th>
                <th>Next Refill Date</th>
                <th>Issued</th>
                <th>Expires</th>
            </tr>
        ";

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
                    <td>{$row['totalDispensed']} units</td>
                    <td>" . ($row['nextRefillDate'] ? date("F j, Y", strtotime($row['nextRefillDate'])) : "N/A") . "</td>
                    <td>" . date("F j, Y", strtotime($row['issueDate'])) . "</td>
                    <td>" . date("F j, Y", strtotime($row['expirationDate'])) . "</td>
                </tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No medications found.</p>";
    }
    ?>

</div>

</body>
</html>
