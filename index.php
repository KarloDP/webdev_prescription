<?php
// index.php at: /webdev_prescription/index.php
session_start();

// If a user is already logged in, send them to their dashboard
if (!empty($_SESSION['user']['role'])) {
    switch ($_SESSION['user']['role']) {
        case 'patient':
            header('Location: frontend/patient/dashboard/dashboard.php');
            exit;
        // later: doctor/pharmacist/admin
    }
}

// Not logged in → send to *project* login
header('Location: login.php');   // <-- NO leading slash
exit;
