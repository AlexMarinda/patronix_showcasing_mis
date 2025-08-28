<!-- <?php
// include 'config.php';
// // Simulated donation logic with 4% total fee (2% FDI, 2% system)
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $fan_id = $_POST['fan_id'];
//     $video_id = $_POST['video_id'];
//     $amount = floatval($_POST['amount']);
//     $talent_payout = $amount * 0.96;

//     // Insert donation record
//     $stmt = $conn->prepare("INSERT INTO donations (fan_id, video_id, amount) VALUES (?, ?, ?)");
//     $stmt->bind_param("iid", $fan_id, $video_id, $amount);
//     $stmt->execute();

//     echo "Donation recorded. Talent will receive: $" . number_format($talent_payout, 2);
// }
?>
<form method="post">
    Fan ID: <input name="fan_id"><br>
    Video ID: <input name="video_id"><br>
    Amount: <input name="amount"><br>
    <input type="submit" value="Donate">
</form> -->

<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $video_id = $_POST['video_id'];
  $phone = $_POST['phone'];
  $amount = $_POST['amount'];

  $fdi_api_key = "your_api_key";
  $fdi_secret = "your_secret";

  // FDI PULL API (Get money from donor)
  $pull_url = "https://api.fdi.rw/v1/momo/pull";
  $pull_data = [
    "msisdn" => $phone,
    "amount" => $amount,
    "description" => "Patronix donation"
  ];

  $headers = [
    "APIKEY: $fdi_api_key",
    "Authorization: Bearer $fdi_secret",
    "Content-Type: application/json"
  ];

  $ch = curl_init($pull_url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pull_data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $result = json_decode($response, true);
  curl_close($ch);

  if (isset($result['status']) && $result['status'] === 'success') {
    // Calculate 96%
    $net = $amount * 0.96;

    // Get user phone from video
    $get_user = mysqli_query($conn, "SELECT u.phone FROM users u JOIN videos v ON u.id = v.user_id WHERE v.id = $video_id");
    $user = mysqli_fetch_assoc($get_user);
    $talent_phone = $user['phone'];

    // FDI PUSH API (Send 96% to talent)
    $push_url = "https://api.fdi.rw/v1/momo/push";
    $push_data = [
      "msisdn" => $talent_phone,
      "amount" => $net,
      "description" => "Patronix payout"
    ];

    $ch = curl_init($push_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($push_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $push_response = curl_exec($ch);
    curl_close($ch);

    mysqli_query($conn, "INSERT INTO donation_history (video_id, user_phone, amount, status) 
                         VALUES ($video_id, '$phone', $amount, 'success')");

    echo "Donation successful. Thank you!";
  } else {
    echo "Donation failed.";
  }
} else {
  $video_id = $_GET['id'];
?>
<form method="post">
  <input type="hidden" name="video_id" value="<?php echo $video_id; ?>">
  Phone Number: <input type="text" name="phone" required><br>
  Amount: <input type="number" name="amount" required><br>
  <button type="submit">Donate</button>
</form>
<?php } ?>