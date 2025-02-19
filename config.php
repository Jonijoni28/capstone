<?php
// config.php - Database Connection
$host = "localhost";
$db_name = "u641468145_nstp";
$username = "u641468145_nstp";
$password = "Nstpslsubsinfotech@2025";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// auth.php - API Key Authentication
function authenticate() {
    global $conn;
    
    if (!isset($_SERVER['HTTP_API_KEY'])) {
        http_response_code(403);
        echo json_encode(["message" => "API key required"]);
        exit();
    }
    
    $api_key = $_SERVER['HTTP_API_KEY'];
    $stmt = $conn->prepare("SELECT * FROM api_keys WHERE api_key = ?");
    $stmt->execute([$api_key]);
    
    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(["message" => "Invalid API key"]);
        exit();
    }
}

// students.php - CRUD Operations for Student Information
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticate();
    $stmt = $conn->prepare("SELECT * FROM students");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    authenticate();
    $data = json_decode(file_get_contents("php://nstpslsu.com"), true);
    $stmt = $conn->prepare("INSERT INTO students (school_id, first_name, last_name, gender, semester, nstp, department, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['school_id'], 
        $data['first_name'], 
        $data['last_name'], 
        $data['gender'], 
        $data['semester'], 
        $data['nstp'], 
        $data['department'], 
        $data['course']
    ]);
    echo json_encode(["message" => "Student added successfully"]);
}

// grades.php - CRUD Operations for Student Grades
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['school_id'])) {
    authenticate();
    $stmt = $conn->prepare("SELECT * FROM tbl_students_grades WHERE school_id = ?");
    $stmt->execute([$_GET['school_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    authenticate();
    $data = json_decode(file_get_contents("php://nstpslsu.com"), true);
    $stmt = $conn->prepare("INSERT INTO tbl_students_grades (school_id, prelim, midterm, finals, final_grades, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['school_id'], 
        $data['prelim'], 
        $data['midterm'], 
        $data['finals'], 
        $data['final_grades'], 
        $data['status']
    ]);
    echo json_encode(["message" => "Grade added successfully"]);
}

// announcements.php - CRUD Operations for Announcements
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticate();
    $stmt = $conn->prepare("SELECT * FROM announcements ORDER BY id DESC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    authenticate();
    $data = json_decode(file_get_contents("php://nstpslsu.com"), true);
    $stmt = $conn->prepare("INSERT INTO announcements (title, audience, what, date, location, attire, note, announced_by, image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['audience'],
        $data['what'],
        $data['date'],
        $data['location'],
        $data['attire'],
        $data['note'],
        $data['announced_by'],
        $data['image']
    ]);
    echo json_encode(["message" => "Announcement added successfully"]);
}