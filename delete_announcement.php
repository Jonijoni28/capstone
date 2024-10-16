<?php
require_once 'db_conn.php'; // Include your database connection file
session_start();

// Check if the user is logged in and is an admin
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && 
    ($_SESSION["user_type"] == "admin" || $_SESSION["user_type"] == "instructor"))) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Get the announcement ID from the request
// Get the announcement ID from the request
$announcement_id = $_POST['id'] ?? null; // This line should be correct

if ($announcement_id) {
    // Prepare SQL Delete
    $conn = connect_db();
    $stmt = $conn->prepare("DELETE FROM announcement WHERE id = ?");
    $stmt->bind_param("i", $announcement_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID.']);
}
?>