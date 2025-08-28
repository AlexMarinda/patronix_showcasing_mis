<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: fan_search.php");
    exit();
}

$video_id = intval($_GET['id']);
$msg = '';
$error = '';

// Fetch video info and talent phone
$video = $conn->query("SELECT v.*, t.phone AS talent_phone FROM videos v JOIN talents t ON v.user_id = t.id WHERE v.id = $video_id AND v.status='approved'")->fetch_assoc();
if (!$video) {
    die("Video not found or not approved.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $fan_phone = $_POST['fan_phone']; // fan mobile money number

    if ($amount <= 0) {
        $error = "Enter a valid donation amount.";
    } elseif (!preg_match('/^\d{9,15}$/', $fan_phone)) {
        $error = "Enter a valid phone number.";
    } else {
        // Step 1: Initiate Pull payment from fan's mobile money using FDI API
        // (Example: Using cURL to call FDI Pull API)

        $fdi_url = "https://api.fdiservices.com/pull";  // Replace with actual URL
        $fdi_headers = [
            "Content-Type: application/json",
            "Authorization: Bearer YOUR_FDI_API_TOKEN"
        ];

        $fdi_payload = json_encode([
            "amount" => $amount,
            "customer_phone" => $fan_phone,
            "reference" => "donation_video_{$video_id}_" . time(),
            "description" => "Donation to video ID $video_id"
        ]);

        $ch = curl_init($fdi_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $fdi_headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fdi_payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $error = "FDI API request failed: $err";
        } else {
            $resp_data = json_decode($response, true);

            if (isset($resp_data['status']) && $resp_data['status'] === 'success') {
                // Save donation record with pending status
                $stmt = $conn->prepare("INSERT INTO donation_history (user_id, video_id, amount, date, status) VALUES (?, ?, ?, NOW(), 'pending')");
                $stmt->bind_param("iid", $video['user_id'], $video_id, $amount);
                $stmt->execute();

                // Step 2: Push payout to talent's phone via FDI Push API (subtract fees here)
                $fee_percent = 0.04; // 4% total fees
                $payout_amount = $amount * (1 - $fee_percent);

                $fdi_push_url = "https://api.fdiservices.com/push"; // Replace with actual URL
                $push_payload = json_encode([
                    "amount" => $payout_amount,
                    "recipient_phone" => $video['talent_phone'],
                    "reference" => "payout_video_{$video_id}_" . time(),
                    "description" => "Payout for donation video $video_id"
                ]);

                $ch2 = curl_init($fdi_push_url);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, $fdi_headers);
                curl_setopt($ch2, CURLOPT_POST, true);
                curl_setopt($ch2, CURLOPT_POSTFIELDS, $push_payload);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

                $push_response = curl_exec($ch2);
                $push_err = curl_error($ch2);
                curl_close($ch2);

                if ($push_err) {
                    $error = "FDI Push API request failed: $push_err";
                } else {
                    $push_data = json_decode($push_response, true);
                    if (isset($push_data['status']) && $push_data['status'] === 'success') {
                        $msg = "Donation successful! Thank you for supporting this talent.";
                        // Update donation record status to success
                        $donation_id = $stmt->insert_id;
                        $conn->query("UPDATE donation_history SET status='success' WHERE id = $donation_id");

                        // Optional: Update video views or donation stats here
                    } else {
                        $error = "Payout failed: " . ($push_data['message'] ?? 'Unknown error');
                    }
                }
            } else {
                $error = "Donation pull payment failed: " . ($resp_data['message'] ?? 'Unknown error');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donate to Video - Patronix MIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="form-box">
    <h2>Donate to: <?php echo htmlspecialchars($video['title']); ?></h2>

    <?php if ($msg) echo "<p class='success'>$msg</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="post">
        <label>Donation Amount:</label><br>
        <input type="number" name="amount" step="0.01" min="1" required><br>

        <label>Your Mobile Money Phone Number:</label><br>
        <input type="text" name="fan_phone" placeholder="e.g. 2507XXXXXXXX" required><br>

        <button type="submit">Donate</button>
    </form>

    <br><a href="fan_search.php">← Back to Videos</a>
</div>
</body>
</html>