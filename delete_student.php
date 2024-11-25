<?php
require_once ("db_conn.php");

$conn = connect_db();

/**
 * Deletes a student from the database.
 * Expects the student ID via GET request parameter 'id'.
 */

// Check if it's a DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the ID to be deleted
    $school_id = $_GET['school_id'];

    // Prepare and execute the DELETE statement
    $statement = $conn->prepare("DELETE FROM tbl_cwts WHERE school_id = ?");
    $statement->bind_param("s", $school_id);

    if ($statement->execute()) {
        // If deletion is successful
        echo "Row deleted successfully";
    } else {
        // If deletion fails
        http_response_code(500);
        echo "Error deleting row";
    }

    // Close the connection
    $statement->close();
    $conn->close();
} else {
    // If not a DELETE request, return method not allowed
    http_response_code(405);
    echo "Method Not Allowed";
}


require_once 'db_conn.php';
require_once 'audit_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connect_db();
    $user_id = $_SESSION['user_id'];
    
    // Your existing delete code
    $stmt = $conn->prepare("DELETE FROM your_table WHERE id = ?");
    if ($stmt->execute([$_POST['student_id']])) {
        // Log the delete action
        logDelete(
            $user_id,
            'students',
            $_POST['student_id'],
            "Deleted student record"
        );
        echo "success";
    } else {
        echo "error";
    }
}
?>