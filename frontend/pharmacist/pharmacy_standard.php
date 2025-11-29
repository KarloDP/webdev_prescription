<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// This should be the path to your auth.php file
require_once __DIR__ . '/../../backend/includes/auth.php';

// Protect the page, only allow pharmacists
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

// Get user info from the session
$user = $_SESSION['user'];
$userName = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');

// Define base path for assets and determine the final avatar URL
$basePath = '/WebDev_Prescription';
$imgBase = $basePath . '/assets/images/pharmacistUser'; // Assuming your default images are here
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
                        <a href="index.php">Dashboard</a>
                    </li>
                    <li class="<?= $activePage === 'patients' ? 'active' : '' ?>">
                        <a href="#">Patients</a>
                    </li>
                    <li class="<?= $activePage === 'prescription' ? 'active' : '' ?>">
                        <a href="#">Prescription</a>
                    </li>
                    <li class="<?= $activePage === 'dispense' ? 'active' : '' ?>">
                        <a href="#">Dispense Medication</a>
                    </li>
                     <li class="<?= $activePage === 'logs' ? 'active' : '' ?>">
                        <a href="#">Audit Logs</a>
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
</body>
</html>
