<?php
include 'config.php';

// Get Alice's user ID
$result = $conn->query("SELECT id FROM talents WHERE email='alice@example.com' LIMIT 1");
if (!$result || $result->num_rows === 0) {
    die("Alice user not found.");
}
$alice = $result->fetch_assoc();

$user_id = $alice['id'];

// Insert sample videos for Alice
$sql1 = "INSERT INTO videos (user_id, title, description, category, filename, status) VALUES
    ($user_id, 'My First Talent Video', 'This is a demo video uploaded by Alice.', 'Music', 'alice_video1.mp4', 'approved')";

$sql2 = "INSERT INTO videos (user_id, title, description, category, filename, status) VALUES
    ($user_id, 'Another Performance', 'Alice\'s second video.', 'Dance', 'alice_video2.mp4', 'approved')";

if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
    echo "Sample videos inserted successfully!";
} else {
    echo "Error inserting videos: " . $conn->error;
}
?>