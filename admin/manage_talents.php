<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Search & Pagination setup
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search) {
    $search_param = "%$search%";

    // Total count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM talents WHERE fullname LIKE ? OR phone LIKE ?");
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Fetch talents
    $stmt = $conn->prepare("SELECT * FROM talents WHERE fullname LIKE ? OR phone LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // No search, fetch all talents
    $totalRows = $conn->query("SELECT COUNT(*) as total FROM talents")->fetch_assoc()['total'];
    $result = $conn->query("SELECT * FROM talents ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Talents - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'header.php'; ?>
    <h2>Manage Talents</h2>
    <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
    <a href="admin_logout.php" class="logout">Logout</a>

    <form method="GET" action="manage_talents.php" style="margin-top: 20px;">
        <input type="text" name="search" placeholder="Search by name or phone" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Fullname</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0):
            $i = $offset + 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="view_talent.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
                        <a href="delete_talent.php?id=<?php echo $row['id']; ?>" class="btn red" onclick="return confirm('Delete this talent?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile;
        else: ?>
            <tr><td colspan="6">No talents found.</td></tr>
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