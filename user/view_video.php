<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$video_id = intval($_GET['id'] ?? 0);
if ($video_id <= 0) {
    die("Invalid video ID.");
}

// Fetch video and talent info
$sql = $conn->prepare("
    SELECT v.*, u.fullname AS talent_name 
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    WHERE v.id = ? AND v.status = 'approved'
");
$sql->bind_param("i", $video_id);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    die("Video not found or not approved.");
}

$video = $result->fetch_assoc();

// ✅ Increment views only once per session per video
if (!isset($_SESSION['viewed_videos'])) {
    $_SESSION['viewed_videos'] = [];
}

if (!in_array($video_id, $_SESSION['viewed_videos'])) {
    $conn->query("UPDATE videos SET views = views + 1 WHERE id = $video_id");
    $_SESSION['viewed_videos'][] = $video_id;
    $video['views']++; // increment locally to show correct count immediately
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($video['title']); ?> - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="user-container">
    <h2><?php echo htmlspecialchars($video['title']); ?></h2>
    <p><strong>Talent:</strong> <?php echo htmlspecialchars($video['talent_name']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($video['category']); ?></p>
    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($video['description'])); ?></p>

  <div class="video-wrapper">
    <video controls>
        <source src="../uploads/videos/<?php echo htmlspecialchars($video['filename']); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>

    <p><strong>Views:</strong> <?php echo (int)$video['views']; ?></p>
    <p><strong>Likes:</strong> <?php echo (int)$video['likes']; ?></p>

    <p>
        <a href="like_video.php?id=<?php echo $video_id; ?>">Like</a> |
        <a href="donate.php?id=<?php echo $video_id; ?>">Donate</a> |
        <a href="fan_dashboard.php">Back to Dashboard</a>
    </p>
</div>
</body>
</html>