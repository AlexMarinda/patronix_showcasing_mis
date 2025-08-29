<?php
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);

$sql = "DELETE FROM users WHERE role='talent' AND id = $id";
if ($conn->query($sql)) {
    // Success: redirect back to manage page
    header("Location: manage_talents.php?deleted=1");
    exit;
} else {
    echo "Error deleting talent: " . $conn->error;
}
?>