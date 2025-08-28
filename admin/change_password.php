<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashed)) {
        $message = "❌ Current password is incorrect!";
    } elseif ($new !== $confirm) {
        $message = "❌ New passwords do not match!";
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $update->bind_param("si", $newHash, $admin_id);
        if ($update->execute()) {
            $message = "✅ Password updated successfully.";
        } else {
            $message = "❌ Failed to update password.";
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Change Admin Password</h2>
        <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
        <br><br>

        <?php if ($message): ?>
            <p style="color: <?php echo str_starts_with($message, '✅') ? 'green' : 'red'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Current Password:</label>
            <input type="password" name="current_password" required><br>

            <label>New Password:</label>
            <input type="password" name="new_password" required><br>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required><br>

            <button type="submit">Update Password</button>
        </form>
    </div>
</body>
</html>