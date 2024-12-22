<?php
require_once 'audit_functions.php';  // Include this first!
session_start();

// Log the logout before destroying the session
if (isset($_SESSION['username'])) {
    logActivity($_SESSION['username'], 'LOGOUT', 'User logged out of the system');
}

// Unset all session variables
$_SESSION = array();

// Remove cookies
if (isset($_COOKIE['auth'])) {
    setcookie('auth', '', time() - 3600, '/');
}

if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>
