<?php
require_once 'db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $instructorId = $data['instructorId'];
    $userType = $data['userType'];

    // Debugging: Log incoming data
    error_log("Instructor ID: $instructorId, User Type: $userType");

    // Ensure instructorId is an integer
    $instructorId = intval($instructorId);

    if (isset($instructorId) && isset($userType) && in_array($userType, ['admin', 'instructor'])) {
        $conn = connect_db();

        // Check connection
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }

        $query = "UPDATE registration SET user_type = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            error_log('Prepare failed: ' . htmlspecialchars($conn->error));
            echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param('si', $userType, $instructorId);

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