<?php
session_start();
require_once "db_conn.php";

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = connect_db();
        
        // Handle verification
        if (isset($_POST['action']) && $_POST['action'] === 'verify') {
            // Get the form data
            $firstName = $_POST['first_name'] ?? '';
            $middleName = $_POST['middle_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $mobile = $_POST['mobile'] ?? '';

            // Prepare the SQL statement
            $stmt = $conn->prepare("SELECT id, registration_id FROM user_info WHERE 
                first_name = ? AND 
                middle_name = ? AND 
                last_name = ? AND 
                email = ? AND 
                mobile = ?");

            $stmt->bind_param("sssss", $firstName, $middleName, $lastName, $email, $mobile);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['verified_user_id'] = $user['id'];
                $_SESSION['verified_registration_id'] = $user['registration_id'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No account found with these details.'
                ]);
            }

            $stmt->close();
        }
        // Handle password reset
        else if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            if (!isset($_SESSION['verified_registration_id'])) {
                throw new Exception('Please verify your details first');
            }

            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate passwords match
            if ($new_password !== $confirm_password) {
                throw new Exception('Passwords do not match');
            }

            // Update password in registration table
            $stmt = $conn->prepare("UPDATE registration SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password, $_SESSION['verified_registration_id']);
            
            if ($stmt->execute()) {
                // Clear session variables
                unset($_SESSION['verified_user_id']);
                unset($_SESSION['verified_registration_id']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Password reset successful'
                ]);
            } else {
                throw new Exception('Failed to update password');
            }

            $stmt->close();
        }
        
        $conn->close();
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 