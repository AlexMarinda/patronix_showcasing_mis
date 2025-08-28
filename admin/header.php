<?php
// You can include session check here too if needed
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
        }
        .navbar {
            background: #333;
            padding: 10px;
            display: flex;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_talents.php">Manage Talents</a>
        <a href="manage_fans.php">Manage Fans</a>
        <a href="manage_donations.php">Donations</a>
        <a href="charts.php">Charts</a>
        <a href="logout.php" onclick="return confirm('Logout now?')">Logout</a>
    </div>
    <div class="container">