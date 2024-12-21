<?php
require_once 'db_conn.php';
// Initialize the response array
$response = array('success' => false, 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = connect_db();

    // Check connection
    if ($conn->connect_error) {
        $response['message'] = "Connection failed: " . $conn->connect_error;
        sendJsonResponse($response);
        exit();
    }

    // Sanitize and validate input data
    $title = sanitizeInput($_POST['title']);
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_name = sanitizeInput($_POST['middle_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $suffix = sanitizeInput($_POST['suffix']);
    $sex = sanitizeInput($_POST['sex']);
    $email = sanitizeInput($_POST['email']);
    $mobile = sanitizeInput($_POST['mobile']);
    $university = sanitizeInput($_POST['university']);
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $employment_status = sanitizeInput($_POST['employment_status']);
    $area_assignment = sanitizeInput($_POST['area_assignment']);
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Handle file upload
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
        $filename = $_FILES["photo"]["name"];
        $filetype = $_FILES["photo"]["type"];
        $filesize = $_FILES["photo"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $response['message'] = "Error: Please select a valid file format.";
            sendJsonResponse($response);
            exit();
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $response['message'] = "Error: File size is larger than the allowed limit.";
            sendJsonResponse($response);
            exit();
        }

        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Generate a unique filename
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = "uploads/" . $new_filename;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_path)) {
                $photo_path = $upload_path;
            } else {
                $response['message'] = "Sorry, there was an error uploading your file.";
                sendJsonResponse($response);
                exit();
            }
        } else {
            $response['message'] = "Error: There was a problem uploading your file. Please try again.";
            sendJsonResponse($response);
            exit();
        }
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare SQL statement for registration
        $sql_registration = "INSERT INTO registration (username, password, user_type) VALUES (?, ?, ?)";
        $user_type = "instructor"; // Default user type

        $stmt_registration = $conn->prepare($sql_registration);
        $stmt_registration->bind_param("sss", $username, $password, $user_type);

        // Execute the registration statement
        if (!$stmt_registration->execute()) {
            throw new Exception("Registration failed: " . $stmt_registration->error);
        }

        // Get the last inserted ID from the registration table
        $registration_id = $conn->insert_id;
        
        // Prepare SQL statement for user_info
        $sql_user_info = "INSERT INTO user_info (registration_id, title, first_name, middle_name, last_name, suffix, sex, email, mobile, photo, university, department, designation, employment_status, area_assignment) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_user_info = $conn->prepare($sql_user_info);
        $stmt_user_info->bind_param("issssssssssssss", $registration_id, $title, $first_name, $middle_name, $last_name, $suffix, $sex, $email, $mobile, $photo_path, $university, $department, $designation, $employment_status, $area_assignment);

        // Log the SQL statement for debugging
        error_log("User Info SQL: " . $stmt_user_info->sqlstate);

        // Execute the user_info statement
        if (!$stmt_user_info->execute()) {
            throw new Exception("User info insertion failed: " . $stmt_user_info->error);
        }

        // Commit the transaction
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Registration successful. Go to Login Page.";

    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        $response['message'] = "Error: " . $e->getMessage();
    }

    // Close statements
    if (isset($stmt_user_info) && $stmt_user_info instanceof mysqli_stmt) {
        $stmt_user_info->close();
    }
    if (isset($stmt_registration) && $stmt_registration instanceof mysqli_stmt) {
        $stmt_registration->close();
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Send JSON response
sendJsonResponse($response);

/**
 * Sanitizes the input data.
 * @param string $data The input data to sanitize.
 * @return string The sanitized data.
 */
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Sends a JSON response and exits the script.
 * @param array $response The response array to send.
 */
function sendJsonResponse($response)
{
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}