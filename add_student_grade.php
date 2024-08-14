<?
require_once ("db_conn.php");

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the ID to be edited
    $school_id = $_POST['school_id'];
    $prelim = $_POST['prelim'];
    $midterm = $_POST['midterm'];
    $finals = $_POST['finals'];

    $statement = $conn->prepare("INSERT INTO `tbl_students_grades` (`school_id`, `grades_id`, `prelim`, `midterm`, `finals`) VALUES (?, NULL, ?, ?, ?)");
    $statement->bind_param("sddd", $school_id, $prelim, $midterm, $finals);
    
    if ($statement->execute()) {
        http_response_code(200);
        echo "<script>alert(Successfully added grade!)</script>";
    } else {
        // If update fails
        http_response_code(500);
        echo "<script>alert(Error updating data: {$conn->error})</script>";
    }

    // Close the connection
    $statement->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "<script>alert(\"Wrong HTTP request used! Try again.\")</script>";
}