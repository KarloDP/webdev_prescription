<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$user_name = $_SESSION['patient_name'] ?? ($_SESSION['user_name'] ?? 'Patient');
$activePage = $activePage ?? 'dashboard';
$sidebarItems = $sidebarItems ?? [
    'dashboard' => 'Dashboard',
    'history' => 'History',
    'medications' => 'Medications',
    'pharmacies' => 'Pharmacies'
];

// asset base relative to this file (patient folder -> go up one to project root)
$assetBase = dirname($_SERVER['SCRIPT_NAME']) . '/../assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars(ucfirst($activePage) . ' | MediSync'); ?></title>

    <!-- Ensure these files exist: assets/css/layout_standard.css, assets/css/patient_standard.css -->
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/layout_standard.css" />
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/patient_standard.css" />
</head>
<body>
    <header class="top-navbar">
        <div class="logo">
            <img src="<?php echo $assetBase; ?>/images/orange_logo.png" alt="Logo" />
            <span>MediSync Wellness</span>
        </div>
        <div class="profile">
            <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
            <img src="<?php echo $assetBase; ?>/images/user.png" class="avatar" alt="Profile">
            <div class="menu-icon">⋮</div>
        </div>
    </header>

    <aside class="sidebar">
        <ul>
            <?php foreach ($sidebarItems as $key => $label): ?>
                <li class="<?php echo ($activePage === $key) ? 'active' : ''; ?>">
                    <a href="<?php echo $key; ?>.php"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></a>
                </li>
            <?php endforeach; ?>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="content">
        <?php echo isset($content) ? $content : '<h1>Welcome</h1><p>Select an option from the sidebar.</p>'; ?>
    </main>
</body>
</html>
