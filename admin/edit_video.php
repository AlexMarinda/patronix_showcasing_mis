<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_videos.php");
    exit();
}

$id = intval($_GET['id']);
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $status = $conn->real_escape_string($_POST['status']);

    $conn->query("UPDATE videos SET title='$title', category='$category', status='$status' WHERE id=$id");
    $msg = "Video updated successfully!";
}

// Fetch current video data
$video = $conn->query("SELECT * FROM videos WHERE id=$id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Video</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
<div class="admin-container">
    <h2>Edit Video</h2>
    <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
    <form method="post">
        Title:<br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required><br>

        Category:<br>
        <input type="text" name="category" value="<?php echo htmlspecialchars($video['category']); ?>" required><br>

        Status:<br>
        <select name="status" required>
            <option value="pending" <?= $video['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $video['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $video['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select><br><br>

        <button type="submit">Update Video</button>
    </form>

    <br><a href="admin_videos.php">← Back to Videos</a>
</div>
</body>
</html>