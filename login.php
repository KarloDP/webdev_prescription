<?php
// login.php – patient email-only login (for development/testing)

session_start();

require_once __DIR__ . '/backend/includes/db_connect.php';
require_once __DIR__ . '/backend/includes/auth.php';

$error = "";

// If already logged in as patient, go straight to dashboard
if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'patient') {
    header('Location: frontend/patient/dashboard/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = "Please enter your email.";
    } else {
        // Look up patient by email only
        $sql = "
            SELECT 
                patientID AS patientID,
                email,
                firstName,
                lastName
            FROM patient
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();

                // Insecure (no password check) – OK only for development
                // auth.php should have set_user_session(string $role, array $row)
                set_user_session('patient', $row);

                header('Location: frontend/patient/dashboard/dashboard.php');
                exit;
            } else {
                $error = "No patient found with that email.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Login</title>
</head>
<body>

<h2>Patient Login</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>
        Email:
        <input type="email" name="email" required placeholder="Enter your registered email">
    </label>
    <br><br>
    <button type="submit">Continue</button>
</form>

<p style="margin-top:1rem; font-size:0.9rem; color:#666;">
    (No password required for now – development/testing only.)
</p>

</body>
</html>