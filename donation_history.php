<?php
include 'config.php';
$result = mysqli_query($conn, "SELECT dh.*, v.title FROM donation_history dh JOIN videos v ON dh.video_id = v.id ORDER BY dh.date DESC");
?>
<h2>Donation History</h2>
<table border="1" cellpadding="5">
  <tr>
    <th>Video Title</th>
    <th>Donor Phone</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Date</th>
  </tr>
  <?php while($row = mysqli_fetch_assoc($result)) { ?>
  <tr>
    <td><?php echo $row['title']; ?></td>
    <td><?php echo $row['user_phone']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['date']; ?></td>
  </tr>
  <?php } ?>
</table>