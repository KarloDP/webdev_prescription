<?php
// patients.php
include('../includes/db_connect.php');
$activePage = 'patients';

// get patients + assigned doctor name
$query = "
    SELECT p.patientID, p.firstName, p.lastName, p.birthDate, p.gender,
           p.contactNumber, p.address, p.email, d.firstName AS doctorFirst, d.lastName AS doctorLast
    FROM patient p
    LEFT JOIN doctor d ON p.doctorID = d.doctorID
    ORDER BY p.firstName ASC
";

$res = $conn->query($query);
if ($res === false) {
    die("DB error: " . $conn->error);
}

ob_start();
?>

    <div class="card">
        <h2>Patient List</h2>
        <a class="btn" href="add_patient.php">+ Add New Patient</a>
    </div>

    <div class="card">
        <?php if ($res && $res->num_rows > 0): ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Birth Date</th><th>Gender</th>
                    <th>Contact</th><th>Email</th><th>Address</th><th>Doctor</th><th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patientID']) ?></td>
                        <td><?= htmlspecialchars($row['firstName'].' '.$row['lastName']) ?></td>
                        <td><?= htmlspecialchars($row['birthDate']) ?></td>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['contactNumber']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars(trim($row['doctorFirst'].' '.$row['doctorLast'])) ?></td>
                        <td>
                            <a href="view_patient_prescription.php?id=<?= (int)$row['patientID'] ?>">View</a> |
                            <!-- pass id to add_prescription.php so it can auto-select patient -->
                            <a href="add_prescription.php?id=<?= (int)$row['patientID'] ?>">+ Add RX</a> |
                            <a class="danger" href="delete_patient.php?id=<?= (int)$row['patientID'] ?>" onclick="return confirm('Delete patient and all prescriptions?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No patients found.</p>
        <?php endif; ?>
    </div>

<?php
$content = ob_get_clean();
include('doctor_standard.php');
