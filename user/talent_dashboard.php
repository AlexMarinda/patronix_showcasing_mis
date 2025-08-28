<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'talent') {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch talent videos count
$stmt = $conn->prepare("SELECT COUNT(*) as total_videos FROM videos WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_videos = $stmt->get_result()->fetch_assoc()['total_videos'] ?? 0;

// Fetch total donations to talent (sum amounts) from donation_history table
$stmt = $conn->prepare("SELECT SUM(amount) as total_donations FROM donation_history WHERE talent_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_donations = $stmt->get_result()->fetch_assoc()['total_donations'] ?? 0;

// Fetch list of videos by talent
$stmt = $conn->prepare("SELECT id, title, category, views, likes,status, uploaded_at FROM videos WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$videos_res = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Talent Dashboard - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            flex: 1;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #333;
            color: white;
        }
        a.btn {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        a.btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
    <div>
        <a href="upload_video.php" class="btn">Upload New Video</a>
        <a href="user_logout.php" class="btn">Logout</a>
    </div>

    <div class="stats">
        <div class="stat-box">
            <h2><?php echo $total_videos; ?></h2>
            <p>Videos Uploaded</p>
        </div>
        <div class="stat-box">
            <h2>RWF<?php echo number_format($total_donations, 2); ?></h2>
            <p>Total Donations Received</p>
        </div>
    </div>

    <h2>Your Uploaded Videos</h2>

    <?php if ($videos_res->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Views</th>
                <th>Likes</th>
                <th>Status</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($video = $videos_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($video['title']); ?></td>
                <td><?php echo htmlspecialchars($video['category']); ?></td>
                <td><?php echo (int)$video['views']; ?></td>
                <td><?php echo (int)$video['likes']; ?></td>
                <td><?php echo $video['status']; ?></td>
                <td><?php echo date('d M Y', strtotime($video['uploaded_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>You have not uploaded any videos yet.</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>