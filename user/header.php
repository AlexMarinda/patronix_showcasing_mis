<!-- <?php
// session_start();
// include '../config.php';
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'fan') {
//     header("Location: user_login.php");
//     exit();
// }
?> -->
<header style="background:#28a745; color:white; padding:15px; text-align:center;">
    <h1>Patronix User Dashboard</h1>
    <nav>
        <a href="fan_dashboard.php" style="color:white; margin-right:15px;">Fan Dashboard</a>
        <!-- <a href="talent_dashboard.php" style="color:white; margin-right:15px;">Talent Dashboard</a> -->
        <a href="user_logout.php" style="color:white;">Logout</a>
    </nav>
</header>
<hr>