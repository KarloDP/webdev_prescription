<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MediSync</title>
    <link rel="stylesheet" href="../assets/css/create.css">
</head>
<body>

<div class="container">

    <!-- LEFT IMAGE SIDE -->
    <div class="left-panel">
        <img src="../assets/images/sign-login.png" alt="Pharmacy" class="hero-img">
    </div>

    <!-- RIGHT FORM SIDE -->
    <div class="right-panel">
        <div class="form-wrapper">
            <div class="brand">
                <img src="../assets/images/Main_logo.png" alt="Logo" class="brand-logo">
                <h2 class="brand-name">MediSync</h2>
            </div>  

            <h3 class="form-title">Create Your Account</h3>

            <form action="#" method="POST">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter your full name" required>

                <label>Email</label>
                <input type="text" name="useremail" placeholder="Enter your email" required>

                <label>Password</label>
                <div class="password-field">
                    <input type="password" name="password" placeholder="Enter password" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <label>Confirm Password</label>
                <div class="password-field">
                    <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <button type="submit" class="btn">Create Account</button>
            </form>

             <p class="signup-text">Already have an account? <a href="signin.php">Sign in now</a></p>

            <footer>
                <p>@bytebusters</p>
                <p>©webdevteam</p>
            </footer>
        </div>
    </div>
</div>

</body>
</html>
