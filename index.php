<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Tracker</title>
</head>
<body>

<h2>Doctors List</h2>

<?php
include 'db.php';

// Use the correct table name and columns
$sql = "SELECT * FROM doctor";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Specialization</th>
            <th>License #</th>
            <th>Email</th>
            <th>Clinic Address</th>
          </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["doctorID"] . "</td>";
        echo "<td>" . $row["firstName"] . "</td>";
        echo "<td>" . $row["lastName"] . "</td>";
        echo "<td>" . $row["specialization"] . "</td>";
        echo "<td>" . $row["licenseNumber"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["clinicAddress"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No doctors found.";
}

$conn->close();
?>

</body>
</html>
