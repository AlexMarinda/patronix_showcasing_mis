<?php
session_start();
include '../config.php'; // Correct path

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("No video ID provided.");
}

$video_id = (int) $_GET['id'];

// First, optionally delete the video file from disk (if stored)
// Then delete from DB
$stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
$stmt->bind_param("i", $video_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Video deleted successfully.";
} else {
    $_SESSION['message'] = "Error deleting video.";
}

$stmt->close();
header("Location: manage_videos.php");
exit();
?>