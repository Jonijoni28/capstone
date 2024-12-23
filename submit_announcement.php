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

// Get form data
$title = $_POST['title'];
$audience = $_POST['audience'];
$what = $_POST['what'];
$date = $_POST['date'];
$location = $_POST['location'];
$attire = $_POST['attire'];
$note = $_POST['note'];
$announced_by = $_POST['announced_by'];
$image = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $image = 'uploads/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image);
}

// Prepare SQL Insert
$stmt = $conn->prepare("INSERT INTO announcement (title, audience, what, date, location, attire, note, announced_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $title, $audience, $what, $date, $location, $attire, $note, $announced_by, $image);

// Execute the statement
if ($stmt->execute()) {
    // Create detailed description for audit log
    $description = "Posted new announcement:\n" .
                  "Title: $title\n" .
                  "Audience: $audience\n" .
                  "What: $what\n" .
                  "Date: $date\n" .
                  "Location: $location\n" .
                  "Attire: $attire\n" .
                  "Note: $note\n" .
                  "Announced By: $announced_by";

    // Log the activity
    $result = logActivity(
        $full_name,
        'Post Announcement',
        $description,
        'Announcement Table',
        $conn->insert_id
    );

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Announcement added successfully']);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    exit;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>