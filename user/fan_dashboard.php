<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'fan') {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Fetch distinct categories for filter dropdown
$categories_res = $conn->query("SELECT DISTINCT category FROM videos WHERE status = 'approved' ORDER BY category ASC");
$categories = [];
while ($row = $categories_res->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Prepare query with search and category filter
$where = "status = 'approved'";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (title LIKE '%$search_esc%' OR description LIKE '%$search_esc%')";
}
if ($category) {
    $cat_esc = $conn->real_escape_string($category);
    $where .= " AND category = '$cat_esc'";
}

$sql = "SELECT v.*, u.fullname AS talent_name FROM videos v JOIN users u ON v.user_id = u.id WHERE $where ORDER BY uploaded_at DESC";
$videos_res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fan Dashboard - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .filter-form {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #222;
            color: white;
        }
        a.btn {
            background: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
        }
        a.btn:hover {
            background: #1e7e34;
        }
        .header-actions {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="user-container">
    <?php
include 'header.php';  // instead of session_start + checks here (done in header.php)
?>
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?> (Fan)</h1>
    <div class="header-actions">
        <a href="user_logout.php" class="btn">Logout</a>
    </div>

    <h2>Browse Videos</h2>

    <form method="GET" action="fan_dashboard.php" class="filter-form">
        <input type="text" name="search" placeholder="Search videos..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">-- All Categories --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($cat === $category) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <?php if ($videos_res && $videos_res->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Talent</th>
                <th>Category</th>
                <th>Description</th>
                <th>Likes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($video = $videos_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($video['title']); ?></td>
                <td><?php echo htmlspecialchars($video['talent_name']); ?></td>
                <td><?php echo htmlspecialchars($video['category']); ?></td>
                <td><?php echo htmlspecialchars($video['description']); ?></td>
                <td><?php echo (int)$video['likes']; ?></td>
                <td>
                    <a href="view_video.php?id=<?php echo $video['id']; ?>" class="btn">View</a>
                    <a href="like_video.php?id=<?php echo $video['id']; ?>" class="btn">Like</a>
                    <a href="donate.php?id=<?php echo $video['id']; ?>" class="btn">Donate</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No videos found.</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>