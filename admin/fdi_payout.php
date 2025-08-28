<?php
include '../config.php';

// FDI API endpoint and credentials
$fdi_push_url = "https://fdi-api.example.com/push"; // replace with your real URL
$fdi_api_key = "YOUR_FDI_API_KEY"; // replace with your key

echo "Starting talent payouts...\n";

// Fetch talents with positive balance to pay (adjust your table/field names)
$payouts = $conn->query("SELECT id, phone, balance FROM users WHERE role = 'talent' AND balance > 0");

if ($payouts === false) {
    echo "Database query error: " . $conn->error . "\n";
    exit;
}

if ($payouts->num_rows === 0) {
    echo "No payouts to process.\n";
}

while ($talent = $payouts->fetch_assoc()) {
    $talent_id = $talent['id'];
    $talent_phone = $talent['phone'];
    $payout_amount = (float)$talent['balance'];
    $external_id = uniqid("payout_");

    $payload = [
        "amount" => $payout_amount,
        "phone_number" => $talent_phone,
        "external_id" => $external_id,
        "currency" => "RWF",
        "description" => "Payout for talent earnings"
    ];

    $ch = curl_init($fdi_push_url);
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

    if ($httpcode == 200) {
        $response_data = json_decode($response, true);
        echo "Payout sent to {$talent_phone} amount {$payout_amount}\n";

        // Mark payout as completed - here we reset balance to 0 (adjust per your logic)
        $update = $conn->query("UPDATE users SET balance = 0 WHERE id = $talent_id");
        if (!$update) {
            echo "Failed to reset balance for user ID $talent_id: " . $conn->error . "\n";
        }

        // Optionally insert a payout record to track payouts (not shown here)

    } else {
        echo "Error paying {$talent_phone}: HTTP $httpcode\n";
    }
}

echo "Talent payouts complete.\n";