<?php
require_once 'db_conn.php';
require_once 'audit_functions.php';
session_start();

// Check if the user is logged in and is an admin
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && 
    ($_SESSION["user_type"] == "admin" || $_SESSION["user_type"] == "instructor"))) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Get the announcement ID from the request
$announcement_id = $_POST['id'] ?? null;

if ($announcement_id) {
    $conn = connect_db();

    // Get the user's full name
    $user_id = $_SESSION['user_id'];
    $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $full_name = $user['full_name'];

    // Get announcement details before deletion
    $get_announcement = $conn->prepare("SELECT * FROM announcement WHERE id = ?");
    $get_announcement->bind_param("i", $announcement_id);
    $get_announcement->execute();
    $announcement = $get_announcement->get_result()->fetch_assoc();

    // Prepare SQL Delete
    $stmt = $conn->prepare("DELETE FROM announcement WHERE id = ?");
    $stmt->bind_param("i", $announcement_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Create detailed description for audit log
        $description = "Deleted announcement:\n" .
                      "Title: {$announcement['title']}\n" .
                      "Audience: {$announcement['audience']}\n" .
                      "What: {$announcement['what']}\n" .
                      "Date: {$announcement['date']}\n" .
                      "Location: {$announcement['location']}\n" .
                      "Attire: {$announcement['attire']}\n" .
                      "Note: {$announcement['note']}\n" .
                      "Announced By: {$announcement['announced_by']}";

        // Log the activity
        $result = logActivity(
            $full_name,
            'Delete Announcement',
            $description,
            'Announcement Table',
            $announcement_id
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        exit;
    }

    // Close the statements and connection
    $get_announcement->close();
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID.']);
}
?>