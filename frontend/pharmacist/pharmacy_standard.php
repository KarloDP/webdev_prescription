<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add cache control headers to prevent caching of sensitive pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past

// This should be the path to your auth.php file
require_once __DIR__ . '/../../backend/includes/auth.php';

// Protect the page, only allow pharmacists
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

// Get user info from the session
$user = $_SESSION['user'];
$userName = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');

// Define base path for assets and determine the final avatar URL
$basePath = '/assets/images/';
$imgBase = $basePath . 'pharmacistUser'; // Assuming your default images are here
$defaultAvatar = $imgBase . '/Dr_Alwin.jpg'; // The default image from your snippet

// Use the user's custom avatar if it exists in the session, otherwise use the default
$userAvatar = !empty($user['avatar_url']) ? $basePath . $user['avatar_url'] : $defaultAvatar;

// This variable will be set by the page that includes this file
$activePage = $activePage ?? 'dashboard';

// This variable is expected to be set by the page including this file
$pageContent = $pageContent ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <!-- Link to your new CSS files -->
    <link rel="stylesheet" href="../css/pharmacist/pharmacy_standard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <?= $pageStyles ?? '' ?>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Integrative Medicine</h2>
            </div>
            <div class="sidebar-profile">
                <img src="<?= $userAvatar ?>" alt="Avatar" class="profile-avatar">
                <div class="profile-info">
                    <span class="profile-name"><?= $userName ?></span>
                    <span class="profile-role">Pharmacist</span>
                </div>
                <!-- Add dropdown for logout later -->
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="<?= $activePage === 'patients' ? 'active' : '' ?>">
                        <a href="patients.php"><i class="fas fa-users"></i> Patients</a>
                    </li>
                    <li class="<?= $activePage === 'prescription' ? 'active' : '' ?>">
                        <a href="prescription.php"><i class="fas fa-file-prescription"></i> Prescription</a>
                    </li>
                    <li class="<?= $activePage === 'dispense' ? 'active' : '' ?>">
                        <a href="dispense.php"><i class="fas fa-pills"></i> Dispense Medication</a>
                    </li>
                     <li class="<?= $activePage === 'logs' ? 'active' : '' ?>">
                        <a href="#"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                 <a href="/WebDev_Prescription/logout.php" class="logout-link">Logout</a>
            </div>
        </aside>

        <main class="content">
            <?= $pageContent ?>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.profile-actions-dropdown .dropdown-toggle');
            const dropdown = document.querySelector('.profile-actions-dropdown');

            if (dropdownToggle && dropdown) {
                dropdownToggle.addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent immediate closing by document click
                    dropdown.classList.toggle('active');
                });

                // Close the dropdown if the user clicks outside of it
                document.addEventListener('click', function(event) {
                    if (!dropdown.contains(event.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>
