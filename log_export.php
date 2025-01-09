<?php
require_once 'db_conn.php';
require_once 'audit_functions.php';
session_start();

// Check if user is logged in
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id())) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get the JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Get the user's full name
$user_id = $_SESSION['user_id'];
$conn = connect_db();
$query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$full_name = $user['full_name'];

// Create the log entry
$action = 'Export to ' . $data['type'];
$description = $data['description'];

// Log the activity
$result = logActivity(
    $full_name,
    $action,
    $description,
    'Student Grades',
    null
);

// Send response
header('Content-Type: application/json');
if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to log activity']);
}

$conn->close();
?>