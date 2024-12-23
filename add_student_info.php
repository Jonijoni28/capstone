<?php
require_once("db_conn.php");
require_once("audit_functions.php");
$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $school_id = $_POST['school_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $semester = $_POST['semester'];
    $nstp = $_POST['nstp'];
    $department = $_POST['department'];
    $course = $_POST['course'];

    // Prepare and execute the INSERT statement
    $statement = $conn->prepare("INSERT INTO tbl_cwts (school_id, first_name, last_name, gender, semester, nstp, department, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->bind_param("ssssssss", $school_id, $first_name, $last_name, $gender, $semester, $nstp, $department, $course);

    if ($statement->execute()) {
        // Get the user's full name
        $user_id = $_SESSION['user_id'];
        $query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $full_name = $user['full_name'];

        try {
            // Create a more detailed description including all student information
            $description = "Added new student:\n" .
                          "ID: $school_id\n" .
                          "Name: $first_name $last_name\n" .
                          "Gender: $gender\n" .
                          "Semester: $semester\n" .
                          "NSTP: $nstp\n" .
                          "Department: $department\n" .
                          "Course: $course";

            $result = logActivity(
                $full_name,
                'Add Student',
                $description,
                'CWTS Students Table',
                $school_id
            );

            if ($result) {
                echo "Success: Student added successfully.";
            } else {
                throw new Exception("Failed to log activity");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error logging activity: " . $e->getMessage();
        }
    } else {
        http_response_code(500);
        echo "Error adding student: " . $conn->error;
    }

    // Close the connection
    $statement->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>