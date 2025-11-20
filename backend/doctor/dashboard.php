<?php
session_start();
$activePage = 'dashboard';

// DB connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "webdev_prescription";
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// TEMP: Set doctor manually for testing
$doctor_id = 1; // <--- change this ONLY for testing

// Count patients for this doctor
$sql1 = $conn->prepare("SELECT COUNT(*) AS total FROM patient WHERE doctorID = ?");
$sql1->bind_param("i", $doctor_id);
$sql1->execute();
$total_patients = $sql1->get_result()->fetch_assoc()['total'];

// Count prescriptions for this doctor
$sql2 = $conn->prepare("SELECT COUNT(*) AS total FROM prescription WHERE doctorID = ?");
$sql2->bind_param("i", $doctor_id);
$sql2->execute();
$total_prescriptions = $sql2->get_result()->fetch_assoc()['total'];

// In your system, profile_updates does not exist, so set fixed number
$total_updates = 0;

ob_start();
?>
<h1>Hello Doctor</h1>
<p>Welcome to MediSync Doctor Dashboard</p>

<div class="stats-container">
    <div class="stat-box">
        <h2><?= $total_patients ?></h2>
        <p>Patients Assigned</p>
    </div>
    <div class="stat-box">
        <h2><?= $total_prescriptions ?></h2>
        <p>Prescriptions Created</p>
    </div>
    <div class="stat-box">
        <h2><?= $total_updates ?></h2>
        <p>Profile Updates</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'doctor_standard.php';
?>
