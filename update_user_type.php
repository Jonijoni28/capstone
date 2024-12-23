<?php
require_once 'db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $instructorId = $data['instructorId'];
    $designation = $data['designation'];

    // Ensure instructorId is an integer
    $instructorId = intval($instructorId);

    if (isset($instructorId) && isset($designation)) {
        $conn = connect_db();

        // Check connection
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

// First, get the admin's name (the person making the change)
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT CONCAT(first_name, ' ', last_name) as admin_name FROM user_info WHERE id = ?";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param('i', $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_name = $admin_result->fetch_assoc()['admin_name'];

// Get the instructor's name
$instructor_query = "SELECT CONCAT(first_name, ' ', last_name) as instructor_name FROM user_info WHERE id = ?";
$instructor_stmt = $conn->prepare($instructor_query);
$instructor_stmt->bind_param('i', $instructorId);
$instructor_stmt->execute();
$instructor_result = $instructor_stmt->get_result();
$instructor_name = $instructor_result->fetch_assoc()['instructor_name'];

// Update the user_info table
$query = "UPDATE user_info SET designation = ? WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    exit;
}

// Bind parameters
$stmt->bind_param('si', $designation, $instructorId);

if ($stmt->execute()) {
    // Create audit log entry
    $action = "Change User Type";
    $description = "$admin_name changed user type of $instructor_name to $designation";
    
    // Insert into audit_log table
    $audit_query = "INSERT INTO audit_log (User_Account, Actions, Description) VALUES (?, ?, ?)";
    $audit_stmt = $conn->prepare($audit_query);
    $audit_stmt->bind_param('sss', $admin_name, $action, $description);
    $audit_stmt->execute();

    echo json_encode(['success' => true]);
} else {
    error_log('Execute failed: ' . htmlspecialchars($stmt->error));
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . htmlspecialchars($stmt->error)]);
}

$stmt->close();
$admin_stmt->close();
$instructor_stmt->close();
$conn->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
}
?>