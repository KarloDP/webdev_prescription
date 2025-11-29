<?php
session_start();

// TEMPORARY pharmacist credentials
$validEmail = "test@pharmacy.com";
$validPassword = "12345";

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === $validEmail && $password === $validPassword) {

        // Create temporary session
        $_SESSION['pharmacistID'] = 1;
        $_SESSION['pharmacist_name'] = "Test Pharmacist";

        // CORRECT redirect — because we are already inside /frontend/pharmacist/
        header("Location: stock/stock.php");
        exit;
    }
    else {
        $error = "Invalid login credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacist Login</title>

    <style>
        body {
            background: #eef3ef;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            text-align: center;
        }

        h2 {
            color: #1e3d2f;
            margin-bottom: 20px;
        }

        .input-group {
            width: 100%;
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-size: 14px;
            color: #1e3d2f;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #b5c9bc;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 10px 12px;
            background: #1e3d2f;
            color: #fff;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #294f3e;
        }

        .error {
            color: #a40000;
            background: #ffd5d5;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .footer-text {
            margin-top: 12px;
            font-size: 13px;
            color: #4f6658;
        }
    </style>
</head>

<body>

<div class="login-box">
    <h2>Pharmacist Login</h2>

    <?php if (!empty($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <p class="footer-text">Temporary login used for development</p>
</div>

</body>
</html>
