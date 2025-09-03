<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$video_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($video_id <= 0) {
    die("Invalid video ID.");
}

// Fetch video and talent info
$sql = $conn->prepare("SELECT v.*, u.id as talent_id, u.fullname AS talent_name FROM videos v JOIN users u ON v.user_id = u.id WHERE v.id = ? AND v.status = 'approved'");
$sql->bind_param("i", $video_id);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    die("Video not found or not approved.");
}

$video = $result->fetch_assoc();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $phone = $_POST['phone'];

    if ($amount <= 0) {
        $message = "Please enter a valid donation amount.";
    } else {
        // Save donation with 'pending' status (you can implement FDI pull/push API here)
        $stmt = $conn->prepare("INSERT INTO donation_history (fan_id, talent_id, video_id, amount, phone, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if (!$stmt) {
         die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiids", $user_id, $video['talent_id'], $video_id, $amount, $phone);
        if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
        if ($stmt->execute()) {
            $donation_id = $conn->insert_id;
            $message = "Thank you for your donation! It will be processed shortly.";
            // TODO: Add FDI payment integration here for push/pull
            // After successful insert of donation record, do FDI pull request

// 1. Define FDI API credentials and endpoint
$fdi_api_url = "https://payments-api.fdibiz.com/v2/momo/pull"; // replace with real URL
$fdi_api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIzOTA3MmNiZS0wZGVkLTRmOTktOWRiZi1kMDg2YjEzZDU4NjUiLCJpYXQiOjE3NTY4OTMyNDYsIm5iZiI6MTc1Njg5MzI0NiwianRpIjoiNWFlZWVlMTEtZTQwNi00NmI1LWJkMGEtOTAyODMyY2FjMWUxIiwiZXhwIjoxNzU2OTc5NjQ2LCJ0eXBlIjoiYWNjZXNzIiwiZnJlc2giOnRydWUsImFwaV9pZCI6IjhjNWVkMjMxLWZlNTAtNGQzNC1iOGUxLTYxYmNlZjBmZjNlMCIsImt5Y19pZCI6IjM1NjMzNjFkLTcxNjUtNDE1Yi05YzkxLTExNTZkOTMyZGYwOCIsIm5hbWUiOiJYMyJ9.CWGAm0TIDTgZjHdjERPU5PkKuBvBKyH5YiKx-1tf52s"; // replace with your key

// Prepare payload
$payload = [
    "amount" => $amount,
    "msisdn" => $phone,
    "channelId" =>  "momo-mtn-rw",
    "trxRef" => (string)$donation_id, // donation id for tracking
    "accountId" => "8C5ED231-FE50-4D34-B8E1-61BCEF0FF3E0",
    "callback" =>"http://178.79.172.122:5020/callback"
    // "currency" => "RWF",
    // "description" => "Donation to " . $video['talent_name'] . " for video " . $video['title']
];
// {
//   "trxRef": "TRX-011ww2sas23ewet-76qqwq2wa-3yTuo",
//   "channelId": "momo-mtn-rw",
//   "accountId": "8C5ED231-FE50-4D34-B8E1-61BCEF0FF3E0",
//   "msisdn": "250785571149",
//   "amount": 100,
//   "callback":"http://178.79.172.122:5020/callback"
 
// }

// Use curl to send POST request
$ch = curl_init($fdi_api_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $fdi_api_key
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 202) {
    $response_data = json_decode($response, true);

    // Save transaction ID for future status check
    $transaction_id = $response_data['data']['trxRef'] ?? null;

    if ($transaction_id) {
        // Update donation record with transaction_id and set status to processing
        $update = $conn->prepare("UPDATE donation_history SET transaction_id = ?, status = 'processing' WHERE id = ?");
        $update->bind_param("si", $transaction_id, $donation_id);
        $update->execute();
        $update->close();

        $message = "Donation initiated. Please complete the payment on your phone.";
    } else {
        $message = "Failed to initiate payment. Try again.";
    }
} else {
    $message = "FDI API error: HTTP $response";
}
//end
        } else {
            $message = "Failed to process donation. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donate to <?php echo htmlspecialchars($video['title']); ?> - Patronix MIS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* donation */
form {
  max-width: 400px;
  margin: 20px auto;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  font-family: Arial, sans-serif;
}

form label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

form input[type="number"],
form input[type="text"] {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 15px;
  border: 1.5px solid #ccc;
  border-radius: 5px;
  font-size: 15px;
  transition: border-color 0.3s ease;
}

form input[type="number"]:focus,
form input[type="text"]:focus {
  border-color: #4CAF50; /* nice green highlight */
  outline: none;
}

form button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  font-size: 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  width: 100%;
}

form button:hover {
  background-color: #45a049;
}
</style>
</head>
<body>
<div class="user-container">
        <?php
include 'header.php';  // instead of session_start + checks here (done in header.php)
?>
    <h2>Donate to "<?php echo htmlspecialchars($video['title']); ?>"</h2>
    <p><strong>Talent:</strong> <?php echo htmlspecialchars($video['talent_name']); ?></p>
    <a href="view_video.php?id=<?php echo $video_id; ?>" class="btn">← View Video again</a>

<?php if ($message): ?>
    <div class="alert <?php echo (stripos($message, 'fail') !== false || stripos($message, 'error') !== false) ? 'alert-error' : 'alert-success'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

    <form method="POST">
        <label>Donation Amount (RWF):</label><br>
        <input type="number" name="amount" min="1" step="any" required><br>

        <label>Your Phone Number:</label><br>
        <input type="text" name="phone" placeholder="e.g. 0788XXXXXX" required><br><br>

        <button type="submit">Donate</button>
    </form>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
