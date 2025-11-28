<?php
function renderLogsTable($result) {
    ?>
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
    <?php
}
