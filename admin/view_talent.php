<?php
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM talents WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo "Talent not found.";
    exit;
}

$t = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Talent - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
<h2>View Talent</h2>

<p><strong>Name:</strong> <?= htmlspecialchars($t['fullname']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($t['email']) ?></p>
<p><strong>Phone:</strong> <?= htmlspecialchars($t['phone']) ?></p>
<p><strong>Joined:</strong> <?= $t['created_at'] ?></p>

<h3>Uploaded Videos</h3>
<?php
$sql2 = "SELECT * FROM videos WHERE user_id = $id ORDER BY uploaded_at DESC";
$res2 = $conn->query($sql2);

if ($res2 && $res2->num_rows > 0) {
    echo "<ul>";
    while ($v = $res2->fetch_assoc()) {
        echo "<li><strong>" . htmlspecialchars($v['title']) . "</strong> - Views: {$v['views']} - Likes: {$v['likes']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No videos uploaded by this talent.</p>";
}
?>

<p><a href="manage_talents.php">← Back to Manage Talents</a></p>
  <?php include 'footer.php'; ?>
</body>
</html>