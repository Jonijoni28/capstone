<?php
require_once 'db_conn.php';
require_once 'audit_functions.php';
session_start();

// Check authentication
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id())) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$school_id = $data['school_id'];
$grades_id = $data['grades_id'];

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

// Get student name and existing grades before deletion
$student_query = "
    SELECT 
        c.first_name,
        c.last_name,
        g.prelim,
        g.midterm,
        g.finals,
        g.final_grades,
        g.status
    FROM tbl_cwts c
    JOIN tbl_students_grades g ON c.school_id = g.school_id
    WHERE g.grades_id = ?";

$stmt = $conn->prepare($student_query);
$stmt->bind_param('i', $grades_id);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();

if ($student_data) {
    $student_name = $student_data['first_name'] . ' ' . $student_data['last_name'];
    
    // Create description for audit log
    $description = "Deleted grades for student: $student_name ($school_id)\n";
    $description .= "Prelim: " . ($student_data['prelim'] ?? 'None') . "\n";
    $description .= "Midterm: " . ($student_data['midterm'] ?? 'None') . "\n";
    $description .= "Finals: " . ($student_data['finals'] ?? 'None') . "\n";
    $description .= "Final Grade: " . ($student_data['final_grades'] ?? 'None') . "\n";
    $description .= "Status: " . ($student_data['status'] ?? 'None');

    // Delete the grades
    $delete_stmt = $conn->prepare("DELETE FROM tbl_students_grades WHERE grades_id = ?");
    $delete_stmt->bind_param('i', $grades_id);

    if ($delete_stmt->execute()) {
        // Log the activity
        $result = logActivity(
            $full_name,
            'DELETE GRADES',
            $description,
            'Student Grades',
            $grades_id
        );

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete grades']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Grades not found']);
}

$conn->close();
?>