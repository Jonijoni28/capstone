<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug: Log the request method
file_put_contents('php://stderr', "Received Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n");

// Database Connection
$host = "localhost";
$db_name = "u641468145_nstp";
$username = "u641468145_nstp";
$password = "Nstpslsubsinfotech@2025";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    file_put_contents('php://stderr', "Database connection: Success\n");
} catch (PDOException $e) {
    file_put_contents('php://stderr', "Database connection failed: " . $e->getMessage() . "\n");
    die("Connection failed: " . $e->getMessage());
}

// Authenticate API key
function authenticate() {
    global $conn;
    $headers = getallheaders();

    // Debug: Log received headers
    file_put_contents('php://stderr', "Headers: " . print_r($headers, true));

    $api_key = $headers['Authorization'] 
        ?? $headers['api_key'] 
        ?? $headers['API_KEY'] 
        ?? $_SERVER['HTTP_API_KEY'] 
        ?? null;

    if (!$api_key) {
        file_put_contents('php://stderr', "Authentication error: API key missing\n");
        http_response_code(403);
        die(json_encode(["status" => "error", "message" => "API key required"]));
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM api_keys WHERE api_key = ?");
        $stmt->execute([$api_key]);

        if ($stmt->rowCount() == 0) {
            file_put_contents('php://stderr', "Authentication error: Invalid API key\n");
            http_response_code(403);
            die(json_encode(["status" => "error", "message" => "Invalid API key"]));
        }
    } catch (PDOException $e) {
        file_put_contents('php://stderr', "Database error during authentication: " . $e->getMessage() . "\n");
        die(json_encode(["status" => "error", "message" => "Authentication failed: " . $e->getMessage()]));
    }
}

// Process GET Request for Announcement
if (isset($_GET['announcements']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticate(); // Ensure API authentication

    try {
        // Fetch announcement for "SLSU Students"
        $stmt = $conn->prepare("SELECT title, what, audience, date, location, attire, note, announced_by, image 
                                FROM announcement ORDER BY date DESC");
        $stmt->execute();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepend base URL to each image path
        $base_url = "https://nstpslsu.com/";
        foreach ($announcements as &$announcement) {
            $announcement['image'] = $base_url . $announcement['image'];
        }

        if ($announcements) {
            echo json_encode(["status" => "success", "data" => $announcements]);
        } else {
            echo json_encode(["status" => "error", "message" => "No announcements found for SLSU Students"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(["status" => "error", "message" => "Failed to fetch announcements: " . $e->getMessage()]));
    }
    exit();
}

// Process GET Request for a Specific school_id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['school_id'])) {
    authenticate(); // Ensure API authentication
    
    $school_id = $_GET['school_id'];

    // Query 1: Get data from tbl_cwts
    $stmt1 = $conn->prepare("SELECT nstp, instructor, semester FROM tbl_cwts WHERE school_id = ?");
    $stmt1->execute([$school_id]);
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Query 2: Get data from tbl_students_grades
    $stmt2 = $conn->prepare("SELECT prelim, midterm, finals, final_grades, status FROM tbl_students_grades WHERE school_id = ?");
    $stmt2->execute([$school_id]);
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Combine results into a single JSON response
    if ($result1 || $result2) {
        echo json_encode([
            "data" => [
                "cwts" => $result1 ?: [], // Include CWTS data or an empty array if not found
                "grades" => $result2 ?: [] // Include grades data or an empty array if not found
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No data found for this student"]);
    }
    exit(); // Exit after sending the response
}




// Process GET Request for All Data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticate();

    $stmt = $conn->prepare("SELECT * FROM tbl_cwts");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit(); // Exit after sending response
}
// Process POST Request to Update 'pass' Field in tbl_cwts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update_pass'])) {
    authenticate(); // Ensure API authentication

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (empty($input['school_id']) || empty($input['pass'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required fields: school_id or pass"]);
        exit();
    }

    // Extract fields
    $school_id = $input['school_id'];
    $new_pass = $input['pass'];

    try {
        // Update 'pass' field in the database
        $stmt = $conn->prepare("UPDATE tbl_cwts SET pass = ? WHERE school_id = ?");
        $result = $stmt->execute([$new_pass, $school_id]);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update password"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
    exit();
}

// Process POST Request to Add Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['add_data'])) {
    authenticate(); // Ensure API authentication

    $input = json_decode(file_get_contents('php://input'), true);
    $required_fields = ['school_id', 'gmail', 'first_name', 'mi', 'suffix', 'last_name', 'gender', 'semester', 'pass', 'nstp', 'department', 'course'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            die(json_encode(["status" => "error", "message" => "Missing field: $field"]));
        }
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO tbl_cwts (school_id, gmail, first_name, mi, suffix, last_name, gender, semester, pass, nstp, department, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $input['school_id'],
            $input['gmail'],
            $input['first_name'],
            $input['mi'],
            $input['suffix'],
            $input['last_name'],
            $input['gender'],
            $input['semester'],
            $input['pass'],
            $input['nstp'],
            $input['department'],
            $input['course']
        ]);

        echo json_encode(["status" => "success", "message" => "Data inserted successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(["status" => "error", "message" => "Failed to insert data: " . $e->getMessage()]));
    }
    exit();
}


// Handle Invalid Request Methods
http_response_code(405);
echo json_encode([
    "status" => "error",
    "message" => "Invalid request method",
    "debug" => [
        "received_method" => $_SERVER['REQUEST_METHOD'],
        "expected_method" => "POST or GET"
    ]
]);
exit();
?>
