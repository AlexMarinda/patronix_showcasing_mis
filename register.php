<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $phone, $email, $password, $role);
    $stmt->execute();
    echo "Registration successful!";
}
?>
<form method="post">
    Name: <input name="name"><br>
    Phone: <input name="phone"><br>
    Email: <input name="email"><br>
    Password: <input type="password" name="password"><br>
    Role: <select name="role"><option value='talent'>Talent</option><option value='fan'>Fan</option></select><br>
    <input type="submit" value="Register">
</form>

<!-- <?php
// include 'config.php';
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//   $name = $_POST['name'];
//   $phone = $_POST['phone'];
//   $query = "INSERT INTO users (name, phone) VALUES ('$name', '$phone')";
//   if (mysqli_query($conn, $query)) {
//     echo "User registered successfully.";
//   } else {
//     echo "Error: " . mysqli_error($conn);
//   }
// }
// ?>
<h2>Register as a Talent</h2>
<form method="post">
  Name: <input type="text" name="name" required><br>
  Phone: <input type="text" name="phone" required><br>
  <input type="submit" value="Register">
</form> -->