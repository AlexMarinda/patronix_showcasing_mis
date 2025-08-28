<?php
session_start();
include 'config.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM talents WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $talent = $stmt->get_result()->fetch_assoc();

    if ($talent && password_verify($password, $talent['password'])) {
        $_SESSION['talent_id'] = $talent['id'];
        $_SESSION['talent_name'] = $talent['fullname'];
        header("Location: talent_profile.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Talent Login - Patronix MIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="form-box">
        <h2>Talent Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Not registered? <a href="talent_register.php">Register here</a></p>
        <p><a href="talent_reset_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>