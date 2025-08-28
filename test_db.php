<!-- <?php
// include 'config.php';

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Database connected successfully!";
?> -->

<?php
include 'config.php';

// Example: Fetch all talents
$sql = "SELECT id, fullname, email FROM talents LIMIT 5";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<h2>Talents List (up to 5)</h2><ul>";

while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['fullname']) . " (" . htmlspecialchars($row['email']) . ")</li>";
}

echo "</ul>";
?>