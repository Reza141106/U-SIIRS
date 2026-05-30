<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<h1>Welcome User</h1>

<a href="submit-report.php">Submit Report</a><br><br>
<a href="my-report.php">My Reports</a><br><br>
<a href="logout.php">Logout</a>