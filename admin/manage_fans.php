<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE role='fan' And id = ?");
    // $stmt = $conn->prepare("DELETE FROM users WHERE role='fan'");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_fans.php");
        exit();
    }
}

// Fetch all fans
$result = $conn->query("SELECT * FROM users WHERE role='fan' ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Manage Talents - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<div class="admin-container">
     <?php include 'header.php'; ?>
    <h2>Manage Fans</h2>
    <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fan = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fan['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($fan['email']); ?></td>
                        <td><?php echo htmlspecialchars($fan['phone']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($fan['created_at'])); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $fan['id']; ?>" onclick="return confirm('Are you sure you want to delete this fan?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No fans found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</html>