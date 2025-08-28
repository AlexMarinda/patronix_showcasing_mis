<?php
include 'config.php';

$sql = "SELECT v.title, v.category, v.filename, t.fullname FROM videos v
        JOIN talents t ON v.user_id = t.id
        WHERE v.status = 'approved'
        LIMIT 5";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<h2>Approved Videos</h2><ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>" . htmlspecialchars($row['title']) . "</strong> (" . htmlspecialchars($row['category']) . ") by " . htmlspecialchars($row['fullname']) . "</li>";
}
echo "</ul>";
?>