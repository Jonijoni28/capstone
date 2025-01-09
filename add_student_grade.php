<?php
session_start();
require_once("db_conn.php");
require_once("audit_functions.php");

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get the user's full name
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            throw new Exception("User not logged in");
        }

        // Get user's full name
        $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $full_name = $user['full_name'];

        // Retrieve the data
        $school_id = $_POST['school_id'];
        $prelim = $_POST['prelim'];
        $midterm = $_POST['midterm'];
        $finals = $_POST['finals'];

        // Get student name
        $student_query = "SELECT CONCAT(first_name, ' ', last_name) as student_name FROM tbl_cwts WHERE school_id = ?";
        $stmt = $conn->prepare($student_query);
        $stmt->bind_param('s', $school_id);
        $stmt->execute();
        $student_result = $stmt->get_result();
        $student = $student_result->fetch_assoc();
        $student_name = $student['student_name'];

        // Insert grades
        $statement = $conn->prepare("INSERT INTO `tbl_students_grades` (`school_id`, `grades_id`, `prelim`, `midterm`, `finals`) VALUES (?, NULL, ?, ?, ?)");
        $statement->bind_param("sddd", $school_id, $prelim, $midterm, $finals);
        
        if ($statement->execute()) {
            // Build the description for audit log
            $description = "Added new grades:\n" .
                         "Student: $student_name\n" .
                         "ID: $school_id\n" .
                         "Prelim: " . ($prelim ?? 'N/A') . "\n" .
                         "Midterm: " . ($midterm ?? 'N/A') . "\n" .
                         "Finals: " . ($finals ?? 'N/A');

            // Log the activity
            $result = logActivity(
                $full_name,
                'Add Grades',
                $description,
                'Student Grades',
                $school_id
            );

            if (!$result) {
                throw new Exception("Failed to log activity");
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Successfully added grade!']);
        } else {
            throw new Exception("Error updating data: " . $conn->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    // Close the connection
    $statement->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Wrong HTTP request method used! Try again.']);
}
?>