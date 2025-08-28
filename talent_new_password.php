<?php
session_start();
include 'config.php';

$error = '';
$msg = '';

if (!isset($_GET['token'])) {
    die("Invalid or missing token.");
}

$token = $_GET['token'];

// Verify token validity
$stmt = $conn->prepare("SELECT id, reset_expiry FROM talents WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid token.");
}

$user = $result->fetch_assoc();
if (strtotime($user['reset_expiry']) < time()) {
    die("Token expired. Please request a new password reset.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE talents SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $update->bind_param("si", $hash, $user['id']);
        if ($update->execute()) {
            $msg = "Password updated successfully. You can now <a href='talent_login.php'>login</a>.";
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set New Password - Talent</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="form-box">
    <h2>Set New Password</h2>

    <?php if ($msg) {
        echo "<p class='success'>$msg</p>";
    } else { ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="new_password" placeholder="New Password" required><br>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
            <button type="submit">Change Password</button>
        </form>
    <?php } ?>

</div>
</body>
</html>