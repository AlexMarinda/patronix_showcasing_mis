
// include '../config.php';

// // FDI API endpoint and credentials
// $fdi_status_url = "https://fdi-api.example.com/transaction/status"; // replace with real URL
// $fdi_api_key = "YOUR_FDI_API_KEY"; // replace with your key

// // Fetch all donations with status 'processing'
// $result = $conn->query("SELECT id, transaction_id FROM donations WHERE status = 'processing'");

// while ($donation = $result->fetch_assoc()) {
//     $transaction_id = $donation['transaction_id'];

//     // Prepare curl to check status
//     $ch = curl_init($fdi_status_url . '?transaction_id=' . urlencode($transaction_id));
//     curl_setopt($ch, CURLOPT_HTTPHEADER, [
//         'Authorization: Bearer ' . $fdi_api_key
//     ]);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//     $response = curl_exec($ch);
//     $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);

//     if ($httpcode == 200) {
//         $data = json_decode($response, true);
//         $status = $data['status'] ?? 'pending';

//         if ($status === 'success') {
//             // Update donation status to success
//             $conn->query("UPDATE donations SET status = 'success' WHERE id = " . (int)$donation['id']);
            
//             // TODO: Add code to credit talent balance and trigger payout if needed

//         } elseif ($status === 'failed' || $status === 'error') {
//             // Update donation status to failed
//             $conn->query("UPDATE donations SET status = 'failed' WHERE id = " . (int)$donation['id']);
//         }
//     }
// }

<?php
include '../config.php';

// FDI API endpoint and credentials
$fdi_status_url = "https://fdi-api.example.com/transaction/status"; // replace with your real URL
$fdi_api_key = "YOUR_FDI_API_KEY"; // replace with your key

echo "Starting transaction status check...\n";

// Fetch all donations with status 'processing'
$result = $conn->query("SELECT id, transaction_id FROM donations WHERE status = 'processing'");

if ($result === false) {
    echo "Database query error: " . $conn->error . "\n";
    exit;
}

if ($result->num_rows === 0) {
    echo "No donations pending status update.\n";
}

while ($donation = $result->fetch_assoc()) {
    $transaction_id = $donation['transaction_id'];

    // Prepare curl to check status
    $ch = curl_init($fdi_status_url . '?transaction_id=' . urlencode($transaction_id));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $fdi_api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        $data = json_decode($response, true);
        $status = $data['status'] ?? 'pending';

        echo "Donation ID {$donation['id']} (Transaction: $transaction_id) status: $status\n";

        if ($status === 'success') {
            $update = $conn->query("UPDATE donations SET status = 'success' WHERE id = " . (int)$donation['id']);
            if ($update) {
                echo "Donation ID {$donation['id']} marked as success.\n";
                // TODO: Add code to credit talent balance and trigger payout if needed
            } else {
                echo "Failed to update donation status for ID {$donation['id']}: " . $conn->error . "\n";
            }
        } elseif ($status === 'failed' || $status === 'error') {
            $update = $conn->query("UPDATE donations SET status = 'failed' WHERE id = " . (int)$donation['id']);
            if ($update) {
                echo "Donation ID {$donation['id']} marked as failed.\n";
            } else {
                echo "Failed to update donation status for ID {$donation['id']}: " . $conn->error . "\n";
            }
        }
    } else {
        echo "Error checking donation ID {$donation['id']}: HTTP $httpcode\n";
    }
}

echo "Transaction status check complete.\n";