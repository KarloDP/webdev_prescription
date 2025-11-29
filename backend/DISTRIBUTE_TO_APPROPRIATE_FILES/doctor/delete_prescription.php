<?php
include('../includes/db_connect.php');
session_start();
//backend\DISTRIBUTE_TO_APPROPRIATE_FILES\doctor\delete_prescription.php
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM patient WHERE patientID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Success message + 2 sec redirect
echo "
<html>
<head>
<meta http-equiv='refresh' content='2;url=patients.php'>
<style>
.success-box {
    margin: 40px auto;
    padding: 15px 25px;
    width: 400px;
    background: #d4edda;
    border-left: 6px solid #28a745;
    font-size: 18px;
    border-radius: 4px;
    font-family: Arial;
}
</style>
</head>
<body>
<div class='success-box'>
    ✅ Patient deleted successfully.<br>
    Redirecting in 2 seconds...
</div>
</body>
</html>
";
exit;
?>