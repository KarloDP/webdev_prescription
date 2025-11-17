<?php
// TestLoginPatient.php
// Minimal, safe login handler that uses includes/db_connect.php (WAMP) and includes/auth.php
// - Sets canonical session keys via set_user_session()
// - Uses POST only for login attempts, GET shows a small local test form
// - Redirects to patient/dashboard.php on success

session_start();

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

$redirectTo = 'patient/dashboard.php';
$error = '';

// Only accept POST for actual login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT patientID, firstName, lastName, email FROM patient WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();

                // Use auth helper to set canonical session keys and regenerate session id
                set_user_session($row);

                // Keep email in session for backwards resolution if needed
                $_SESSION['patient_email'] = $row['email'];

                // Redirect to dashboard (Post/Redirect/Get)
                header('Location: ' . $redirectTo);
                exit;
            } else {
                $error = 'Invalid email or user not found.';
            }

            $stmt->close();
        } else {
            // prepare failed — log and show generic error
            error_log('Login prepare failed: ' . $conn->error);
            $error = 'Server error. Try again later.';
        }
    }
}

// GET fallback: lightweight local test form (remove in production)
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Patient Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; padding: 24px; }
    form { max-width: 420px; }
    label, input { display:block; width:100%; margin-bottom:8px; }
    .error { color:#b00020; margin-bottom:12px; }
  </style>
</head>
<body>
  <h2>Patient Login</h2>

  <?php if ($error !== ''): ?>
    <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" required autocomplete="email">
    <button type="submit">Sign in</button>
  </form>
</body>
</html>
