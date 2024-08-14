<?php
require_once ("db_conn.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $department = $_POST["department"];

    // Check for duplicates
    $conn = connect_db();
    $sql_check_duplicate = "SELECT COUNT(*) FROM tbl_cwts WHERE first_name = ? AND last_name = ? AND department = ?";
    $stmt_check_duplicate = $conn->prepare($sql_check_duplicate);
    $stmt_check_duplicate->bind_param("sss", $first_name, $last_name, $department);
    $stmt_check_duplicate->execute();
    $stmt_check_duplicate->bind_result($count);
    $stmt_check_duplicate->fetch();
    $stmt_check_duplicate->close();

    if ($count > 0) {
        // If duplicate found, return error
        http_response_code(400);
        echo "Error: Student with the same first name, last name, and department already exists";
        exit(); // Terminate script
    }

    // If no duplicate found, proceed with insertion
    $school_id = $_POST["school_id"];
    $gender = $_POST["gender"];
    $nstp = $_POST["nstp"];
    $course = $_POST["course"];

    // Insert into database
    $sql_insert = "INSERT INTO tbl_cwts (school_id, first_name, last_name, gender, nstp, department, course) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssssss", $school_id, $first_name, $last_name, $gender, $nstp, $department, $course);


    if ($stmt_insert->execute()) {
        // If insertion is successful
        http_response_code(200);
        echo "Success: Data added successfully";
    } else {
        // If insertion fails
        http_response_code(500);
        echo "Error: " . $conn->error;
    }


    $stmt_insert->close();
    $conn->close();
} else {
    echo "Invalid request";
}