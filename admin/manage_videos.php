<?php if (isset($_SESSION['message'])): ?>
    <div class="alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>
<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Search & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search) {
    $search_param = "%$search%";

    // Total count with join for talent name
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM videos v JOIN talents t ON v.user_id = t.id WHERE v.title LIKE ? OR t.fullname LIKE ?");
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Fetch videos with talent name
    $stmt = $conn->prepare("SELECT v.*, t.fullname FROM videos v JOIN talents t ON v.user_id = t.id WHERE v.title LIKE ? OR t.fullname LIKE ? ORDER BY v.uploaded_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

} else {
    $totalRows = $conn->query("SELECT COUNT(*) as total FROM videos")->fetch_assoc()['total'];
    $sql = "SELECT v.*, t.fullname FROM videos v JOIN talents t ON v.user_id = t.id ORDER BY v.uploaded_at DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}

$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Videos - Admin</title>
        <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="admin-container">
     <?php include 'header.php'; ?>
    <h2>Manage Videos</h2>
    <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
    <a href="admin_logout.php" class="logout">Logout</a>

    <form method="GET" action="manage_videos.php" style="margin-top: 20px;">
        <input type="text" name="search" placeholder="Search by video title or talent" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Talent</th>
            <th>Category</th>
            <th>Status</th>
            <th>Uploaded</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0):
            $i = $offset + 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['uploaded_at'])); ?></td>
                    <td>
                        <a href="view_video.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="approve_video.php?id=<?php echo $row['id']; ?>" class="btn green">Approve</a>
                            <a href="reject_video.php?id=<?php echo $row['id']; ?>" class="btn red">Reject</a>
                        <?php endif; ?>
                        <a href="delete_video.php?id=<?php echo $row['id']; ?>" class="btn red" onclick="return confirm('Delete this video?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr><td colspan="7">No videos found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
               class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>