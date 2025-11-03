<?php
require_once "../includes/db_connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if logs table exists
$hasLogs = ($conn->query("SHOW TABLES LIKE 'logs'")->num_rows > 0);

if ($hasLogs) {
    $result = $conn->query("SELECT * FROM logs ORDER BY timestamp DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>System Logs</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-top:20px; }
    th, td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#f4f4f4; }
  </style>
</head>
<body>
  <h2>System Logs</h2>

  <?php if (!$hasLogs): ?>
    <p><strong>No logs table found.</strong> Create it in your database to start tracking actions.</p>
    <pre>
CREATE TABLE logs (
  logID INT AUTO_INCREMENT PRIMARY KEY,
  user_type ENUM('student','driver','admin') NOT NULL,
  userID INT NOT NULL,
  action VARCHAR(255) NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
    </pre>
  <?php else: ?>
    <table>
      <tr>
        <th>Log ID</th>
        <th>User Type</th>
        <th>User ID</th>
        <th>Action</th>
        <th>Timestamp</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['logID']) ?></td>
          <td><?= htmlspecialchars($row['user_type']) ?></td>
          <td><?= htmlspecialchars($row['userID']) ?></td>
          <td><?= htmlspecialchars($row['action']) ?></td>
          <td><?= htmlspecialchars($row['timestamp']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
</body>
</html>
