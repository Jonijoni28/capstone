<?php
require_once("db_conn.php");
$conn = connect_db();

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$instructor = $data['instructor'];
$studentIds = $data['studentIds'];

// Call the function to transfer students
if (transferStudentsToInstructor($instructor, $studentIds)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to transfer students.']);
}

// Function to transfer students to the selected instructor
function transferStudentsToInstructor($instructor, $studentIds) {
    global $conn; // Use the existing database connection
    $studentIdsString = implode(',', array_map('intval', $studentIds)); // Convert IDs to a comma-separated string

    // SQL query to update the instructor for the selected students
    $sql = "UPDATE tbl_cwts SET instructor = ? WHERE school_id IN ($studentIdsString)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $instructor);

    if ($stmt->execute()) {
        return true; // Success
    } else {
        return false; // Failure
    }
}
?>