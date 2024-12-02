<?php
require_once 'db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $instructorId = $data['instructorId'];
    $designation = $data['designation'];

    // Ensure instructorId is an integer
    $instructorId = intval($instructorId);

    if (isset($instructorId) && isset($designation)) {
        $conn = connect_db();

        // Check connection
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

        // Update the user_info table instead of registration
        $query = "UPDATE user_info SET designation = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param('si', $designation, $instructorId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            error_log('Execute failed: ' . htmlspecialchars($stmt->error));
            echo json_encode(['success' => false, 'error' => 'Execute failed: ' . htmlspecialchars($stmt->error)]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
}
?>