<?php
// frontend/patient/profile/edit_profile.php
ob_start();
session_start();
include(__DIR__ . '/../../../backend/includes/auth.php');
include(__DIR__ . '/../../../backend/includes/db_connect.php');

if (!isset($_SESSION['patientID'])) {
  header("Location: ../TestLoginPatient.php");
  exit;
}

$patientID = $_SESSION['patientID'];
$stmt = $conn->prepare("SELECT firstName, lastName, birthDate, contactNumber, email, address
                        FROM patient WHERE patientID = ?");
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $contactNumber = trim($_POST['contactNumber']);
  $email = trim($_POST['email']);
  $address = trim($_POST['address']);

  if (empty($contactNumber) || empty($email) || empty($address)) {
    $error = "All fields must be filled out.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif (!preg_match("/^[0-9]{10}$/", $contactNumber)) {
    $error = "Contact number must be exactly 10 digits.";
  }elseif (
    $contactNumber === $patient['contactNumber'] &&
    $email === $patient['email'] &&
    $address === $patient['address']
  ) {
    $error = "You didn't change anything.";
  } else {
    $checkEmail = $conn->prepare("SELECT patientID FROM patient WHERE email = ? AND patientID != ?");
    $checkEmail->bind_param("si", $email, $patientID);
    $checkEmail->execute();
    $checkResult = $checkEmail->get_result();

    if ($checkResult->num_rows > 0) {
      $error = "This email is already in use.";
    } else {
      $update = $conn->prepare("UPDATE patient
                                SET contactNumber = ?, email = ?, address = ?
                                WHERE patientID = ?");
      $update->bind_param("sssi", $contactNumber, $email, $address, $patientID);

      if ($update->execute()) {
        header("Location: profile.php?updated=1");
        exit();
      } else {
        $error = "Database error: " . $conn->error;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../assets/css/role-patient.css">
</head>
<body>
<div class="main-content">
  <h2>Edit My Profile</h2>

  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

  <form method="POST" action="" class="profile-form">
    <p><strong>Name:</strong> <?= htmlspecialchars($patient['firstName'] . ' ' . $patient['lastName']) ?></p>
    <p><strong>Birthdate:</strong> <?= htmlspecialchars($patient['birthDate']) ?></p>

    <label>Contact Number:</label>
    <input type="text" name="contactNumber" value="<?= htmlspecialchars($patient['contactNumber']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>" required>

    <label>Address:</label>
    <input type="text" name="address" value="<?= htmlspecialchars($patient['address']) ?>" required>

    <button type="submit" class="btn-save">Save Changes</button>
    <a href="profile.php" class="btn-cancel">Cancel</a>
  </form>
</div>
</body>
</html>
<?php
$content = ob_get_clean();
include __DIR__ . '/../patient_standard.php';
?>