<?php
session_start();
$activePage = 'profile';

$doctorName = $_SESSION['user']['name'] ?? "Doctor";

ob_start();
?>

    <div class="profile-page">

        <h1>Profile</h1>
        <p>Welcome, <strong><?= htmlspecialchars($doctorName) ?></strong></p>

        <table class="table-base profile-table">
            <tr>
                <th>Name</th>
                <td id="p_name">Loading...</td>
            </tr>
            <tr>
                <th>Specialization</th>
                <td id="p_specialization">—</td>
            </tr>
            <tr>
                <th>License Number</th>
                <td id="p_license">—</td>
            </tr>
            <tr>
                <th>Clinic / Hospital</th>
                <td id="p_clinic">—</td>
            </tr>
            <tr>
                <th>Email</th>
                <td id="p_email">—</td>
            </tr>
            <tr>
                <th>Status</th>
                <td id="p_status">—</td>
            </tr>
        </table>

        <button id="logoutBtn">Logout</button>
    </div>

    <!-- FIXED: VARIABLES MUST BE IN SEPARATE SCRIPT TAG -->
    <script>
        console.log("SESSION doctorID:", <?= json_encode($_SESSION["user"]["doctorID"] ?? null) ?>);
        const LOGGED_DOCTOR_ID = <?= json_encode($_SESSION["user"]["id"] ?? null) ?>;
    </script>

    <script src="profile.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
