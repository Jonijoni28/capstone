<?php
require_once 'audit_functions.php';  // Include this first!
session_start();

// Get the user's full name before destroying the session
$conn = connect_db();
$user_id = $_SESSION['user_id'];
$query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$full_name = $user['full_name'];

// Log the logout with full name
logActivity($full_name, 'Logout', 'User logged out of the system');

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
