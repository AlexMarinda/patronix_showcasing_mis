<?php
session_start();
include 'config.php';

if (!isset($_SESSION['talent_id'])) {
    header("Location: talent_login.php");
    exit();
}

$talent_id = $_SESSION['talent_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);

    // Basic validation
    if (empty($title) || empty($category)) {
        $msg = "Please fill all fields.";
    } elseif (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $msg = "Please upload a video file.";
    } else {
        $allowed_types = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime'];
        $file_type = $_FILES['video']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $msg = "Unsupported video format. Allowed: mp4, avi, mpeg, mov.";
        } else {
            // Move uploaded video to uploads folder
            $uploads_dir = 'uploads/videos/';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }

            $filename = time() . '_' . basename($_FILES['video']['name']);
            $target_path = $uploads_dir . $filename;

            if (move_uploaded_file($_FILES['video']['tmp_name'], $target_path)) {
                $title = $conn->real_escape_string($title);
                $category = $conn->real_escape_string($category);
                $filename = $conn->real_escape_string($filename);

                // Insert into DB with status 'pending' and zero views
                $conn->query("INSERT INTO videos (user_id, title, category, filename, status, views, uploaded_at) VALUES ($talent_id, '$title', '$category', '$filename', 'pending', 0, NOW())");
                $msg = "Video uploaded successfully and is pending approval.";
            } else {
                $msg = "Failed to save uploaded video.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Video - Patronix MIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="form-box">
    <h2>Upload New Video</h2>
    <?php if ($msg) echo "<p class='info'>$msg</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Video Title" required><br>
        <input type="text" name="category" placeholder="Category" required><br>
        <input type="file" name="video" accept="video/*" required><br>
        <button type="submit">Upload</button>
    </form>
    <br><a href="talent_profile.php">← Back to Profile</a>
</div>
</body>
</html>