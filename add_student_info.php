<?php
require_once("db_conn.php");

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
        echo "Success: Student added successfully.";
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