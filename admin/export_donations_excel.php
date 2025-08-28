<?php
include 'config.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: menu/admin/admin_login.php");
    exit();
}

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=donations_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");
fputcsv($output, ['Date', 'User', 'Category', 'Amount']);

$query = "SELECT dh.date, u.fullname AS user_name, v.category, dh.amount 
          FROM donation_history dh
          JOIN talents u ON dh.user_id = u.id
          JOIN videos v ON dh.video_id = v.id
          ORDER BY dh.date DESC";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['date'], $row['user_name'], $row['category'], number_format($row['amount'], 2)]);
}

fclose($output);
exit();