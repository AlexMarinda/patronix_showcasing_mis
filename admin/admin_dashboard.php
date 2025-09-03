<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin/admin_login.php");
    exit();
}

// Fetch summary data with error handling
$totalDonations = 0;
$totalVideos = 0;

// Total donations
$res = $conn->query("SELECT SUM(amount) as total FROM donation_history");
if ($res) {
    $totalDonations = $res->fetch_assoc()['total'] ?? 0;
}

// Total videos
$res = $conn->query("SELECT COUNT(*) as total FROM videos");
if ($res) {
    $totalVideos = $res->fetch_assoc()['total'] ?? 0;
}

// Total talents
$res = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='talent'");
$totalTalents = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['total'] : 0;

// Total fans
$res = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='fan'");
$totalFans = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['total'] : 0;

// Prepare monthly donation data for chart
$labels = [];
$data = [];
$chartQuery = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total 
                            FROM donations GROUP BY month ORDER BY month ASC");
if ($chartQuery) {
    while ($row = $chartQuery->fetch_assoc()) {
        $labels[] = $row['month'];
        $data[] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-container {
            max-width: 900px;
            margin: 30px auto;
            font-family: Arial, sans-serif;
        }
        .stats-box {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .stats-box > div {
            flex: 1;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 6px;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }
        .logout {
            float: right;
            background: #d9534f;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: -50px;
        }
        .logout:hover {
            background: #c9302c;
        }
        h2, h3 {
            color: #333;
        }
        .admin-nav ul {
            list-style-type: none;
            padding-left: 0;
        }
        .admin-nav ul li {
            margin-bottom: 8px;
        }
        .admin-nav ul li a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: bold;
        }
        .admin-nav ul li a:hover {
            text-decoration: underline;
        }
        /* Chart container */
        .chart-container {
            width: 100%;
            max-width: 800px;
            height: 400px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="admin-container">
         <?php include 'header.php'; ?>
        <a href="admin_logout.php" class="logout">Logout</a>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></h2>

        <!-- ✅ Updated stats boxes -->
        <div class="stats-box">
            <div>Total Donations:<br><span>Frw <?= number_format($totalDonations, 2) ?></span></div>
            <div>Total Talents:<br><span><?= $totalTalents ?></span></div>
            <div>Total Fans:<br><span><?= $totalFans ?></span></div>
            <div>Total Videos:<br><span><?= $totalVideos ?></span></div>
        </div>

        <div class="admin-nav">
            <h3>Admin Management</h3>
            <ul>
                <li><a href="manage_talents.php">Manage Talents</a></li>
                <li><a href="manage_videos.php">Manage Videos</a></li>
                <li><a href="donation_history.php">View Donations</a></li>
                <li><a href="fdi_tools.php">FDI API Tools</a></li>
            </ul>
        </div>

        <h3>Donation Chart (per Month)</h3>
        <div class="chart-container">
            <canvas id="donationChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('donationChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Total Donations ($)',
                        data: <?= json_encode($data) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>