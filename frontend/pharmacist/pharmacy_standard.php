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

// Dynamically calculate base URL for Docker/virtual host compatibility
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$projectFolder = dirname($_SERVER['SCRIPT_NAME']);
$projectFolder = explode('/frontend', $projectFolder)[0];
$baseUrl = rtrim($protocol . $host . $projectFolder, '/');
$loginPath = rtrim($baseUrl, '/') . '/login.php';

// Now use dynamic login path for authentication
require_login($loginPath, ['pharmacist']);

$user       = $_SESSION['user'] ?? [];
$userName   = htmlspecialchars($user['name'] ?? 'Pharmacist', ENT_QUOTES, 'UTF-8');
$userAvatar = $user['avatar'] ?? '';
if (empty($userAvatar)) {
    $userAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=0E6B3D&color=fff';
}
$userAvatar = htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8');

// Calculate asset paths
$cssBase = rtrim($baseUrl, '/') . '/frontend/css/pharmacist';
$jsBase = rtrim($baseUrl, '/') . '/frontend/pharmacist/js';
$pharmacistBase = rtrim($baseUrl, '/') . '/frontend/pharmacist';
$loginUrl = rtrim($baseUrl, '/') . '/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Panel</title>

    <!-- Favicon (inline to avoid 404) -->
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Base layout CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssBase, ENT_QUOTES, 'UTF-8'); ?>/pharmacy_standard.css">
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
                    <a href="<?php echo htmlspecialchars($pharmacistBase, ENT_QUOTES, 'UTF-8'); ?>/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'patients' ? 'active' : '' ?>">
                    <a href="<?php echo htmlspecialchars($pharmacistBase, ENT_QUOTES, 'UTF-8'); ?>/patients.php"><i class="fas fa-users"></i> Patients</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'dispense' ? 'active' : '' ?>">
                    <a href="<?php echo htmlspecialchars($pharmacistBase, ENT_QUOTES, 'UTF-8'); ?>/dispense.php"><i class="fas fa-pills"></i> Dispense Medication</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'stock' ? 'active' : '' ?>">
                    <a href="<?php echo htmlspecialchars($pharmacistBase, ENT_QUOTES, 'UTF-8'); ?>/stock/stock.php"><i class="fas fa-boxes"></i> Stock Inventory</a>
                </li>
                <li class="<?= ($activePage ?? '') === 'logs' ? 'active' : '' ?>">
                    <a href="<?php echo htmlspecialchars($pharmacistBase, ENT_QUOTES, 'UTF-8'); ?>/audit_logs.php"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="<?php echo htmlspecialchars(rtrim($baseUrl, '/') . '/logout.php', ENT_QUOTES, 'UTF-8'); ?>" class="logout-link">Logout</a>
        </div>
    </aside>

    <main class="content">
        <?= $pageContent ?? '' ?>
    </main>
</div>
</body>
</html>
