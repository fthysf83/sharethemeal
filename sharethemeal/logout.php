<?php
session_start();

// Destroy the session data
session_unset();
session_destroy();

// Redirect to the login page
header("Location: index.html");
exit();
?>
