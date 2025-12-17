<?php
session_start();

require_once __DIR__ . '/backend/includes/db_connect.php';
require_once __DIR__ . '/backend/includes/auth.php';
require_once __DIR__ . '/backend/includes/functions.php';

$error = "";
$selectedRole = $_POST['role'] ?? 'patient'; 

if (isset($_SESSION['user']) && isset($_SESSION['user']['role']) && isset($_SESSION['user']['id'])) {
    redirect_based_on_role($_SESSION['user']['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $allowedRoles = ['patient', 'doctor', 'pharmacist', 'admin'];

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } elseif (!in_array($role, $allowedRoles)) {
        $error = "Invalid role selected.";
    } else {
        if ($user = authenticate_user($conn, $role, $email, $password)) {
            set_user_session($role, $user);
            log_audit($conn, $_SESSION['user']['id'], $role, 'Login', 'User logged in successfully');
            redirect_based_on_role($role);
        } else {
            $error = "Invalid credentials for the selected role.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Integrative Medicine</title>
    <link rel="stylesheet" href="frontend/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="split-screen">
    <div class="left-pane">
        <div class="photo-credit"></div>
    </div>

    <div class="right-pane">
        <div class="login-container">
            <div class="logo">
                <img src="assets\images\LOGO.png" alt="Integrative Medicine Logo">
            </div>

            <h1>Nice to see you again</h1>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="role">Login as</label>
                    <select name="role" id="role" class="input-field" required>
                        <option value="patient" <?= $selectedRole === 'patient' ? 'selected' : '' ?>>Patient</option>
                        <option value="doctor" <?= $selectedRole === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                        <option value="pharmacist" <?= $selectedRole === 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="input-field" 
                           placeholder="Email or phone number" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="input-field" 
                               placeholder="Enter password" required>
                    </div>
                </div>

                <div class="form-extras">
                </div>

                <button type="submit" class="btn-primary">Sign in</button>
            </form>

            <div class="auth-links">
                <a href="/frontend/register.php">Create an account</a>
            </div>
            
            <div class="copyright">
                &copy; Integrative Medicine <?= date("Y"); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>