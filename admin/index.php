<?php
include 'config.php';

if (isset($_GET['approve'])) {
  $id = $_GET['approve'];
  mysqli_query($conn, "UPDATE videos SET status='approved' WHERE id=$id");
  echo "Video approved.<br>";
}

$result = mysqli_query($conn, "SELECT * FROM videos WHERE status='pending'");
?>
<h2>Admin Panel</h2>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
  <p>
    <?php echo $row['title']; ?> – 
    <a href="?approve=<?php echo $row['id']; ?>">Approve</a>
  </p>
<?php } ?>