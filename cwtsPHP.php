<?php
// Include the database connection file
require_once 'db_conn.php';

// Use the connect_db function to establish a connection
$conn = connect_db();

// CRUD Operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["action"] == "add") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $sql = "INSERT INTO your_table_name (name, email) VALUES ('$name', '$email')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif ($_POST["action"] == "update") {
        $school_id = $_POST["id"];
        $name = $_POST["name"];
        $email = $_POST["email"];
        $sql = "UPDATE your_table_name SET name='$name', email='$email' WHERE id=$school_id";
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } elseif ($_POST["action"] == "delete") {
        $school_id = $_POST["id"];
        $sql = "DELETE FROM your_table_name WHERE id=$school_id";
        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}
?>
