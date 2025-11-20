<?php
// add_patient.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../includes/db_connect.php');

$activePage = 'patients';

$doctors = $conn->query("SELECT doctorID, firstName, lastName FROM doctor ORDER BY firstName");

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $birthDate = $_POST['birthDate'] ?? null;
    $gender = trim($_POST['gender'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $doctorID = intval($_POST['doctorID'] ?? 0);

    if ($firstName === '' || $lastName === '') $errors[] = "First and last name required.";
    if (empty($birthDate)) $errors[] = "Birth date required.";
    if ($doctorID <= 0) $errors[] = "Select a doctor.";

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO patient (firstName, lastName, birthDate, gender, contactNumber, address, email, doctorID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssss ssi", $firstName, $lastName, $birthDate, $gender, $contactNumber, $address, $email, $doctorID);
            // NOTE: small PHP quirk — bind_param requires types combined without spaces; we'll correct
            // Rebind properly
            $stmt->close();

            // Re-prepare properly
            $stmt = $conn->prepare("
                INSERT INTO patient (firstName, lastName, birthDate, gender, contactNumber, address, email, doctorID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssssi", $firstName, $lastName, $birthDate, $gender, $contactNumber, $address, $email, $doctorID);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            $success = true;
            // show message and redirect back to patients.php (or referrer) - B2 behavior
        } catch (Exception $e) {
            $errors[] = "DB error: " . $e->getMessage();
        }
    }
}

ob_start();
?>

    <div class="card">
        <h2>Add Patient</h2>

        <?php if ($success): ?>
            <div style="padding:10px;background:#e6ffed;border:1px solid #1f8a3f;margin-bottom:12px;">
                ✅ Patient added successfully. Redirecting...
            </div>
            <script>
                setTimeout(function(){
                    // redirect back to referrer if exists, else patients.php
                    var ref = <?= json_encode($_SERVER['HTTP_REFERER'] ?? 'patients.php') ?>;
                    window.location.href = ref || 'patients.php';
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="padding:10px;background:#ffe6e6;border:1px solid #d62b2b;margin-bottom:12px;">
                <strong>Errors:</strong>
                <ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width:700px;">
            <label>First name</label><br>
            <input type="text" name="firstName" required value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>"><br><br>

            <label>Last name</label><br>
            <input type="text" name="lastName" required value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>"><br><br>

            <label>Birth Date</label><br>
            <input type="date" name="birthDate" required value="<?= htmlspecialchars($_POST['birthDate'] ?? '') ?>"><br><br>

            <label>Gender</label><br>
            <select name="gender">
                <option <?php if(($_POST['gender'] ?? '')=='Male') echo 'selected' ?>>Male</option>
                <option <?php if(($_POST['gender'] ?? '')=='Female') echo 'selected' ?>>Female</option>
                <option <?php if(($_POST['gender'] ?? '')=='Other') echo 'selected' ?>>Other</option>
            </select><br><br>

            <label>Contact Number</label><br>
            <input type="text" name="contactNumber" value="<?= htmlspecialchars($_POST['contactNumber'] ?? '') ?>"><br><br>

            <label>Address</label><br>
            <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"><br><br>

            <label>Email</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><br><br>

            <label>Doctor</label><br>
            <select name="doctorID" required>
                <option value="">Select Doctor</option>
                <?php while($d = $doctors->fetch_assoc()): ?>
                    <option value="<?= (int)$d['doctorID'] ?>" <?= (isset($_POST['doctorID']) && $_POST['doctorID']==$d['doctorID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['firstName'].' '.$d['lastName']) ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>

            <button class="btn" type="submit">Add Patient</button>
        </form>
    </div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');