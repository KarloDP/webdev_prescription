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
require_login('/frontend/login.php', ['pharmacist']); // adjust if your login path differs

$user       = $_SESSION['user'] ?? [];
$userName   = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');
$userAvatar = $user['avatar'] ?? '';
if (empty($userAvatar)) {
    $userAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=0E6B3D&color=fff';
}
$userAvatar = htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8');

// Root-relative base for assets (your site URL is /frontend/…)
$baseUrl = '/WebDev_Prescription/frontend';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Panel</title>

    <!-- Favicon (inline to avoid 404) -->
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==">

    <!-- Base layout CSS -->
    <link rel="stylesheet" href="/frontend/css/pharmacist/pharmacy_standard.css">
    <?= $pageStyles ?? '' ?>
    <?= $pageScripts ?? '' ?>
</head>
<body>
<div class="main-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Integrative Medicine</h2>
        </div>

        <div class="sidebar-profile">
            <img src="<?= $userAvatar ?>" class="profile-avatar" alt="avatar">
            <div class="profile-info">
                <span class="profile-name"><?= $userName ?></span>
                <span class="profile-role">Pharmacist</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <a href="/frontend/pharmacist/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'patients' ? 'active' : '' ?>">
                    <a href="/frontend/pharmacist/patients.php"><i class="fas fa-users"></i> Patients</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'dispense' ? 'active' : '' ?>">
                    <a href="/frontend/pharmacist/dispense.php"><i class="fas fa-pills"></i> Dispense Medication</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>">
                    <a href="/frontend/pharmacist/stock/stock.php"><i class="fas fa-boxes"></i> Stock Inventory</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'logs' ? 'active' : '' ?>">
                    <a href="/frontend/pharmacist/audit_logs.php"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout.php" class="logout-link">Logout</a>
        </div>
    </aside>

    <main class="content">
        <?= $pageContent ?? '' ?>
    </main>
</div>
</body>
</html>
