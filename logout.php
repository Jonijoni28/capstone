

<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Remove the authentication cookie
if (isset($_COOKIE['auth'])) {
    setcookie('auth', '', time() - 3600, '/'); // Expire the cookie
}

if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/'); // Expire the PHP session cookie
}

// Redirect to the login page after logout
header("Location: index.php");
exit();

require_once 'audit_functions.php';

// Make sure to log before destroying the session
if (isset($_SESSION['user_id'])) {
    logLogout($_SESSION['user_id']);
}




session_destroy();
header('Location: faculty.php');
exit();
?>
