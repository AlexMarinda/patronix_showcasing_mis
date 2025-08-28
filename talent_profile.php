<?php
session_start();
include 'config.php';

if (!isset($_SESSION['talent_id'])) {
    header("Location: talent_login.php");
    exit();
}

$talent_id = $_SESSION['talent_id'];

// Fetch talent info
$talent = $conn->query("SELECT fullname, email, phone FROM talents WHERE id = $talent_id")->fetch_assoc();

// Fetch uploaded videos by talent
$videos = $conn->query("SELECT * FROM videos WHERE user_id = $talent_id ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Talent Profile - Patronix MIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="profile-container">
        <h2>Welcome, <?php echo htmlspecialchars($talent['fullname']); ?></h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($talent['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($talent['phone']); ?></p>

        <a href="upload_video.php">Upload New Video</a> | 
        <a href="talent_logout.php">Logout</a>

        <h3>Your Videos</h3>
        <?php if ($videos->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Uploaded At</th>
                    <th>Views</th>
                </tr>
                <?php while ($video = $videos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($video['title']); ?></td>
                        <td><?php echo htmlspecialchars($video['category']); ?></td>
                        <td><?php echo htmlspecialchars($video['status']); ?></td>
                        <td><?php echo $video['uploaded_at']; ?></td>
                        <td><?php echo $video['views']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>You have not uploaded any videos yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>