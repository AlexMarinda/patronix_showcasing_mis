<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Video ID not provided.";
    exit();
}

$video_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT v.*, t.fullname FROM videos v JOIN talents t ON v.user_id = t.id WHERE v.id = ?");
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Video not found.";
    exit();
}

$video = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Video - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'header.php'; ?>
    <h2>View Video Details</h2>
    <a href="manage_videos.php" class="btn">← Back to Videos</a>

    <div class="video-box">
        <h3><?php echo htmlspecialchars($video['title']); ?></h3>
        <p><strong>Talent:</strong> <?php echo htmlspecialchars($video['fullname']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($video['category']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($video['status']); ?></p>
        <p><strong>Views:</strong> <?php echo $video['views']; ?></p>
        <p><strong>Likes:</strong> <?php echo $video['likes']; ?></p>
        <p><strong>Uploaded At:</strong> <?php echo $video['uploaded_at']; ?></p>

        <video width="600" controls>
            <source src="../uploads/<?php echo $video['filename']; ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>