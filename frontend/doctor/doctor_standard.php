<?php
// frontend/doctor/doctor_standard.php
//
// Shared layout for doctor pages. Expects pages to set:
//   - $activePage (string)   e.g. 'dashboard' or 'patients'
//   - $sidebarItems (array)  optional override for sidebar links
//   - $content (string)      HTML content captured by the page
//
// This file is self-contained and uses relative paths that work when
// included from files inside the doctor/ directory.

if (session_status() === PHP_SESSION_NONE) session_start();

$user_name = $_SESSION['doctor_name']
    ?? $_SESSION['user_name']
    ?? 'Doctor';

$activePage = $activePage ?? 'dashboard';
$sidebarItems = $sidebarItems ?? [
    'dashboard'     => 'Dashboard',
    'patients'      => 'Patients',
    'prescriptions' => 'Prescriptions',
    'profile'       => 'Profile',
];

/**
 * Compute base URL so CSS and image paths work regardless of hosting.
 * This keeps links consistent whether pages are served from PHP wrappers
 * or static HTML under /frontend/doctor/...
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Example SCRIPT_NAME: /webdev_prescription/frontend/doctor/dashboard/index.html
$projectFolder = dirname($_SERVER['SCRIPT_NAME']);            // e.g. /webdev_prescription/frontend/doctor
$projectFolder = explode('/frontend', $projectFolder)[0];     // keep only root folder
$baseUrl = rtrim($protocol . $host . $projectFolder, '/');

$cssDoctorBase = rtrim($baseUrl, '/') . '/frontend/css/doctor';
$cssPatientBase = rtrim($baseUrl, '/') . '/frontend/css/patient';
$imgBase = rtrim($baseUrl, '/') . '/assets/images';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars(ucfirst($activePage) . ' | MediSync', ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Core/shared styles (use patient folder for shared table/layout files that exist in your tree) -->
  <link rel="stylesheet" href="<?php echo $cssPatientBase; ?>/table.css">
  <link rel="stylesheet" href="<?php echo $cssPatientBase; ?>/layout_standard.css">

  <!-- Doctor-specific styles -->
  <link rel="stylesheet" href="<?php echo $cssDoctorBase; ?>/doctor_standard.css">
</head>
<body>

  <!-- Top Navigation Bar -->
  <header class="top-navbar">
    <div class="logo">
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
          // Link to the frontend doctor pages (static HTML index files)
          $url = $baseUrl . '/frontend/doctor/' . $key . '/index.html';
          $activeClass = ($activePage === $key) ? 'active' : '';
          echo "<li class='" . htmlspecialchars($activeClass, ENT_QUOTES, 'UTF-8') . "'>";
          echo "<a href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</a>";
          echo "</li>";
        }
      ?>

      <!-- Profile (explicit link) -->
      <?php
        $profileActive = ($activePage === 'profile') ? 'active' : '';
        $profileUrl = $baseUrl . '/frontend/doctor/profile/index.html';
        echo "<li class='" . htmlspecialchars($profileActive, ENT_QUOTES, 'UTF-8') . "'>";
        echo "<a href='" . htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') . "'>View Profile</a>";
        echo "</li>";
      ?>

      <!-- Logout -->
      <li><a href="<?php echo $baseUrl . '/logout.php'; ?>">Logout</a></li>
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