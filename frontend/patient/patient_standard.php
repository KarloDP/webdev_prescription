<?php
// patient_standard.php
// Shared layout for patient pages. Expects pages to set:
//   - $activePage (string)   e.g. 'dashboard' or 'history'
//   - $sidebarItems (array)  optional override for sidebar links
//   - $content (string)      HTML content captured by the page
//
// This file is self-contained and uses relative paths that work when
// included from files inside the patient/ directory.

if (session_status() === PHP_SESSION_NONE) session_start();

// Safe user name from session (fallbacks)
$user_name = $_SESSION['patient_name']
    ?? $_SESSION['user_name']
    ?? 'Patient';

// Defaults
$activePage = $activePage ?? 'dashboard';
$sidebarItems = $sidebarItems ?? [
    'dashboard'     => 'Dashboard',
    'patient'       => 'History',
    'medication'    => 'Medications',
    'pharmacies'    => 'Pharmacies',
];

// Asset base (relative to the current script) — resolves to .../assets
$assetBase = dirname($_SERVER['PHP_SELF']) . '/../assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars(ucfirst($activePage) . ' | MediSync'); ?></title>

  <!-- Shared CSS (table.css and layout + patient standard). -->
  <!-- Adjust these filenames if you use different names on disk -->
  <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/table.css">
  <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/layout_standard.css">
  <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/patient_standard.css">

  <!-- Page-level styles may echo additional <link> tags from the page before include -->
</head>
<body>

  <!-- Top Navigation Bar -->
  <header class="top-navbar">
    <div class="logo">
      <img src="<?php echo $assetBase; ?>/images/orange_logo.png" alt="Logo" />
      <span>MediSync Wellness</span>
    </div>

    <div class="profile">
      <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
      <img src="<?php echo $assetBase; ?>/images/user.png" class="avatar" alt="Profile" />
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
      <li><a href="testlogout.php">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Page Content -->
  <main class="content">
    <?php
      // $content should be set by the including page (buffered output)
      echo $content ?? '<h1>Welcome</h1><p>Select an option from the sidebar.</p>';
    ?>
  </main>

</body>
</html>
