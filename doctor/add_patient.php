<?php
session_start();
include('../includes/db_connect.php');
$activePage = 'patients';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $birthDate = $_POST['birthDate'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $contact = trim($_POST['contactNumber'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $healthCondition = trim($_POST['healthCondition'] ?? null);
    $allergies = trim($_POST['allergies'] ?? null);
    $currentMedication = trim($_POST['currentMedication'] ?? null);
    $knownDiseases = trim($_POST['knownDiseases'] ?? null);

    // Basic validation
    if ($firstName === '' || $lastName === '' || !$birthDate || $contact === '') {
        $message = "<div style='color:red;'>Please fill required fields.</div>";
    } else {
        // Check duplicate
        $chk = $conn->prepare("SELECT patientID FROM patient WHERE firstName=? AND lastName=? AND birthDate=? AND contactNumber=? LIMIT 1");
        $chk->bind_param("ssss", $firstName, $lastName, $birthDate, $contact);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $message = "<div style='color:red;'>Patient already exists.</div>";
            $chk->close();
        } else {
            $chk->close();
            $stmt = $conn->prepare("INSERT INTO patient (firstName,lastName,birthDate,gender,contactNumber,address,email,doctorID,healthCondition,allergies,currentMedication,knownDiseases) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            // default doctorID 1 for demo; in real app get from session
            $doctorID = 1;
            $stmt->bind_param("ssssssssssss", $firstName,$lastName,$birthDate,$gender,$contact,$address,$email,$doctorID,$healthCondition,$allergies,$currentMedication,$knownDiseases);
            if ($stmt->execute()) {
                $message = "<div style='color:green;'>Patient added successfully.</div>";
            } else {
                $message = "<div style='color:red;'>Error adding patient: ".$stmt->error."</div>";
            }
            $stmt->close();
        }
    }
}

ob_start();
?>
    <div class="card">
        <h2>Add New Patient</h2>
        <?= $message ?>
        <form method="post" style="max-width:700px;">
            <label>First Name</label><br>
            <input type="text" name="firstName" required><br><br>

            <label>Last Name</label><br>
            <input type="text" name="lastName" required><br><br>

            <label>Birth Date</label><br>
            <input type="date" name="birthDate" required><br><br>

            <label>Gender</label><br>
            <select name="gender" required><option value="">Select</option><option>Male</option><option>Female</option></select><br><br>

            <label>Contact Number</label><br>
            <input type="text" name="contactNumber" required><br><br>

            <label>Email</label><br>
            <input type="email" name="email"><br><br>

            <label>Address</label><br>
            <input type="text" name="address"><br><br>

            <hr>
            <h3>Medical Information</h3>
            <label>Health Condition</label><br>
            <textarea name="healthCondition" rows="2"></textarea><br><br>

            <label>Allergies</label><br>
            <textarea name="allergies" rows="2"></textarea><br><br>

            <label>Current Medication</label><br>
            <textarea name="currentMedication" rows="2"></textarea><br><br>

            <label>Known Diseases</label><br>
            <textarea name="knownDiseases" rows="2"></textarea><br><br>

            <button class="btn" type="submit">Add Patient</button>
        </form>
    </div>
<?php
$content = ob_get_clean();
include('doctor_standard.php');
