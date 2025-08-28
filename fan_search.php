<?php
session_start();
include 'config.php';

$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

$whereClauses = [];
if ($search !== '') {
    $whereClauses[] = "title LIKE '%$search%'";
}
if ($category !== '') {
    $whereClauses[] = "category = '$category'";
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(' AND ', $whereClauses);
}

// Fetch videos with 'approved' status
$sql = "SELECT videos.*, talents.fullname FROM videos 
        LEFT JOIN talents ON videos.user_id = talents.id 
        $whereSQL AND status='approved' ORDER BY uploaded_at DESC";

if ($whereSQL === '') {
    $sql = "SELECT videos.*, talents.fullname FROM videos 
            LEFT JOIN talents ON videos.user_id = talents.id 
            WHERE status='approved' ORDER BY uploaded_at DESC";
}

$result = $conn->query($sql);

// Fetch distinct categories for filter dropdown
$catResult = $conn->query("SELECT DISTINCT category FROM videos WHERE status='approved' ORDER BY category ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Videos - Fan Area</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="search-container">
    <h2>Search Videos</h2>
    <form method="get" action="fan_search.php">
        <input type="text" name="q" placeholder="Search by video title" value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="">-- Select Category --</option>
            <?php while ($cat = $catResult->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category == $cat['category'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <hr>

    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Talent</th>
                <th>Views</th>
                <th>Actions</th>
            </tr>
            <?php while ($video = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($video['title']) ?></td>
                    <td><?= htmlspecialchars($video['category']) ?></td>
                    <td><?= htmlspecialchars($video['fullname']) ?></td>
                    <td><?= $video['views'] ?></td>
                    <td>
                        <a href="video_view.php?id=<?= $video['id'] ?>">View</a> |
                        <a href="like_video.php?id=<?= $video['id'] ?>">Like</a> |
                        <a href="donate_video.php?id=<?= $video['id'] ?>">Donate</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No videos found matching your criteria.</p>
    <?php endif; ?>
</div>
</body>
</html>