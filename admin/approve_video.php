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

// Update video status to 'approved'
$stmt = $conn->prepare("UPDATE videos SET status = 'approved' WHERE id = ?");
$stmt->bind_param("i", $video_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Video approved successfully.";
} else {
    $_SESSION['message'] = "Error approving video.";
}
$stmt->close();

header("Location: manage_videos.php");
exit();
?>