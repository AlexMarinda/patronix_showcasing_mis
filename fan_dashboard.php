<?php
include 'config.php';
$result = $conn->query("SELECT v.title, v.filename, v.views, d.amount FROM videos v
LEFT JOIN donations d ON v.id = d.video_id
WHERE v.published = TRUE");

while($row = $result->fetch_assoc()) {
    echo "<h3>{$row['title']}</h3>";
    echo "<video width='300' controls src='videos/{$row['filename']}'></video><br>";
    echo "Views: {$row['views']} | Donated: {$row['amount']}<br><hr>";
}
?>