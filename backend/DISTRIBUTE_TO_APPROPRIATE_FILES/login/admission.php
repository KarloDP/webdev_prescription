<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admissions - MediSync</title>
    <link rel="stylesheet" href="../assets/css/admission.css">
</head>
<body>

<div class="container">

    <!-- LEFT IMAGE SIDE -->
    <div class="left-panel">
        <img src="../assets/images/siiiiiiiign-login.png" alt="Pharmacy" class="hero-img">
    </div>

    <!-- RIGHT FORM SIDE -->
    <div class="right-panel">
        <div class="form-wrapper">

            <div class="brand">
                <img src="../assets/images/Main_logo.png" alt="Logo" class="brand-logo">
                <h2 class="brand-name">MediSync</h2>
            </div>  

            <h3 class="form-title">Admissions</h3>

            <form action="#" method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>

                <label>Password</label>
                <div class="password-field">
                    <input type="password" name="password" placeholder="Enter password" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <label>Admin ID</label>
                <div class="password-field">
                    <input type="password" name="admin_id" placeholder="Enter Unique ID" required>
                    <span class="toggle-password">👁️</span>
                </div>

                <button type="submit" class="btn">Create</button>
            </form>

            <p class="signup-text">Don’t have an account? <a href="request.php">Request Here</a></p>

            <footer>
                <p>@bytebusters</p>
                <p>©webdevteam</p>
            </footer>

        </div>
    </div>
</div>

</body>
</html>
