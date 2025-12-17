<?php
session_start();
if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
    header('Location: /logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create an Account</title>
    <link rel="stylesheet" href="/frontend/css/register.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
</head>
<body>
    <div class="register-wrapper">
        <h1>Create an account</h1>
        <p class="helper">Select your role and complete the required details.</p>
        <form id="register-form" novalidate>
            <div class="role-row">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select role...</option>
                    <option value="doctor">Doctor</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="patient">Patient</option>
                </select>
            </div>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="6" required>

            <label for="confirm">Confirm Password</label>
            <input id="confirm" type="password" autocomplete="new-password" required>

            <div id="dynamic-fields" class="dynamic-fields"></div>

            <div class="actions">
                <button type="submit" id="submit-btn">Register</button>
                <a class="link" href="/login.php">Back to login</a>
            </div>

            <div id="form-message" class="message" role="alert"></div>
            <small class="note">By registering, you acknowledge pending approval for professional roles.</small>
        </form>
    </div>

    <script src="/frontend/js/register.js" defer></script>
</body>
</html>