<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$video_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($video_id <= 0) {
    die("Invalid video ID.");
}

// To prevent multiple likes from same user, you can create a "video_likes" table, but here we keep it simple:
$conn->query("UPDATE videos SET likes = likes + 1 WHERE id = $video_id");

// Redirect back to the video page or dashboard
header("Location: view_video.php?id=$video_id");
exit();