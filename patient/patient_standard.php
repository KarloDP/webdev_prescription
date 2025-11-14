<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) session_start();

// Retrieve patient/user name safely
$user_name = $_SESSION['patient_name']
    ?? ($_SESSION['user_name'] ?? 'Patient');

// Active page will be provided by each page
$activePage = $activePage ?? 'dashboard';

// Default sidebar items (can be overridden by each page if needed)
$sidebarItems = $sidebarItems ?? [
    'dashboard'   => 'Dashboard',
    'patient'     => 'History',
    'medications' => 'Medications',
    'pharmacies'  => 'Pharmacies'
];

// Asset base path (relative)
$assetBase = dirname($_SERVER['SCRIPT_NAME']) . '/../assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars(ucfirst($activePage) . ' | MediSync'); ?></title>

    <!-- Standard Layout CSS -->
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/layout_standard.css" />
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/patient_standard.css" />
</head>

<body>

    <!-- Top Navigation Bar -->
    <header class="top-navbar">
        <div class="logo">
            <img src="<?php echo $assetBase; ?>/images/orange_logo.png" alt="Logo">
            <span>MediSync Wellness</span>
        </div>

        <div class="profile">
            <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
            <img src="<?php echo $assetBase; ?>/images/user.png" class="avatar" alt="Profile">
            <div class="menu-icon">⋮</div>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul>
            <?php foreach ($sidebarItems as $key => $label): ?>
                <li class="<?php echo ($activePage === $key) ? 'active' : ''; ?>">
                    <a href="<?php echo $key; ?>.php">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Page Content -->
    <main class="content">
        <?php
            echo $content
                ?? '<h1>Welcome</h1><p>Select an option from the sidebar.</p>';
        ?>
    </main>

</body>
</html>