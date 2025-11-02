<?php
require_once "../includes/db_connect.php";
$result = $conn->query("SELECT * FROM logs ORDER BY timestamp DESC");
?>
<!DOCTYPE html>
<html>
<head><title>System Logs</title></head>
<body>
  <h2>System Logs</h2>
  <table border="1" cellpadding="8">
    <tr><th>ID</th><th>Action</th><th>User</th><th>Timestamp</th></tr>
    <?php while($row = $result->fetch_assoc()): ?>
