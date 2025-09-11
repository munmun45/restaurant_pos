<?php
// Include session management
require_once 'config/session.php';

// Logout the user
logoutUser();

// Redirect to login page
header("Location: login.php");
exit;
?>