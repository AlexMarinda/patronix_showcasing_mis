<?php
include 'config.php';
$video_id = $_GET['video_id'];
$conn->query("UPDATE videos SET views = views + 1 WHERE id = $video_id");
echo "View count updated!";
?>