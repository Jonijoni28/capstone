<?php
require_once("db_conn.php");
require_once("audit_functions.php");
session_start();

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data for new values
    $school_id = $_GET['school_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $mi = $_POST['mi'];
    $suffix = $_POST['suffix'];
    $gender = $_POST['gender'];
    $semester = $_POST['semester'];
    $nstp = $_POST['nstp'];
    $department = $_POST['department'];
    $course = $_POST['course'];

    // Get old values before update
    $get_old = $conn->prepare("SELECT * FROM tbl_cwts WHERE school_id = ?");
    $get_old->bind_param("s", $school_id);
    $get_old->execute();
    $old_result = $get_old->get_result();
    $old_data = $old_result->fetch_assoc();

    // Prepare and execute the UPDATE statement
    $sql = "UPDATE tbl_cwts SET 
            first_name = ?, 
            last_name = ?, 
            mi = ?,
            suffix = ?,
            gender = ?, 
            semester = ?, 
            nstp = ?, 
            department = ?, 
            course = ? 
            WHERE school_id = ?";
    
    $statement = $conn->prepare($sql);
    
    if (!$statement) {
        http_response_code(500);
        echo "Prepare failed: " . $conn->error;
        exit;
    }

    $statement->bind_param("ssssssssss", 
        $first_name, 
        $last_name,
        $mi,
        $suffix, 
        $gender, 
        $semester, 
        $nstp, 
        $department, 
        $course, 
        $school_id
    );

    if ($statement->execute()) {
        if ($statement->affected_rows > 0) {
            try {
                // Get the user's full name
                $user_id = $_SESSION['user_id'];
                $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $full_name = $user['full_name'];

                // Create detailed description of changes
                $changes = array();
                if ($old_data['first_name'] !== $first_name) {
                    $changes[] = "First Name: {$old_data['first_name']} → {$first_name}";
                }
                if ($old_data['last_name'] !== $last_name) {
                    $changes[] = "Last Name: {$old_data['last_name']} → {$last_name}";
                }
                if ($old_data['mi'] !== $mi) {
                    $changes[] = "MI: {$old_data['mi']} → {$mi}";
                }
                if ($old_data['suffix'] !== $suffix) {
                    $changes[] = "Suffix: {$old_data['suffix']} → {$suffix}";
                }
                if ($old_data['gender'] !== $gender) {
                    $changes[] = "Gender: {$old_data['gender']} → {$gender}";
                }
                if ($old_data['semester'] !== $semester) {
                    $changes[] = "Semester: {$old_data['semester']} → {$semester}";
                }
                if ($old_data['nstp'] !== $nstp) {
                    $changes[] = "NSTP: {$old_data['nstp']} → {$nstp}";
                }
                if ($old_data['department'] !== $department) {
                    $changes[] = "Department: {$old_data['department']} → {$department}";
                }
                if ($old_data['course'] !== $course) {
                    $changes[] = "Course: {$old_data['course']} → {$course}";
                }

                // Create description with changes
                $description = "Updated student (ID: $school_id) - Changes made:\n" . implode("\n", $changes);
                
                $result = logActivity(
                    $full_name,  // Using full name instead of username
                    'Edit Student',
                    $description,
                    'CWTS Students Table',
                    $school_id
                );

                if ($result) {
                    echo "Success: Student updated successfully.";
                } else {
                    throw new Exception("Failed to log activity");
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo "Error logging activity: " . $e->getMessage();
            }
        } else {
            http_response_code(404);
            echo "Error: No student found with ID: $school_id";
        }
    } else {
        http_response_code(500);
        echo "Error updating student: " . $statement->error;
    }

    $statement->close();
    $get_old->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>