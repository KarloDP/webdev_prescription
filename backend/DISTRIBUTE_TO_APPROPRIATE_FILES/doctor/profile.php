<?php
include('../includes/db_connect.php');
$activePage = 'profile';

// For demo show doctor 1
$doc = $conn->query("SELECT * FROM doctor WHERE doctorID = 1")->fetch_assoc();

ob_start();
?>
    <div class="card">
        <h2>Profile</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($doc['firstName'].' '.$doc['lastName']) ?></p>
        <p><strong>Specialization:</strong> <?= htmlspecialchars($doc['specialization']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($doc['email']) ?></p>
        <p><strong>Clinic:</strong> <?= htmlspecialchars($doc['clinicAddress']) ?></p>
    </div>
<?php
$content = ob_get_clean();
include('doctor_standard.php');
