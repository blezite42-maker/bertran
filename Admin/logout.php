<?php
session_start();
session_unset(); // remove all session variables
session_destroy(); // destroy session

// Redirect back to login
header("Location: login.php");
exit();
?>
