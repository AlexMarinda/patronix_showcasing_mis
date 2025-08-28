<?php
include 'config.php';

$password1 = password_hash("password123", PASSWORD_DEFAULT);
$password2 = password_hash("mypassword", PASSWORD_DEFAULT);

$sql1 = "INSERT INTO talents (fullname, email, phone, password) VALUES 
    ('Alice Example', 'alice@example.com', '250700000001', '$password1')";

$sql2 = "INSERT INTO talents (fullname, email, phone, password) VALUES 
    ('Bob Example', 'bob@example.com', '250700000002', '$password2')";

if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
    echo "Sample talents inserted successfully!";
} else {
    echo "Error inserting talents: " . $conn->error;
}
?>