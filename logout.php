<?php
// Start the session
session_start();

// Destroy the session and all its data
session_destroy();

// Redirect the user to the login page
header("Location: index.php");
exit;
?>