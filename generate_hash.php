<?php
$password = 'admin123';
echo password_hash($password, PASSWORD_DEFAULT);
?>



UPDATE admins 
SET password = '$2y$10$FakwMIrko82cUKHtSa9PwujaU9kVZR/k.HKqRTqSsr4ZoC9BOVqMC' 
WHERE username = 'admin';