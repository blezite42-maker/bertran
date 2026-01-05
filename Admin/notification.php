<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_id'])) header("Location: login.php");

$notes = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
?>
<h1>Notifications</h1>
<ul>
<?php while($row=$notes->fetch_assoc()): ?>
<li><?php echo $row['message']; ?> (<?php echo $row['created_at']; ?>)</li>
<?php endwhile; ?>
</ul>
