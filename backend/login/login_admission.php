<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admissions / Login</title>
    <link rel="stylesheet" href="../assets/css/login_admission.css">
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <img src="../assets/images/LOGO.png" class="nav-logo">
        <img src="../assets/images/World-Health-Organization-WHO-Logo-1536x864.png" class="nav-profile">
    </header>

    <div class="container">

        <!-- LEFT SECTION -->
        <div class="left-panel">
            <h1 class="title">Login/Admission</h1>

            <form action="/WebDev_Prescription/login/admission.php" method="get">
                <button type="submit" class="btn login-btn">LOGIN</button>
            </form>

            <form action="/WebDev_Prescription/login/request.php" method="get">
                <button type="submit" class="btn signup-btn">Request an Account</button>
            </form>

            <div class="special-admission">
                <p>Need help?</p>
                <a href="#" class="special-link">Contact System Admin</a>
            </div>
        </div>

        <!-- RIGHT IMAGE -->
        <div class="right-panel">
            <img src="../assets/images/LoginIMG.png" class="hero-img">
        </div>

    </div>

</body>
</html>
