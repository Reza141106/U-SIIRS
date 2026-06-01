<?php
include("config/database.php");

$id = $_GET['id'];

$sql = "SELECT * FROM reports WHERE id='$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<h2>Report Details</h2>

<p>Title: <?php echo $row['title']; ?></p>
<p>Description: <?php echo $row['description']; ?></p>
<p>Status: <?php echo $row['status']; ?></p>

<a href="my-report.php">Back</a>