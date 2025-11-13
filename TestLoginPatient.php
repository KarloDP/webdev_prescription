<?php
session_start();
include('includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $query = "SELECT patientID, firstName FROM patient WHERE email = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['patientID'] = $row['patientID'];
    header("Location: patient/dashboard.php");
    exit;
  } else {
    $error = "Invalid email.";
  }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
  <h2>Patient Login</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required>
    <button type="submit">Login</button>
  </form>
</body>
</html>
