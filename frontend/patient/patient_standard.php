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

$user_name = $_SESSION['patient_name']
    ?? $_SESSION['user_name']
    ?? 'Patient';

$activePage = $activePage ?? 'dashboard';
$sidebarItems = $sidebarItems ?? [
    'dashboard'     => 'Dashboard',
    'patient'       => 'History',
    'medication'    => 'Medications',
    'pharmacies'    => 'Pharmacies',
];

/**
 * Base URL of your project as seen in the browser.
 * Adjust "webdev_prescription" if your folder name is different.
 */
$baseUrl = '/webdev_prescription';
$cssBase = $baseUrl . '/frontend/css/patient';
$imgBase = $baseUrl . '/assets/images';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars(ucfirst($activePage) . ' | MediSync'); ?></title>

  <!-- FIXED CSS PATHS -->
  <link rel="stylesheet" href="<?php echo $cssBase; ?>/table.css">
  <link rel="stylesheet" href="<?php echo $cssBase; ?>/layout_standard.css">
  <link rel="stylesheet" href="<?php echo $cssBase; ?>/patient_standard.css">
</head>
<body>

  <!-- Top Navigation Bar -->
  <header class="top-navbar">
    <div class="logo">
      <!-- FIXED IMAGE PATHS (assuming these live in frontend/assets/images) -->
      <img src="<?php echo $imgBase; ?>/orange_logo.png" alt="Logo" />
      <span>MediSync Wellness</span>
    </div>

    <div class="profile">
      <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></span>
      <img src="<?php echo $imgBase; ?>/user.png" class="avatar" alt="Profile" />
      <div class="menu-icon">⋮</div>
    </div>
  </header>

  <!-- Sidebar Navigation -->
 <aside class="sidebar">
  <ul>
    <?php
      foreach ($sidebarItems as $key => $label) {
        // Build the URL properly:
        $url = $baseUrl . '/frontend/patient/' . $key .'/'. $key . '.php';
        $activeClass = ($activePage === $key) ? 'active' : '';
        echo "<li class='$activeClass'><a href='$url'>$label</a></li>";
      }
    ?>
    <li><a href="<?php echo $baseUrl . '/frontend/patient/testlogout.php'; ?>">Logout</a></li>
  </ul>
</aside>

  <!-- Main Page Content -->
  <main class="content">
    <?php
      echo $content ?? '<h1>Welcome</h1><p>Select an option from the sidebar.</p>';
    ?>
  </main>

</body>
</html>
