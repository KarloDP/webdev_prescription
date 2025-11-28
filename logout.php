<?php
session_start();
session_unset();
session_destroy();

// Redirect to login page in the same folder
header("Location: login.php");
exit();
?>
