<?php
require_once 'db_conn.php'; // Include your database connection file
session_start();

// Check if the user is logged in and is an admin
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && 
    ($_SESSION["user_type"] == "admin" || $_SESSION["user_type"] == "instructor"))) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

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
$conn = connect_db();
$stmt = $conn->prepare("INSERT INTO announcement (title, audience, what, date, location, attire, note, announced_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $title, $audience, $what, $date, $location, $attire, $note, $announced_by, $image);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>