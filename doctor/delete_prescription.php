<?php
include('../includes/db_connect.php');

// Get and sanitize the ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Use prepared statements to be safe
    $stmt = $conn->prepare("DELETE FROM prescription WHERE prescriptionID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Prescription deleted successfully!');
                window.location='view_prescription.php';
              </script>";
    } else {
        echo "<p style='color:red;'>Error deleting record: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color:red;'>No ID provided or invalid ID.</p>";
}
?>
