<?php
require_once 'db_conn.php';

header('Content-Type: application/json');

if (isset($_POST['username'])) {
    $conn = connect_db();
    
    $username = trim($_POST['username']);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registration WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'taken' => $row['count'] > 0,
        'message' => $row['count'] > 0 ? 'Username already taken' : 'Username available'
    ]);
    
    $stmt->close();
    $conn->close();
}
?>