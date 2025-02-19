<?php
require_once("db_conn.php");
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$semester = $_GET['semester'] ?? '1st';
$instructor = $_SESSION['username'];

$conn = connect_db();

$sql = "SELECT is_completed FROM semester_completion 
        WHERE instructor = ? AND semester = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $instructor, $semester);
$stmt->execute();
$result = $stmt->get_result();
$completed = $result->fetch_assoc();

echo json_encode([
    'completed' => $completed ? (bool)$completed['is_completed'] : false
]);
?> 