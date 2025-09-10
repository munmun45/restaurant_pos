<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])) {
    header("Location: login.php");
    exit;
}

// Default page is dashboard for logged in users
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include header
include_once 'includes/header.php';

// Include the requested page
if (file_exists('pages/' . $page . '.php')) {
    include_once 'pages/' . $page . '.php';
} else {
    include_once 'pages/404.php';
}

// Include footer
include_once 'includes/footer.php';
?>