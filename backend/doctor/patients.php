<?php
include('../includes/db_connect.php');
$activePage = 'patients';

/* ------------ PAGE-SPECIFIC CSS ---------------- */
$customCSS = <<<CSS
<style>
.manage-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 15px !important;
}

.manage-table th {
    background: #e8e8e8 !important;
    padding: 10px !important;
    text-align: left !important;
    white-space: nowrap !important;
    border-bottom: 2px solid #ccc !important;
}

.manage-table td {
    padding: 8px !important;
    border-bottom: 1px solid #ddd !important;
}

.action-col {
    width: 200px !important;
}

.action-btn {
    display: block !important;
    padding: 6px 12px !important;
    background: #007bff !important;
    color: white !important;
    text-decoration: none !important;
    border-radius: 6px !important;
    font-weight: bold !important;
    white-space: nowrap !important;
    margin-bottom: 5px !important;
}
.action-btn:hover {
    opacity: 0.85 !important;
}
</style>
CSS;

/* ------------ FETCH PATIENTS ----------------- */
$query = "
    SELECT
        p.patientID, p.firstName, p.lastName, p.birthDate, p.gender,
        p.contactNumber, p.address, p.email,
        d.firstName AS doctorFirst, d.lastName AS doctorLast
    FROM patient p
    LEFT JOIN doctor d ON p.doctorID = d.doctorID
    ORDER BY p.firstName ASC
";

$result = mysqli_query($conn, $query);

/* ------------ PAGE HTML ----------------- */
ob_start();
?>

<div class="main-content">
    <h2>Patient List</h2>

    <a href="add_patient.php"
       style="display:inline-block; background:#28a745; color:white; padding:10px 18px;
              border-radius:6px; text-decoration:none; font-weight:bold; margin-bottom:12px;">
        + Add New Patient
    </a>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="manage-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Birth Date</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Assigned Doctor</th>
                    <th class="action-col">Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['patientID'] ?></td>
                    <td><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
                    <td><?= $row['birthDate'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['contactNumber'] ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['doctorFirst'] . ' ' . $row['doctorLast']) ?></td>

                    <td class="action-col">

                        <a class="action-btn"
                           style="background:#007bff;"
                           href="view_patient_prescription.php?id=<?= $row['patientID'] ?>">
                           View
                        </a>

                        <a class="action-btn"
                           style="background:#28a745;"
                           href="add_prescription.php?id=<?= $row['patientID'] ?>">
                           + Add RX
                        </a>

                        <a class="action-btn"
                           style="background:#dc3545;"
                           href="delete_patient.php?id=<?= $row['patientID'] ?>"
                           onclick="return confirm('Delete this patient and all related prescriptions?');">
                           Delete
                        </a>

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
?>
