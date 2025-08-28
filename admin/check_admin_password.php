<?php
include '../config.php';

$username = 'admin'; // change if needed
$passwordToTest = 'admin123'; // test password

$stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $hash = $row['password'];

    if (password_verify($passwordToTest, $hash)) {
        echo "Password is correct!";
    } else {
        echo "Password is incorrect!";
    }
} else {
    echo "User not found!";
}
?>