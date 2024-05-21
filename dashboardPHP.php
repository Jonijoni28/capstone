<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to avoid undefined variable errors
$totalStudents = 0;
$rotcStudents = 0;
$cwtsStudents = 0;

// Query to get total students
$totalQuery = "SELECT COUNT(*) as total FROM tbl_cwts";
$totalResult = $conn->query($totalQuery);

if ($totalResult) {
    $totalStudents = $totalResult->fetch_assoc()['total'];
} else {
    echo "Error: " . $conn->error;
}

// Query to get ROTC students
$rotcQuery = "SELECT COUNT(*) as total FROM tbl_cwts WHERE nstp='ROTC'";
$rotcResult = $conn->query($rotcQuery);

if ($rotcResult) {
    $rotcStudents = $rotcResult->fetch_assoc()['total'];
} else {
    echo "Error: " . $conn->error;
}

// Query to get CWTS students
$cwtsQuery = "SELECT COUNT(*) as total FROM tbl_cwts WHERE nstp='CWTS'";
$cwtsResult = $conn->query($cwtsQuery);

if ($cwtsResult) {
    $cwtsStudents = $cwtsResult->fetch_assoc()['total'];
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>