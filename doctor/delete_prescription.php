<?php
include('../includes/db_connect.php');

$id = $_GET['id'] ?? 0;

if ($id) {
  $query = "DELETE FROM prescription WHERE prescriptionID = '$id'";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Prescription deleted successfully!'); window.location='view_prescription.php';</script>";
  } else {
    echo "<p style='color:red;'>Error deleting record: " . mysqli_error($conn) . "</p>";
  }
} else {
  echo "<p style='color:red;'>No ID provided.</p>";
}
?>
