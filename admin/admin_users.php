
<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$limit = 10; // users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch users
$result = $conn->query("SELECT * FROM talents ORDER BY id DESC LIMIT $start, $limit");
$total_result = $conn->query("SELECT COUNT(*) as total FROM talents");
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Talented Users</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Talented Users</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Names</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['fullname']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['phone']; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id']; ?>">Edit</a> | 
                        <a href="delete_user.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
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