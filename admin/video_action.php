<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$id = (int) $_GET['id'];
$action = $_GET['action'];

if ($action == 'approve') {
    $conn->query("UPDATE videos SET status='approved' WHERE id=$id");
} elseif ($action == 'reject') {
    $conn->query("UPDATE videos SET status='rejected' WHERE id=$id");
} elseif ($action == 'delete') {
    $conn->query("DELETE FROM videos WHERE id=$id");
}

header("Location: manage_videos.php");
exit();