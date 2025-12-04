<?php
$activePage = 'profile';

// Load doctor name from session
$doctorName = "Doctor";
if (!empty($_SESSION['user']['name'])) {
    $doctorName = htmlspecialchars($_SESSION['user']['name']);
}

ob_start();
?>

    <div class="profile-page">

        <h1>Profile</h1>
        <p>Welcome, <strong><?= $doctorName ?></strong></p>

        <div class="profile-card">
            <table class="table-base profile-table">
                <tr>
                    <th>Name</th>
                    <td><?= $doctorName ?></td>
                </tr>
                <tr>
                    <th>Specialization</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Qualification</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Hospital</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Clinic</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Years of Experience</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Schedule</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
                <tr>
                    <th>Contact Info</th>
                    <td>—</td> <!-- placeholder -->
                </tr>
            </table>

            <button id="logoutBtn" class="btn-logout">Logout</button>
        </div>

    </div>

    <script src="profile.js"></script>

<?php
$content = ob_get_clean();
include(__DIR__ . '/../doctor_standard.php');
