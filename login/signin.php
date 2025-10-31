    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign In - MediSync</title>
        <link rel="stylesheet" href="../assets/css/signin.css">
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

                <h3 class="form-title">Sign In To Your Account</h3>

                <form action="#" method="POST">
                    <label>Login</label>
                    <input type="text" email="useremail" placeholder="Email" required>

                    <label>Password</label>
                    <div class="password-field">
                        <input type="password" name="password" placeholder="Enter password" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                    <div class="options">
                        <label><input type="checkbox" name="remember"> Remember me</label>
                        <a href="#" class="forgot">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn">Sign in</button>
                </form>

                <p class="signup-text">Don’t have an account? <a href="#">Sign up now</a></p>

                <footer>
                    <p>@bytebusters</p>
                    <p>©webdevteam</p>
                </footer>
            </div>
        </div>
    </div>

    </body>
    </html>
