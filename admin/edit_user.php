<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_users.php");
    exit();
}

$id = intval($_GET['id']);
$msg = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $conn->query("UPDATE talents SET fullname='$fullname', email='$email', phone='$phone' WHERE id=$id");
    $msg = "User updated successfully!";
}

// Fetch current user data
$user = $conn->query("SELECT * FROM talents WHERE id=$id")->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Edit User</h2>
        <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
        <form method="post">
            Full Name:<br>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required><br>
            Email:<br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
            Phone:<br>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br>
            <button type="submit">Update</button>
        </form>
        <br><a href="admin_users.php">← Back to Users</a>
    </div>
</body>
</html>