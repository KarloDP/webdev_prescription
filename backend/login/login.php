<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <!-- NAVBAR / HEADER -->
    <header class="header">
        <img src="../assets/images/LOGO.png" class="nav-logo">
        <img src="../assets/images/World-Health-Organization-WHO-Logo-1536x864.png" class="nav-profile">
    </header>

    <div class="container">
        <div class="left-panel">
            <h1 class="title">MediSync</h1>

            <form action="/WebDev_Prescription/login/signin.php" method="get">
                <button type="submit" class="btn login-btn">LOGIN</button>
            </form>

            <form action="/WebDev_Prescription/login/create.php" method="get">
                <button type="submit" class="btn signup-btn">SIGN UP</button>
            </form>

            <!-- Special Admission link -->
            <div class="special-admission">
                <p>For Partnered Pharmacies and Doctors</p>
                <a href="/WebDev_Prescription/login/login_admission.php" class="special-link">Special Admission</a>
            </div>  
        </div>

        <div class="right-panel">
            <img src="../assets/images/LoginIMG.png" class="hero-img">
        </div>
    </div>
</body>
</html>
