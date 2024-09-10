<?php
require_once("db_conn.php");

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the ID to be edited
    $school_id = $_REQUEST['school_id'];

    // Retrieve other form data
    $first_name = $_POST['first_name']; // change to match your form field names
    $last_name = $_POST['last_name']; // change to match your form field names
    $gender = $_POST['gender']; // change to match your form field names
    $nstp = $_POST['nstp']; // change to match your form field names
    $department = $_POST['department']; // change to match your form field names
    $course = $_POST['course']; // change to match your form field names

    // Prepare and execute the UPDATE statement
    $statement = $conn->prepare("UPDATE tbl_cwts SET first_name=?, last_name=?, gender=?, nstp=?, department=?, course=? WHERE school_id=?");
    $statement->bind_param("sssssss", $first_name, $last_name, $gender, $nstp, $department, $course, $school_id);

    if ($statement->execute()) {
        // If update is successful
        echo "Data updated successfully.";
    } else {
        // If update fails
        http_response_code(500);
        echo "Error updating data: " . $conn->error;
    }

    // Close the connection
    $statement->close();
    $conn->close();
} else {
    // If not a PUT or POST request, return method not allowed
    http_response_code(405);
    echo "Method Not Allowed";
}