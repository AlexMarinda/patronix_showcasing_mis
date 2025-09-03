<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'talent') {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);

    // Validate video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        $fileType = $_FILES['video']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $message = "Unsupported video format. Allowed: mp4, webm, ogg.";
        } else {
            // Save video
            $uploadDir = '../uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = time() . '_' . basename($_FILES['video']['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
                // Insert to DB
                $stmt = $conn->prepare("INSERT INTO videos (user_id, title, category, description, filename, status, uploaded_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");

                $stmt->bind_param("issss", $user_id, $title, $category, $description, $filename);
                if ($stmt->execute()) {
                    $message = "Video uploaded successfully and pending approval.";
                } else {
                    $message = "Database error: " . $stmt->error;  // show actual DB error
                    unlink($targetPath); // delete uploaded file on error
                }
            } else {
                $message = "Failed to move uploaded video file.";
            }
        }
    } else {
        $message = "Please select a video to upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Video - Talent</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="user-container">
        <h2>Upload New Video</h2>
        <a href="talent_dashboard.php" class="btn">← Back to Dashboard</a>

        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Title:</label><br>
            <input type="text" name="title" required><br>

            <label>Category:</label><br>
            <input type="text" name="category" required placeholder="e.g. Music, Dance"><br>

            <label>Description:</label><br>
            <textarea name="description" rows="4" placeholder="Optional"></textarea><br>

            <label>Video File (mp4, webm, ogg):</label><br>
            <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" required><br><br>

            <button type="submit">Upload Video</button>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>