<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM talents WHERE id=$id");
}

header("Location: admin_users.php");
exit();