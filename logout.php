<?php
require_once __DIR__ . '/backend/includes/auth.php';

clear_user_session(); // to remove all user-related session data


session_destroy(); // completely destroy the session

// Redirect to login page in the same folder
header("Location: login.php");
exit();
?>
