<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Pagination settings
$limit = 10; // donations per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get total donations count
$totalResult = $conn->query("
    SELECT COUNT(*) as total 
    FROM donation_history d
");
$totalRow = $totalResult->fetch_assoc();
$totalDonations = $totalRow['total'];
$totalPages = ceil($totalDonations / $limit);

// Fetch donations with limit & offset
$donations = $conn->query("
    SELECT d.*, 
           u.fullname AS talent_name, 
           f.fullname AS fan_name, 
           v.title
    FROM donation_history d
    JOIN users u ON d.talent_id = u.id AND u.role = 'talent'
    JOIN users f ON d.fan_id = f.id AND f.role = 'fan'
    JOIN videos v ON d.video_id = v.id
    ORDER BY d.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Donations - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Simple pagination styles */
        .pagination {
            margin: 20px 0;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 6px 12px;
            margin: 0 2px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'header.php'; ?>
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
                    <th>Payment Txn ID</th>
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

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>

    <?php else: ?>
        <p>No donations found.</p>
    <?php endif; ?>
</div>
</body>
</html>
