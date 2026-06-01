<?php
session_start();
include("config/database.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM reports WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);
?>

<h2>My Reports</h2>

<table border="1">
<tr>
    <th>Title</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo $row['title']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td>
        <a href="report-details.php?id=<?php echo $row['id']; ?>">View</a> |
        <a href="edit-report.php?id=<?php echo $row['id']; ?>">Edit</a>
    </td>
</tr>
<?php } ?>

</table>