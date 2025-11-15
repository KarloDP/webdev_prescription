<?php
session_start();
include('../includes/db_connect.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$doctorID = $_SESSION['doctor_id'] ?? 0;
$activePage = 'patients';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $birthDate = $_POST['birthDate'];
    $gender = $_POST['gender'];
    $contact = trim($_POST['contactNumber']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    // NEW FIELDS
    $healthCondition = trim($_POST['healthCondition']);
    $allergies = trim($_POST['allergies']);
    $currentMedication = trim($_POST['currentMedication']);
    $knownDiseases = trim($_POST['knownDiseases']);

    // Prevent duplicates
    $check = $conn->prepare("
        SELECT patientID FROM patient
        WHERE firstName = ? AND lastName = ? AND birthDate = ? AND contactNumber = ?
    ");
    $check->bind_param("ssss", $firstName, $lastName, $birthDate, $contact);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<p style='color:red; font-weight:bold;'>⚠ Patient already exists!</p>";
    } else {

        // Insert patient
        $stmt = $conn->prepare("
            INSERT INTO patient
            (firstName, lastName, birthDate, gender, contactNumber, address, email, doctorID,
             healthCondition, allergies, currentMedication, knownDiseases)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssssssss",
            $firstName, $lastName, $birthDate, $gender, $contact, $address, $email, $doctorID,
            $healthCondition, $allergies, $currentMedication, $knownDiseases
        );

        if ($stmt->execute()) {
            $message = "<p style='color:green; font-weight:bold;'>✔ Patient added successfully!</p>";
        } else {
            $message = "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
        }
    }
}
?>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content" style="padding:30px;">

    <h2 style="text-align:center;">Add New Patient</h2>
    <?= $message ?>

    <div style="
        width: 500px;
        margin: 0 auto;
        padding: 25px;
        background: #f8f8f8;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    ">

        <form method="POST">

            <label>First Name:</label>
            <input type="text" name="firstName" required class="input-field">

            <label>Last Name:</label>
            <input type="text" name="lastName" required class="input-field">

            <label>Birth Date:</label>
            <input type="date" name="birthDate" required class="input-field">

            <label>Gender:</label>
            <select name="gender" required class="input-field">
                <option value="">Select Gender</option>
                <option>Male</option>
                <option>Female</option>
            </select>

            <label>Contact Number:</label>
            <input type="text" name="contactNumber" required class="input-field">

            <label>Email:</label>
            <input type="email" name="email" class="input-field">

            <label>Address:</label>
            <input type="text" name="address" class="input-field">

            <hr style="margin:20px 0;">

            <h3>Medical Information</h3>

            <label>Health Condition:</label>
            <textarea name="healthCondition" class="input-field"></textarea>

            <label>Allergies:</label>
            <textarea name="allergies" class="input-field"></textarea>

            <label>Current Medication:</label>
            <textarea name="currentMedication" class="input-field"></textarea>

            <label>Known Diseases:</label>
            <textarea name="knownDiseases" class="input-field"></textarea>

            <button type="submit"
                style="
                    margin-top: 15px;
                    width: 100%;
                    padding: 10px;
                    background: #28a745;
                    color: white;
                    border: none;
                    font-size: 16px;
                    border-radius: 5px;
                    font-weight: bold;
                ">
                Add Patient
            </button>

        </form>

    </div>

</div>

<style>
.input-field {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>
