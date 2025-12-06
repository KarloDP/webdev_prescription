<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Include authentication
require_once __DIR__ . '/../../backend/includes/auth.php';
require_login('/WebDev_Prescription/login.php', ['pharmacist']);

$user = $_SESSION['user'];
$userName = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');

// Avatar setup
$basePath = '/WebDev_Prescription/assets/images/';
$defaultAvatar = $basePath . 'pharmacistUser/Dr_Alwin.jpg';
$userAvatar = !empty($user['avatar_url']) ? $basePath . $user['avatar_url'] : $defaultAvatar;

// Active page
$activePage = $activePage ?? 'dashboard';

// Page content
$pageContent = $pageContent ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Panel</title>

    <!-- FIXED ABSOLUTE CSS PATH -->
    <link rel="stylesheet" href="/WebDev_Prescription/frontend/css/pharmacist/pharmacy_standard.css">
    <link rel="stylesheet" href="/WebDev_Prescription/frontend/assets/css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <?= $pageStyles ?? '' ?>
</head>
<body>

<div class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Integrative Medicine</h2>
        </div>

        <div class="sidebar-profile">
            <img src="<?= $userAvatar ?>" class="profile-avatar">
            <div class="profile-info">
                <span class="profile-name"><?= $userName ?></span>
                <span class="profile-role">Pharmacist</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>

                <li class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <a href="/WebDev_Prescription/frontend/pharmacist/index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>

                <li class="<?= $activePage === 'patients' ? 'active' : '' ?>">
                    <a href="/WebDev_Prescription/frontend/pharmacist/patients.php">
                        <i class="fas fa-users"></i> Patients
                    </a>
                </li>

                <li class="<?= $activePage === 'prescription' ? 'active' : '' ?>">
                    <a href="/WebDev_Prescription/frontend/pharmacist/prescription.php">
                        <i class="fas fa-file-prescription"></i> Prescription
                    </a>
                </li>

                <li class="<?= $activePage === 'dispense' ? 'active' : '' ?>">
                    <a href="/WebDev_Prescription/frontend/pharmacist/dispense.php">
                        <i class="fas fa-pills"></i> Dispense Medication
                    </a>
                </li>

                <li class="<?= $activePage === 'stock' ? 'active' : '' ?>">
                    <a href="/WebDev_Prescription/frontend/pharmacist/stock/stock.php">
                        <i class="fas fa-boxes"></i> Stock Inventory
                    </a>
                </li>

                <li class="<?= $activePage === 'logs' ? 'active' : '' ?>">
                    <a href="#">
                        <i class="fas fa-clipboard-list"></i> Audit Logs
                    </a>
                </li>

            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="/WebDev_Prescription/logout.php" class="logout-link">Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">
        <?= $pageContent ?>
    </main>

</div>

</body>
</html>
