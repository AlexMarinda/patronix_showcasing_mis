<?php
session_start();
include 'config.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM talents WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $error = "Email not found.";
    } else {
        // Generate a token and expiration (e.g., 1 hour)
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token and expiry in DB
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $update = $conn->prepare("UPDATE talents SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expires, $user_id);
        $update->execute();

        // Send email with reset link (simple example)
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/talent_new_password.php?token=$token";

        $subject = "Password Reset Request";
        $message = "Click this link to reset your password: $reset_link\nThis link expires in 1 hour.";
        $headers = "From: no-reply@patronixmis.com";

        if (mail($email, $subject, $message, $headers)) {
            $msg = "Password reset link sent to your email.";
        } else {
            $error = "Failed to send email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Talent</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="form-box">
    <h2>Reset Password</h2>
    <?php if ($msg) echo "<p class='info'>$msg</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your registered email" required><br>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="talent_login.php">← Back to Login</a></p>
</div>
</body>
</html>