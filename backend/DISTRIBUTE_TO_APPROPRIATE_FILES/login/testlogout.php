<?php
session_start();

// Clear session data
session_unset();
session_destroy();

// Optional: Add a logout message
$message = "You have been logged out successfully.";

// Redirect to your custom login page
header("Location: http://localhost/WebDev_Prescription/TestLoginPatient.php?message=" . urlencode($message));
exit;
?>
