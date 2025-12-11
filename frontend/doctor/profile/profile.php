<?php
// filepath: c:\wamp64\www\WebDev_Prescription\frontend\doctor\profile\profile.php
require_once(__DIR__ . '/../../../backend/includes/auth.php');
require_once(__DIR__ . '/../../../backend/includes/db_connect.php');
require_login('/webdev_prescription/login.php', ['doctor']);

$activePage = 'profile';

// fetch doctor row
$doctorName = "Doctor";
$specialization = $clinic = $email = $license = $status = '—';

if (isset($_SESSION['user']['id'])) {
    $doctorID = (int)$_SESSION['user']['id'];
    if ($stmt = $conn->prepare("SELECT firstName, lastName, specialization, clinicAddress, email, licenseNumber, status FROM doctor WHERE doctorID = ? LIMIT 1")) {
        $stmt->bind_param('i', $doctorID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $doctorName = htmlspecialchars(trim($row['firstName'] . ' ' . $row['lastName'])) ?: $doctorName;
            $specialization = htmlspecialchars($row['specialization'] ?? '—');
            $clinic = htmlspecialchars($row['clinicAddress'] ?? '—');
            $email = htmlspecialchars($row['email'] ?? '—');
            $license = htmlspecialchars($row['licenseNumber'] ?? '—');
            $status = htmlspecialchars($row['status'] ?? '—');
        }
        $stmt->close();
    }
}

ob_start();
?>
<div class="profile-page">
    <h1>Profile</h1>
    <p>Welcome, <strong><?= $doctorName ?></strong></p>
    <div class="profile-card">
        <table class="table-base profile-table">
            <tr><th>Name</th><td><?= $doctorName ?></td></tr>
            <tr><th>Specialization</th><td><?= $specialization ?></td></tr>
            <tr><th>Clinic</th><td><?= $clinic ?></td></tr>
            <tr><th>Email</th><td><?= $email ?></td></tr>
            <tr><th>License</th><td><?= $license ?></td></tr>
            <tr><th>Status</th><td><?= $status ?></td></tr>
        </table>
    </div>
</div>
<script src="profile.js"></script>
<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
