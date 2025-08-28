<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$checkStatusMessage = '';
$payoutMessage = '';

if (isset($_POST['check_status'])) {
    // Run FDI transaction status check script
    ob_start();
    include 'fdi_check_status.php';
    $checkStatusMessage = ob_get_clean();
}

if (isset($_POST['run_payout'])) {
    // Run FDI payout script
    ob_start();
    include 'fdi_payout.php';
    $payoutMessage = ob_get_clean();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>FDI Tools - Admin - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .result-box { background: #f0f0f0; border: 1px solid #ccc; padding: 15px; margin-top: 10px; white-space: pre-wrap; max-height: 300px; overflow-y: auto;}
        button { margin-right: 15px; padding: 10px 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>FDI API Tools</h2>
        <a href="admin_dashboard.php">← Back to Dashboard</a>

        <form method="POST" style="margin-top:20px;">
            <button type="submit" name="check_status">Run Transaction Status Check</button>
            <button type="submit" name="run_payout">Run Talent Payouts</button>
        </form>

        <?php if ($checkStatusMessage): ?>
            <h3>Transaction Status Check Output:</h3>
            <div class="result-box"><?php echo htmlspecialchars($checkStatusMessage); ?></div>
        <?php endif; ?>

        <?php if ($payoutMessage): ?>
            <h3>Payouts Output:</h3>
            <div class="result-box"><?php echo htmlspecialchars($payoutMessage); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>