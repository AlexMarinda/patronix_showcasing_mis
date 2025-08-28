<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Handle delete (prevent deleting self)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id != $_SESSION['admin_id']) {
        $conn->query("DELETE FROM admins WHERE id = $delete_id");
    }
    header("Location: manage_admins.php");
    exit();
}

// Handle new admin creation
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $check = $conn->query("SELECT * FROM admins WHERE username='$username'");
    if ($check->num_rows > 0) {
        $msg = "Username already taken!";
    } else {
        $conn->query("INSERT INTO admins (username, password) VALUES ('$username', '$password')");
        $msg = "New admin created successfully!";
    }
}

// Fetch all admins
$result = $conn->query("SELECT * FROM admins ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Admins - Patronix</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .delete-btn { color: red; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>Manage Admins</h2>
        <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>

        <?php if ($msg): ?>
            <p class="<?php echo strpos($msg, 'successfully') ? 'success' : 'error'; ?>"><?php echo $msg; ?></p>
        <?php endif; ?>

        <h3>Create New Admin</h3>
        <form method="POST" style="margin-bottom: 20px;">
            <input type="text" name="username" placeholder="New admin username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Admin</button>
        </form>

        <h3>Existing Admins</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td>
                            <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                <a href="?delete_id=<?php echo $admin['id']; ?>" class="delete-btn" onclick="return confirm('Delete this admin?');">Delete</a>
                            <?php else: ?>
                                <i>Current Admin</i>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>