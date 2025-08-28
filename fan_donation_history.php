<?php
session_start();
include 'config.php';

// For simplicity, this page shows all donations (no fan login system).
// If you add fan accounts, filter by fan user ID here.

$result = $conn->query("
    SELECT dh.*, v.title, v.category, t.fullname AS talent_name 
    FROM donation_history dh
    JOIN videos v ON dh.video_id = v.id
    JOIN talents t ON v.user_id = t.id
    ORDER BY dh.date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donation History - Fan Area</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="history-container">
    <h2>Donation History</h2>
    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Date</th>
                <th>Video</th>
                <th>Category</th>
                <th>Talent</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['date'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['talent_name']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No donation records found.</p>
    <?php endif; ?>
</div>
</body>
</html>