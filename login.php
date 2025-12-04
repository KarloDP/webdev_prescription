<?php
session_start();

require_once __DIR__ . '/backend/includes/db_connect.php';
require_once __DIR__ . '/backend/includes/auth.php';

$error = "";
$selectedRole = $_POST['role'] ?? 'patient'; // Default to patient for the dropdown

// If any user is already logged in, redirect them to their dashboard
if (!empty($_SESSION['user']['role'])) {
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
        // Authenticate using the generic function from auth.php
        $userData = authenticate_user($conn, $role, $email, $password);

        if ($userData !== null) {
            // Login is successful, set the session for the correct role
            set_user_session($role, $userData);
            redirect_based_on_role($role);
        } else {
            // Authentication failed
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
    <title>Login</title>
    <link rel="stylesheet" href="frontend/css/login.css">
</head>
<body>

<div class="login-container">
    <div class="logo">
        <!-- Assuming you have a logo image, replace 'path/to/your/logo.png' with the actual path -->
        <img src="assets/images/Integrative-Medicine-Logo.png" alt="Integrative Medicine Logo">
    </div>
    <h2>Login</h2>

    <?php if ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="role">Login as:</label>
            <select name="role" id="role" required>
                <option value="patient" <?= $selectedRole === 'patient' ? 'selected' : '' ?>>Patient</option>
                <option value="doctor" <?= $selectedRole === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                <option value="pharmacist" <?= $selectedRole === 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit">Continue</button>
    </form>
</div>

</body>
</html>