<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$donations = $conn->query("
    SELECT d.*, u.fullname AS talent_name, f.fullname AS fan_name, v.title
    FROM donations d
    JOIN users u ON d.talent_id = u.id
    JOIN fans f ON d.fan_id = f.id
    JOIN videos v ON d.video_id = v.id
    ORDER BY d.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Donations - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Manage Donations</h2>
        <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>

        <?php if ($donations->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fan</th>
                        <th>Talent</th>
                        <th>Video</th>
                        <th>Amount (RWF)</th>
                        <th>FDI Txn ID</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = $donations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['fan_name']); ?></td>
                            <td><?php echo htmlspecialchars($d['talent_name']); ?></td>
                            <td><?php echo htmlspecialchars($d['title']); ?></td>
                            <td><?php echo number_format($d['amount']); ?></td>
                            <td><?php echo htmlspecialchars($d['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($d['status']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($d['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No donations found.</p>
        <?php endif; ?>
    </div>
</body>
</html>