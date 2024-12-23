<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("db_conn.php");
require_once("audit_functions.php");

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get student details before deletion for logging
    $school_id = $_GET['school_id'];
    
    // First get student details
    $get_student = $conn->prepare("SELECT * FROM tbl_cwts WHERE school_id = ?");
    $get_student->bind_param("s", $school_id);
    $get_student->execute();
    $result = $get_student->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        // Get the user's full name
        $user_id = $_SESSION['user_id'];
        $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $full_name = $user['full_name'];

        // Prepare and execute the DELETE statement
        $statement = $conn->prepare("DELETE FROM tbl_cwts WHERE school_id = ?");
        $statement->bind_param("s", $school_id);

        if ($statement->execute()) {
            try {
                // Create detailed description for the log
                $description = "Deleted student: {$student['first_name']} {$student['last_name']} " .
                             "(ID: {$student['school_id']}) - " .
                             "Details: Gender: {$student['gender']}, " .
                             "Semester: {$student['semester']}, " .
                             "NSTP: {$student['nstp']}, " .
                             "Department: {$student['department']}, " .
                             "Course: {$student['course']}";

                $result = logActivity(
                    $full_name,
                    'Delete Student',
                    $description,
                    'CWTS Students Table',
                    $school_id
                );

                if ($result) {
                    echo "Student deleted successfully";
                } else {
                    throw new Exception("Failed to log activity");
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo "Error logging activity: " . $e->getMessage();
            }
        } else {
            http_response_code(500);
            echo "Error deleting student: " . $statement->error;
        }

        $statement->close();
    } else {
        http_response_code(404);
        echo "Student not found";
    }

    $get_student->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>