<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: fan_search.php");
    exit();
}

$video_id = intval($_GET['id']);

// For simplicity, track likes using session to prevent multiple likes by same fan (no user login here)
if (!isset($_SESSION['liked_videos'])) {
    $_SESSION['liked_videos'] = [];
}

if (in_array($video_id, $_SESSION['liked_videos'])) {
    // Already liked
    $_SESSION['like_msg'] = "You have already liked this video.";
} else {
    // Increment likes in videos table
    $conn->query("UPDATE videos SET likes = COALESCE(likes, 0) + 1 WHERE id = $video_id");
    $_SESSION['liked_videos'][] = $video_id;
    $_SESSION['like_msg'] = "Thank you for liking the video!";
}

header("Location: video_view.php?id=$video_id");
exit();