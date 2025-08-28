<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Handle status update (approve/reject)
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    if ($action === 'approve') {
        $conn->query("UPDATE videos SET status='approved' WHERE id=$id");
    } elseif ($action === 'reject') {
        $conn->query("UPDATE videos SET status='rejected' WHERE id=$id");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM videos WHERE id=$id");
    }
    header("Location: admin_videos.php?page=$page");
    exit();
}

// Fetch videos
$total_result = $conn->query("SELECT COUNT(*) as total FROM videos");
$total_videos = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_videos / $limit);

$result = $conn->query("SELECT videos.*, talents.fullname FROM videos LEFT JOIN talents ON videos.user_id = talents.id ORDER BY uploaded_at DESC LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Videos</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
<div class="admin-container">
    <h2>Videos Management</h2>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Uploader</th>
            <th>Category</th>
            <th>Status</th>
            <th>Uploaded At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= htmlspecialchars($row['fullname']); ?></td>
                <td><?= htmlspecialchars($row['category']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><?= $row['uploaded_at']; ?></td>
                <td>
                    <a href="edit_video.php?id=<?= $row['id']; ?>">Edit</a> |
                    <a href="admin_videos.php?action=approve&id=<?= $row['id']; ?>" onclick="return confirm('Approve this video?')">Approve</a> |
                    <a href="admin_videos.php?action=reject&id=<?= $row['id']; ?>" onclick="return confirm('Reject this video?')">Reject</a> |
                    <a href="admin_videos.php?action=delete&id=<?= $row['id']; ?>" onclick="return confirm('Delete this video?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i; ?></a>
        <?php endfor; ?>
    </div>

    <br><a href="admin_dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>