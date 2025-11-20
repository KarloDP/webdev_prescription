<?php
session_start();
$activePage = 'profile';

// DB connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "webdev_prescription";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// TEMP — Select which doctor is logged in (same as dashboard)
$doctor_id = 1; // ← Change manually when testing other doctors

// Fetch doctor data
$stmt = $conn->prepare("SELECT * FROM doctor WHERE doctorID = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

ob_start();
?>

<h1>My Profile</h1>
<p>Doctor Information</p>

<div class="profile-container">

    <div class="profile-card">

        <h2><?= $doctor['firstName'] . " " . $doctor['lastName'] ?></h2>
        <p><strong>Specialization:</strong> <?= $doctor['specialization'] ?></p>
        <p><strong>License Number:</strong> <?= $doctor['licenseNumber'] ?></p>
        <p><strong>Email:</strong> <?= $doctor['email'] ?></p>
        <p><strong>Clinic Address:</strong> <?= $doctor['clinicAddress'] ?></p>
        <p><strong>Status:</strong> <?= ucfirst($doctor['status']) ?></p>

    </div>

</div>

<?php
$content = ob_get_clean();
include 'doctor_standard.php';
?>
