<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = '';

if ($from && $to) {
    $where = "WHERE d.created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
}

$query = "
    SELECT d.*, u.fullname AS talent_name, f.fullname AS fan_name, v.title 
    FROM donations d 
    JOIN users u ON d.talent_id = u.id 
    JOIN fans f ON d.fan_id = f.id 
    JOIN videos v ON d.video_id = v.id 
    $where 
    ORDER BY d.created_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        form input { padding: 5px; margin-right: 10px; }
        .export-links { margin: 15px 0; }
        .export-links a { margin-right: 10px; }
    </style>
</head>
<body>
<div class="admin-container">
    <h2>Donation Reports</h2>
    <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>

    <form method="GET" style="margin-top: 20px;">
        <label>From:</label>
        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" required>
        <label>To:</label>
        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" required>
        <button type="submit">Filter</button>
    </form>

    <div class="export-links">
        <?php if ($from && $to): ?>
            <a href="export_donations_pdf.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>" class="btn">Export PDF</a>
            <a href="export_donations_excel.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>" class="btn">Export Excel</a>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fan</th>
                <th>Talent</th>
                <th>Video</th>
                <th>Amount</th>
                <th>Txn ID</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['fan_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['talent_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>RWF <?php echo number_format($row['amount']); ?></td>
                    <td><?php echo $row['transaction_id']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No data found for selected period.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>