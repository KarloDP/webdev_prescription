<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Request</title>
    <link rel="stylesheet" href="../assets/css/request.css">
</head>
<body>

<div class="container">

    <!-- LEFT IMAGE -->
    <div class="left-panel">
        <img src="../assets/images/sign-login.png" alt="Pharmacy" class="hero-img">
    </div>

    <!-- RIGHT FORM -->
    <div class="right-panel">
        <div class="form-wrapper">

            <div class="brand">
                <img src="../assets/images/Main_logo.png" class="brand-logo">
                <h2 class="brand-name">WebDevTeam</h2>
            </div>

            <h3 class="form-title">Account Request</h3>

            <form action="#" method="POST">

                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>

                <label>Email</label>
                <div class="input-icon">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <span class="email-icon">📧</span>
                </div>

                <label>Password</label>
                <div class="password-field">
                    <input type="password" name="password" placeholder="Enter password" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <label>Certification ID</label>
                <div class="password-field">
                    <input type="password" name="cert_id" placeholder="Enter Certification ID for Validation" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <label>Admin ID</label>
                <div class="password-field">
                    <input type="password" name="admin_id" placeholder="Enter Unique ID" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <button type="submit" class="btn">Create</button>
            </form>

            <p class="signup-text">Already have an account? 
                <a href="/WebDev_Prescription/login/admission.php">Login now</a>
            </p>

            <footer>
                <p>@bytebusters</p>
                <p>©webdevteam</p>
            </footer>

        </div>
    </div>
</div>

</body>
</html>
