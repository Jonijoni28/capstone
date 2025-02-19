<?php
    require_once("db_conn.php");
    require_once("audit_functions.php");

    // At the top of your file after session_start()
    if (isset($_SESSION['username'])) {
        $current_instructor = $_SESSION['username'];
    } else {
        // Redirect to login if not logged in
        header("Location: faculty.php");
        exit();
    }

    // Add this function near the top of the file after the database connection
    function getInstructorFullName($conn, $username) {
        // First get the user_id from the session
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return $username; // Fallback to username if no user_id
        }

        // Get the full name using user_id instead of username
        $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM user_info WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user['full_name'] ?? $username; // Fallback to username if name not found
    }

    // Handle grade deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_grade') {
        header('Content-Type: application/json');
        
        try {
            $gradesId = isset($_POST['grades_id']) ? intval($_POST['grades_id']) : 0;
            $semester = isset($_POST['semester']) ? $_POST['semester'] : '1st'; // Add semester parameter
            $schoolId = isset($_POST['school_id']) ? $_POST['school_id'] : '';
            
            if ($gradesId <= 0 && empty($schoolId)) {
                throw new Exception('Invalid grades ID or school ID');
            }

            // Connect to database
            $conn = connect_db();

            // First, get the student information for logging
            $student_query = "
                SELECT 
                    c.first_name,
                    c.last_name,
                    c.school_id,
                    g.prelim,
                    g.midterm,
                    g.finals,
                    g.final_grades,
                    g.status
                FROM tbl_cwts c
                LEFT JOIN tbl_students_grades g ON c.school_id = g.school_id 
                WHERE (g.grades_id = ? OR c.school_id = ?) AND g.semester = ?";

            $stmt = $conn->prepare($student_query);
            $stmt->bind_param('iss', $gradesId, $schoolId, $semester);
            $stmt->execute();
            $result = $stmt->get_result();
            $student_data = $result->fetch_assoc();

            if (!$student_data) {
                throw new Exception('Student record not found');
            }

            // Update query to set ALL grade-related fields to NULL for the specific semester
            $sql = "UPDATE tbl_students_grades 
                    SET prelim = NULL, 
                        midterm = NULL, 
                        finals = NULL, 
                        final_grades = NULL, 
                        status = NULL 
                    WHERE school_id = ? AND semester = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $student_data['school_id'], $semester);
            
            if ($stmt->execute()) {
                // Get instructor's full name
                $instructor_full_name = getInstructorFullName($conn, $_SESSION['username']);

                // Log the activity with full name
                $description = "Deleted {$semester} semester grades for student: {$student_data['first_name']} {$student_data['last_name']} ({$student_data['school_id']})";
                
                logActivity(
                    $instructor_full_name, // Use full name instead of username
                    'Delete Grades',
                    $description,
                    'Student Grades',
                    $gradesId
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Grades deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete grades');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }





    // Check if user is logged in as instructor
    if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && 
        isset($_SESSION['user_type']) && $_SESSION["user_type"] == "instructor" && 
        isset($_SESSION['username']))) {
        // If no valid session or missing username, redirect to login page
        header('Location: faculty.php');
        exit();
    }

    $instructor = $_SESSION['username'];

    // Data processing logic 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
        header('Content-Type: application/json'); 
        
        try {
            $school_id = isset($_POST['school_id']) ? $_POST['school_id'] : '';
            $semester = isset($_POST['semester']) ? $_POST['semester'] : '1st';
            
            // Define valid grades
            $validGrades = [1.00, 1.25, 1.50, 1.75, 2.00, 2.25, 2.50, 2.75, 3.00, 4.00, 5.00];
            
            // Get and validate the grades
            $prelim = null;
            if (isset($_POST['prelim']) && $_POST['prelim'] !== '') {
                $prelimValue = floatval($_POST['prelim']);
                if (!in_array($prelimValue, $validGrades)) {
                    throw new Exception('Invalid prelim grade value');
                }
                $prelim = $prelimValue;
            }
            
            $midterm = null;
            if (isset($_POST['midterm']) && $_POST['midterm'] !== '') {
                $midtermValue = floatval($_POST['midterm']);
                if (!in_array($midtermValue, $validGrades)) {
                    throw new Exception('Invalid midterm grade value');
                }
                $midterm = $midtermValue;
            }
            
            $finals = null;
            if (isset($_POST['finals']) && $_POST['finals'] !== '') {
                $finalsValue = floatval($_POST['finals']);
                if (!in_array($finalsValue, $validGrades)) {
                    throw new Exception('Invalid finals grade value');
                }
                $finals = $finalsValue;
            }
            
            // Get the status from POST data
            $status = isset($_POST['status']) ? $_POST['status'] : null;

            // Validate inputs
            if (empty($school_id)) {
                throw new Exception('School ID is required');
            }

            // Calculate final grade if all components are present
            $finalGrades = null;
            if ($prelim !== null && $midterm !== null && $finals !== null) {
                $finalGrades = ($prelim + $midterm + $finals) / 3;
                $finalGrades = roundToNearestGrade($finalGrades);
                
                // Always update status to PASSED/FAILED if all grades are present
                // This will override any existing status including both INC and DROP
                $status = ($finalGrades >= 1 && $finalGrades <= 3) ? 'PASSED' : 'FAILED';
            }

            // If status is explicitly set to INC or DROP and any grade is missing, keep that status
            if (isset($_POST['status']) && ($_POST['status'] === 'INC' || $_POST['status'] === 'DROP') && 
                ($prelim === null || $midterm === null || $finals === null)) {
                $status = $_POST['status'];
            }

            $conn = connect_db();

            // Get student name for audit log
            $student_query = "SELECT CONCAT(first_name, ' ', last_name) as student_name 
                             FROM tbl_cwts WHERE school_id = ?";
            $stmt = $conn->prepare($student_query);
            $stmt->bind_param("s", $school_id);
            $stmt->execute();
            $student_result = $stmt->get_result();
            $student = $student_result->fetch_assoc();
            $student_name = $student['student_name'];

            // Check if a grade record exists for this semester
            $checkStmt = $conn->prepare("SELECT grades_id FROM tbl_students_grades 
                WHERE school_id = ? AND semester = ?");
            $checkStmt->bind_param("ss", $school_id, $semester);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            $isUpdate = $result->num_rows > 0;
            
            if ($isUpdate) {
                // Update existing record
                $sql = "UPDATE tbl_students_grades SET 
                        prelim = ?, 
                        midterm = ?, 
                        finals = ?, 
                        final_grades = ?, 
                        status = ? 
                        WHERE school_id = ? AND semester = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ddddsss", 
                    $prelim, 
                    $midterm, 
                    $finals, 
                    $finalGrades, 
                    $status, 
                    $school_id, 
                    $semester
                );
            } else {
                // Insert new record
                $sql = "INSERT INTO tbl_students_grades 
                        (school_id, semester, prelim, midterm, finals, final_grades, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdddds", 
                    $school_id,
                    $semester,
                    $prelim,
                    $midterm,
                    $finals,
                    $finalGrades,
                    $status
                );
            }

            if ($stmt->execute()) {
                // Get instructor's full name
                $instructor_full_name = getInstructorFullName($conn, $_SESSION['username']);

                // Build audit log description
                $action = $isUpdate ? 'Update Grades' : 'Add Grades';
                $description = "{$action} for {$semester} semester:\n" .
                             "Student: {$student_name}\n" .
                             "ID: {$school_id}\n" .
                             "Prelim: " . ($prelim ?? 'N/A') . "\n" .
                             "Midterm: " . ($midterm ?? 'N/A') . "\n" .
                             "Finals: " . ($finals ?? 'N/A') . "\n" .
                             "Final Grade: " . ($finalGrades ?? 'N/A') . "\n" .
                             "Status: " . ($status ?? 'N/A');

                // Log the activity with full name
                logActivity(
                    $instructor_full_name, // Use full name instead of username
                    $action,
                    $description,
                    'Student Grades',
                    $school_id
                );

                echo json_encode([
                    'success' => true,
                    'final_grades' => $finalGrades,
                    'status' => $status,
                    'message' => 'Grades saved successfully'
                ]);
            } else {
                throw new Exception('Failed to save grades: ' . $conn->error);
            }

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }




    // If it's not a POST request, log this information
    error_log("Received non-POST request: " . $_SERVER['REQUEST_METHOD']);

    // Fetch student data for displaying in the table
    $conn = connect_db();
    // Near the top of the file, update the query that fetches students
    $instructor = $_SESSION['username']; // Assuming username is stored in session
    $selectedSemester = $_GET['semester'] ?? '1st';
    $sql = "SELECT 
        c.school_id, 
        c.last_name,
        c.first_name, 
        c.mi,
        c.suffix,
        c.gender,
        ? as semester,
        c.nstp,
        c.department,
        c.course,
        COALESCE(g.grades_id, 0) AS grades_id,
        g.prelim,
        g.midterm,
        g.finals,
        g.final_grades,
        g.status
    FROM 
        tbl_cwts c
    LEFT JOIN 
        tbl_students_grades g ON c.school_id = g.school_id AND g.semester = ?
    WHERE 
        c.instructor = ?
    ORDER BY 
        c.last_name ASC, c.first_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", 
        $selectedSemester,
        $selectedSemester,
        $_SESSION['username']
    );
    $stmt->execute();
    $results = $stmt->get_result();



    // Function to round final grades to specified values
    function roundToNearestGrade($value) {
        $validGrades = [1.000, 1.250, 1.500, 1.750, 2.000, 2.250, 2.500, 2.750, 3.000, 4.000, 5.000];
        $closestGrade = $validGrades[0];

        foreach ($validGrades as $grade) {
            if (abs($grade - $value) < abs($closestGrade - $value)) {
                $closestGrade = $grade;
            }
        }

        return $closestGrade;
    }

    // Add this function after require_once statements and before any HTML
    function checkAllStudentsHaveStatus($conn, $instructor, $semester) {
        $sql = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN g.status IS NOT NULL AND g.status != '' THEN 1 ELSE 0 END) as with_status
                FROM tbl_cwts c
                LEFT JOIN tbl_students_grades g ON c.school_id = g.school_id AND g.semester = ?
                WHERE c.instructor = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $semester, $instructor);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        return $data['total'] > 0 && $data['total'] == $data['with_status'];
    }

    ?>

    <?php
    require_once 'db_conn.php';


    // Check if the session ID stored in the cookie matches the current session
    if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "instructor")) {
        // If no valid session, redirect to login page
        header('Location: faculty.php');
        exit();
    }

    $conn = connect_db();
    $user_id = $_SESSION['user_id'] ?? null;
    ?>



    <?php
    // ... existing PHP code ...

    // Fetch distinct School IDs and Semesters for the filters
    // First, get the instructor's ID or identifier from the session
    // Add these queries to get filter options
    $instructor = $_SESSION['username']; // or however you store the current instructor

    $schoolIds = $conn->query("SELECT DISTINCT SUBSTRING(school_id, 1, 3) AS school_prefix 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $semesters = $conn->query("SELECT DISTINCT semester 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $genders = $conn->query("SELECT DISTINCT gender 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $nstps = $conn->query("SELECT DISTINCT nstp 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $colleges = $conn->query("SELECT DISTINCT department 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $programs = $conn->query("SELECT DISTINCT course 
        FROM tbl_cwts WHERE instructor = '$instructor'");

    $statuses = $conn->query("SELECT DISTINCT g.status 
        FROM tbl_cwts c 
        JOIN tbl_students_grades g ON c.school_id = g.school_id 
        WHERE c.instructor = '$instructor'");

    ?>



    <!DOCTYPE html>
    <html lang="en">

    <head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="slsulogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="cwtsStud.css">
    <style>
    + /* Table size adjustments */
    + #editableTable {
    +     width: 98%;
    +     margin: 20px auto;
    +     border-collapse: collapse;
    +     font-size: 14px;
    + }
    + 
    + /* Column width specifications */
    + #editableTable th:nth-child(1), #editableTable td:nth-child(1) { width: 100px; }  /* School ID */
    + #editableTable th:nth-child(2), #editableTable td:nth-child(2) { width: 120px; } /* First Name */
    + #editableTable th:nth-child(3), #editableTable td:nth-child(3) { width: 120px; } /* Last Name */
    + #editableTable th:nth-child(4), #editableTable td:nth-child(4) { width: 50px; }   /* MI */
    + #editableTable th:nth-child(5), #editableTable td:nth-child(5) { width: 60px; }   /* Suffix */
    + #editableTable th:nth-child(6), #editableTable td:nth-child(6) { width: 80px; }   /* Gender */
    + #editableTable th:nth-child(7), #editableTable td:nth-child(7) { width: 100px; }   /* Semester */
    + #editableTable th:nth-child(8), #editableTable td:nth-child(8) { width: 80px; }   /* NSTP */
    + #editableTable th:nth-child(9), #editableTable td:nth-child(9) { width: 120px; }  /* Department */
    #editableTable th:nth-child(10), #editableTable td:nth-child(10) { width: 150px; } /* Course */
    + #editableTable th:nth-child(11), #editableTable td:nth-child(11) { width: 80px; } /* Prelims */
    + #editableTable th:nth-child(12), #editableTable td:nth-child(12) { width: 80px; } /* Midterms */
    + #editableTable th:nth-child(13), #editableTable td:nth-child(13) { width: 80px; } /* Finals */
    #editableTable th:nth-child(14), #editableTable td:nth-child(14) { width: 80px; } /* Final Grades */
    + #editableTable th:nth-child(15), #editableTable td:nth-child(15) { width: 80px; } /* Status */
    #editableTable th:nth-child(16), #editableTable td:nth-child(16) { width: 200px; } /* Actions - reduced width */
    + 
    + /* Table cell padding and alignment */
    + #editableTable th, #editableTable td {
    +     padding: 8px;
    +     text-align: left;
    +     border: 1px solid #ddd;
    + }
    + 
    + /* Table header styling */
    + #editableTable thead th {
    +     background-color: #f5f5f5;
    +     font-weight: bold;
    +     text-align: center;
    + }
    + 
    + /* Table row hover effect */
    + #editableTable tbody tr:hover {
    +     background-color: #f9f9f9;
    + }
    + 
    + /* Action buttons container */
    + #editableTable td:last-child {
    +     text-align: center;
    +     white-space: nowrap;
    + }

    /* Your existing styles... */
    </style>
    <style>
    /* Add these styles to your existing CSS */
    .actions-cell {
        text-align: center;
        min-width: 100px;
    }

    .edit-btn, .delete-btn {
        padding: 5px 10px;
        margin: 0 2px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .edit-btn {
        background-color: #4CAF50;
        color: white;
    }

    .delete-btn {
        background-color: #f44336;
        color: white;
    }

    /* Disabled state styling */
    .edit-btn:disabled, .delete-btn:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    </style>
    <style>
    /* Add this to your existing styles */
    #editableTable th {
        text-align: center !important;
    }

    #editableTable td {
        text-align: center !important;
    }
    </style>
    </head>

    <body>

    <!-- Add this before the table -->

    <style>
    .semester-selector {
        margin: 20px;
        padding: 10px;
        background-color: #f5f5f5;
        border-radius: 5px;
        display: inline-block;
    }

    .semester-selector select {
        padding: 5px;
        margin-left: 10px;
        border: 1px solid #096c37;
        border-radius: 4px;
    }
    
    .semester-controls {
    position: absolute;
    top: 270px;
    left: 38px;
}
    </style>

    <?php
    $currentSemester = $_GET['semester'] ?? '1st';
    $allHaveStatus = checkAllStudentsHaveStatus($conn, $_SESSION['username'], $currentSemester);
    ?>

    <!-- Add this before your table -->
    <div class="semester-controls">
        <button id="finishGradesBtn" 
                class="finish-grades-btn" 
                <?php echo (!$allHaveStatus ? 'disabled' : ''); ?>
                onclick="finishSemesterGrades('<?php echo $currentSemester; ?>')">
            Submit <?php echo $currentSemester; ?> Semester Grades
        </button>
    </div>

    <style>
    .finish-grades-btn {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin: 10px 0;
        font-size: 14px;
    }

    .finish-grades-btn:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    </style>

    <!-- Table of students with grades -->
    <table id="editableTable" class="table">
        <thead>
            <tr>
                <th>School ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>MI</th>
                <th>Suffix</th>
                <th>Gender</th>
                <th>Semester</th>
                <th>NSTP</th>
                <th>College</th>
                <th>Program</th>
                <th>Prelims</th>
                <th>Midterms</th>
                <th>Finals</th>
                <th>Final Grades</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
            if ($results->num_rows > 0) {
                while ($rows = $results->fetch_assoc()) {
                    echo "<tr data-grades-id='" . htmlspecialchars($rows["grades_id"]) . "' 
                             data-school-id='" . htmlspecialchars($rows["school_id"]) . "'>";
                    echo "<td style='text-align: center;'>{$rows["school_id"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["last_name"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["first_name"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["mi"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["suffix"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["gender"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["semester"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["nstp"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["department"]}</td>";
                    echo "<td style='text-align: center;'>{$rows["course"]}</td>";
                    echo "<td class='prelim'>" . ($rows["prelim"] !== null ? $rows["prelim"] : "---") . "</td>";
                    echo "<td class='midterm'>" . ($rows["midterm"] !== null ? $rows["midterm"] : "---") . "</td>";
                    echo "<td class='finals'>" . ($rows["finals"] !== null ? $rows["finals"] : "---") . "</td>";
                    echo "<td class='final_grades'>" . ($rows["final_grades"] !== null ? number_format($rows["final_grades"], 2) : "---") . "</td>";
                    echo "<td class='status'>" . (!empty($rows["status"]) ? $rows["status"] : "---") . "</td>";
                    echo "<td style='text-align: center;'>";
                    echo "<button type='button' class='editButton' onclick='editGradesInfo(this)'>";
                    echo "<i class='fa-solid fa-pen-to-square'></i>";
                    echo "</button>";
                    echo "<button type='button' class='deleteButton' onclick='openDeleteModal(this)'>";
                    echo "<i class='fa-solid fa-trash'></i>";
                    echo "</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr>";
                echo "<td colspan='16' style='text-align: center; color: red; padding: 20px;'><b>No Students Assigned</b></td>";
                echo "</tr>";
            }
            ?>
            <tr id="noResultsRow" style="display: none;">
                <td colspan="16" style="text-align: center; color: red; padding: 20px;"><b>No Results Found</b></td>
            </tr>
        </tbody>
    </table>

    <input type="checkbox" id="check">
    <label for="check">
        <i class="fas fa-bars" id="btn"></i>
        <i class="fas fa-times" id="cancel"></i>
    </label>
    <div class="sidebar">
        <header>
            <!-- Move the avatar and name above the "Administrator" text -->
            <?php
                $select = mysqli_query($conn, "SELECT * FROM `user_info` WHERE id = '$user_id'") or die('query failed');
                $fetch = mysqli_fetch_assoc($select);

                if ($fetch['photo'] == '') {
                    echo '<img src="default/avatar.png" class="user-avatar">';
                }  else {
                    // Fetch the photo as a blob
                    $photoBlob = $fetch['photo'];

                    // Check if the blob is not empty
                    if (!empty($photoBlob)) {
                        // Output the image
                        echo "<img src=\"$photoBlob\" class=\"user-avatar\" >";
                    } else {
                        // Debugging output if the blob is empty
                        echo '<img src="default/avatar.png" class="user-avatar">';
                    }
                }
            ?>
            <h5><?php echo $fetch['first_name'] . ' ' . $fetch['last_name']; ?></h5>
            <header>Instructor</header>
            <ul>
                <li><a href="professor.php"><i class="fa-solid fa-house"></i></i>Homepage</a></li>
                <li><a href="inputgrades.php"><i class="fas fa-qrcode"></i>Input Grades</a></li>
                <li><a href="#" onclick="confirmLogout()" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
                </form>
            </ul>
        </div>

    <style>

    body {
        background: url('backgroundss.jpg') no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }


    .user-avatar {
        width: 80px; /* Adjust the size as needed */
        height: 80px; /* Keep it the same as width for a circle */
        border-radius: 50%; /* Makes the image circular */
        object-fit: cover; /* Ensures the image covers the area without distortion */
        margin-top: 11px; /* Center the image in the sidebar */
    }

    h2{
        margin-bottom: 10px;
    }

    h5 {
        margin-bottom: -1   0px;
        margin-top: -30px;
        font-size: 20px;
    }
        /* Sidebar */
    .sidebar {
        position: fixed;
        left: -250px;
        top: 0;
        width: 250px;
        height: 100%;
        background: #096c37;
        transition: all .5s ease;
        z-index: 1000;
        overflow-y: auto;
    }

    /* Sidebar header */
    .sidebar header {
        font-size: 22px;
        color: white;
        text-align: center;
        line-height: 70px;
        background: #096c37;
        user-select: none;
    }

    /* Sidebar links styling */
    .sidebar ul a {
        display: block;
        line-height: 65px;
        font-size: 20px;
        color: white;
        text-align: left;
        padding-left: 40px;
        box-sizing: border-box;
        border-top: 1px solid rgba(255, 255, 255, .1);
        border-bottom: 1px solid black;
        transition: .4s;
    }

    /* Hover effect for sidebar links */
    ul li:hover a {
        padding-left: 50px;
    }

    /* Icon styles inside sidebar */
    .sidebar ul a i {
        margin-right: 16px;
    }

    /* Logout link specific styling */
    .sidebar ul a.logout-link {
        color: white; /* Set the text color to red */
    }

    /* Logout link hover effect */
    ul li:hover a.logout-link {
        padding-left: 50px;
        color: #ff5c5c; /* Lighter red on hover */
    }

    /* Sidebar toggle button */

    #check {
                display: none;
            }

            /* Styling for the open button */
            label #btn,
            label #cancel {
                position: absolute;
                cursor: pointer;
                background: #0a3a20;
                border-radius: 3px;
                z-index: 1001;
            }

            /* Button to open the sidebar */
            label #btn {
                left: 20px;
                top: 130px;
                font-size: 35px;
                color: white;
                padding: 6px 12px;
                transition: all .5s;
            }

            /* Button to close the sidebar */
            label #cancel {
                z-index: 1111;
                left: -195px;
                top: 170px;
                font-size: 30px;
                color: #fff;
                padding: 4px 9px;
                transition: all .5s ease;
            }

            /* Toggle: When checked, open the sidebar */
            #check:checked~.sidebar {
                left: 0;
            }

            /* Hide the open button and show the close button when the sidebar is open */
            #check:checked~label #btn {
                left: 200px;
                opacity: 0;
                pointer-events: none;
            }

            /* Move the close button when the sidebar is open */
            #check:checked~label #cancel {
                left: 195px;
            }

            /* Ensure the content shifts when the sidebar is open */
            #check:checked~body {
                margin-left: 250px;
            }

            /* Create a wrapper for all content except sidebar */
    .content-wrapper {
        transition: all .5s ease;
        position: relative;
        width: 100%;
        margin-left: 0;
        z-index: 1;
    }

    /* Adjust the content when sidebar is open */
    #check:checked ~ .content-wrapper {
        margin-left: 150px;
    }

    /* Remove the existing body margin rule if present */
    #check:checked ~ body {
        margin-left: 0;
    }

    /* Ensure header stays full width but shifts with content */
    .header {
        width: 100%;
        transition: margin-left .5s ease;
    }

    #check:checked ~ .content-wrapper .header {
        margin-left: 100px;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: -35px 0;
        gap: 10px;
    }

    .pagination-container button {
        padding: 8px 12px;
        margin: 0 2px;
        border: 1px solid #096c37;
        background-color: white;
        color: #096c37;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .pagination-container button:hover {
        background-color: #096c37;
        color: white;
    }

    .pagination-container button.active {
        background-color: #096c37;
        color: white;
    }

    .pagination-container button[disabled] {
        background-color: #cccccc;
        border-color: #cccccc;
        color: #666666;
        cursor: not-allowed;
    }

    .page-button {
        min-width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }

    #prevPage, #nextPage {
        font-weight: bold;
    }

    .page-button.active {
        background-color: #0a3a20;
        color: white;
    }

    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 245px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }

    .entries-input-container {
        position: relative;
        display: inline-block;
    }

    #rowsPerPageInput {
        width: 70px;
        padding: 5px 10px;
        border: 1px solid #096c37;
        border-radius: 4px;
        background-color: white;
        font-size: 14px;
    }

    .preset-buttons {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: white;
        border: 1px solid #096c37;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 1000;
    }

    .entries-input-container:focus-within .preset-buttons {
        display: block;
    }

    .preset-btn {
        width: 100%;
        padding: 5px 10px;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
        font-size: 14px;
    }

    .preset-btn:hover {
        background-color: #f0f0f0;
    }

    .entries-info {
        position: relative;
        top: -35px;
        margin-left: -110px;
        color: white;
        font-size: 14px;
    }


    .editButton {
        background: none;  /* Remove any background */
        border: none;
        padding: 6px 10px;
        text-align: center;
        display: inline-block;
        font-size: 16px;
        margin: 0px 5px;
        cursor: pointer;
        border-radius: 12px;
    }

    .editButton i {
        font-size: 18px;
        color: black;  /* Make icon black */
    }

    .deleteButton {
        background: none;  /* Remove any background */
        border: none;
        padding: 6px 17px;
        text-align: center;
        display: inline-block;
        font-size: 16px;
        margin: 4px;
        cursor: pointer;
        border-radius: 12px;
    }

    .deleteButton i {
        font-size: 18px;
        color: black;  /* Make icon black */
    }

    .filter-button {
        position: absolute;
        top: 200px;
        left: 217px;
        font-size: 18px;
        padding: 18px;
        width: 30px;
        height: 20px;
        background-color: white;
        color: black;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    dialog {
        border: none;
        border-radius: 8px;
        padding: 20px;
        width: 400px;
        background-color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    dialog h2 {
        margin: 0 0 15px;
        font-size: 24px;
        text-align: center;
    }

    dialog label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    dialog select {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    dialog button {
        background-color: #096c37;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 10px 15px;
        cursor: pointer;
        margin-right: 10px;
    }

    dialog button:hover {
        background-color: #0a3a20;
    }

    dialog button[type="button"] {
        background-color: #ccc;
        color: black;
    }

    dialog button[type="button"]:hover {
        background-color: #bbb;
    }

    .export-container {
        position: absolute;
        margin-top: 10px;
        top: 230px;
        left: 134px;
    }

    #exportButton {
        padding: 10px 20px;
        font-size: 14px;
        background-color: white;
        color: black;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    #editableTable th:nth-child(1), #editableTable td:nth-child(1) { width: 80px; }  /* School ID */
    #editableTable th:nth-child(14), #editableTable td:nth-child(14) { width: 110px; } /* Actions */

    @media screen and (max-width: 2520px){

    .filter-button {
        position: absolute; /* Use absolute positioning */
        top: 200px; /* Adjust the top position */
        left: 300px; /* Adjust the right position */
        /* You can also use left, bottom, etc. */
    }
    .export-container {
    position:absolute;
        margin-top: 10px; /* Space between the table and the button */
        top: 230px; /* Adjust the top position */
        left: 217px; /* Adjust the right position */
    }

        #searchInput { width: 300px;
        left: 1720px;
            
        }


    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 335px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }
    
    
        .semester-controls {
    position: absolute;
    top: 270px;
    left: 123px;
}

    }

    @media screen and (max-width: 1920px){

    .filter-button {
        position: absolute; /* Use absolute positioning */
        top: 200px; /* Adjust the top position */
        left: 280px; /* Adjust the right position */
        /* You can also use left, bottom, etc. */
    }
    .export-container {
    position:absolute;
        margin-top: 10px; /* Space between the table and the button */
        top: 230px; /* Adjust the top position */
        left: 197px; /* Adjust the right position */
    }

        #searchInput { width: 300px;
        left: 1550px;
            
        }


    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 315px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }
    
        .semester-controls {
    position: absolute;
    top: 270px;
    left: 100px;
}

    }


    @media screen and (max-width: 1710px){

    .filter-button {
        position: absolute; /* Use absolute positioning */
        top: 200px; /* Adjust the top position */
        left: 245px; /* Adjust the right position */
        /* You can also use left, bottom, etc. */
    }
    .export-container {
    position:absolute;
        margin-top: 10px; /* Space between the table and the button */
        top: 230px; /* Adjust the top position */
        left: 162px; /* Adjust the right position */
    }

        #searchInput { width: 300px;
        left: 1350px;
            
        }

    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 275px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }
    
        .semester-controls {
    position: absolute;
    top: 270px;
    left: 66px;
}

    }

    @media screen and (max-width: 1600px){

    .filter-button {
        position: absolute; /* Use absolute positioning */
        top: 200px; /* Adjust the top position */
        left: 213.5px; /* Adjust the right position */
        /* You can also use left, bottom, etc. */
    }
    .export-container {
    position:absolute;
        margin-top: 10px; /* Space between the table and the button */
        top: 230px; /* Adjust the top position */
        left: 130px; /* Adjust the right position */
    }

        #searchInput { width: 300px; 
        left: 1200px; 
            
        }

    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 245px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }
    
        .semester-controls {
    position: absolute;
    top: 270px;
    left: 38px;
}

    }

    @media screen and (max-width: 1500px){

    .filter-button {
        position: absolute; /* Use absolute positioning */
        top: 200px; /* Adjust the top position */
        left: 190px; /* Adjust the right position */
        /* You can also use left, bottom, etc. */
    }
    .export-container {
    position:absolute;
        margin-top: 10px; /* Space between the table and the button */
        top: 230px; /* Adjust the top position */
        left: 108px; /* Adjust the right position */
    }

        #searchInput { width: 300px;
        left: 1050px;
            
        }

    .rows-per-page {
        position: absolute;
        top: 160px;
        left: 220px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    }
    
        .semester-controls {
    position: absolute;
    top: 270px;
    left: 14px;
}

    }



    </style>

    <div class="content-wrapper">
    <div class="header">
        <a href="professor.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>
    </div>


    <!-- Search functionality -->
    <div class="search-container">
        <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
    </div>
    <div class="pagination-container">
        <button id="prevPage" onclick="prevPage()">Previous</button>
        <span id="pagination"></span>
        <button id="nextPage" onclick="nextPage()">Next</button>
    </div>
    <!-- Update the rows-per-page div -->
    <div class="rows-per-page">
        <label for="rowsPerPageInput"></label>
        <div class="entries-input-container">
            <input type="number" id="rowsPerPageInput" min="1" value="10">
            <div class="preset-buttons">
                <button class="preset-btn" data-value="10">10</button>
                <button class="preset-btn" data-value="25">25</button>
                <button class="preset-btn" data-value="50">50</button>
                <button class="preset-btn" data-value="100">100</button>
            </div>
        </div>
        <!-- Replace your existing semester select dropdown -->
        <select id="semesterSelect" onchange="changeSemester()">
            <option value="1st">1st Semester</option>
            <option value="2nd">2nd Semester</option>
        </select>
    </div>

    <style>


    /* Style the semester select dropdown */
    #semesterSelect {
        position: absolute;
        top: 0px;
        left: 65px;
        margin-left: 50px;
        padding: 5px 10px;
        border: 1px solid #096c37;
        border-radius: 4px;
        background-color: white;
        font-size: 14px;
        height: 31px; /* Match the height of rowsPerPageInput */
    }
    </style>

    <!-- Modal dialogs -->
    <dialog id="editModal">
        <form method="dialog" id="editForm">
            <h2>Edit Student Grades</h2>
            <label for="editPrelim">Prelims:</label>
            <select id="editPrelim" name="prelim">
                <option value="">Select Grade</option>
                <option value="1.00">1.00</option>
                <option value="1.25">1.25</option>
                <option value="1.50">1.50</option>
                <option value="1.75">1.75</option>
                <option value="2.00">2.00</option>
                <option value="2.25">2.25</option>
                <option value="2.50">2.50</option>
                <option value="2.75">2.75</option>
                <option value="3.00">3.00</option>
                <option value="4.00">4.00</option>
                <option value="5.00">5.00</option>
            </select><br>

            <label for="editMidterm">Midterms:</label>
            <select id="editMidterm" name="midterm">
                <option value="">Select Grade</option>
                <option value="1.00">1.00</option>
                <option value="1.25">1.25</option>
                <option value="1.50">1.50</option>
                <option value="1.75">1.75</option>
                <option value="2.00">2.00</option>
                <option value="2.25">2.25</option>
                <option value="2.50">2.50</option>
                <option value="2.75">2.75</option>
                <option value="3.00">3.00</option>
                <option value="4.00">4.00</option>
                <option value="5.00">5.00</option>
            </select><br>

            <label for="editFinals">Finals:</label>
            <select id="editFinals" name="finals">
                <option value="">Select Grade</option>
                <option value="1.00">1.00</option>
                <option value="1.25">1.25</option>
                <option value="1.50">1.50</option>
                <option value="1.75">1.75</option>
                <option value="2.00">2.00</option>
                <option value="2.25">2.25</option>
                <option value="2.50">2.50</option>
                <option value="2.75">2.75</option>
                <option value="3.00">3.00</option>
                <option value="4.00">4.00</option>
                <option value="5.00">5.00</option>
            </select><br>

            <label for="editStatus">Status:</label>
            <select id="editStatus" name="status">
                <option value="">-- Select Status --</option>
                <option value="INC">INC</option>
                <option value="DROP">DROP</option>
            </select><br>

            <button type="submit">Save</button>
            <button type="button" onclick="document.getElementById('editModal').close()">Cancel</button>
        </form>
    </dialog>


    <dialog id="addModal">
        <form method="dialog" id="addForm">
        <h2>Add New Student</h2>
        <label for="addSchoolId">School ID:</label>
        <input type="text" id="addSchoolId" name="school_id" required><br>

        <label for="addFirstName">First Name:</label>
        <input type="text" id="addFirstName" name="first_name" required><br>

        <label for="addLastName">Last Name:</label>
        <input type="text" id="addLastName" name="last_name" required><br>

        <label for="addGender">Gender:</label>
        <select id="addGender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br>

        <label for="addNstp">NSTP:</label>
        <input type="text" id="addNstp" name="nstp" required><br>

        <label for="addDepartment">Department:</label>
        <input type="text" id="addDepartment" name="department" required><br>

        <label for="addCourse">Course:</label>
        <input type="text" id="addCourse" name="course" required><br>

        <button type="submit">Add</button>
        <button type="button" onclick="addModal.close()">Cancel</button>
        </form>
    </dialog>

    <dialog id="deleteModal">
        <form id="deleteForm">
            <h2>Delete Student Grades</h2>
            <p>Are you sure you want to delete all grades for this student?</p>
            <input type="hidden" name="action" value="delete_grade">
            <input type="hidden" name="grades_id" id="gradesIdInput">
            <input type="hidden" name="school_id" id="deleteSchoolIdInput">
            <input type="hidden" name="semester" id="deleteSemesterInput">
            <button type="submit">Delete All Grades</button>
            <button type="button" onclick="document.getElementById('deleteModal').close()">Cancel</button>
        </form>
    </dialog>


    <!-- Filter Button -->
    <button class="filter-button" onclick="openFilterModal()">
        <i class="fa-solid fa-filter"></i>
    </button>

    <!-- Filter Modal -->
    <dialog id="filterModal">
        <h2>Filter Options</h2>
        <form id="filterForm">
            <div class="filter-group">
                <label for="schoolIdFilter">School Year:</label>
                <select id="schoolIdFilter">
                    <option value="">All</option>
                    <?php 
                    // Sort school IDs before displaying
                    $schoolIdArray = array();
                    while ($row = $schoolIds->fetch_assoc()) {
                        $schoolIdArray[] = $row['school_prefix'];
                    }
                    sort($schoolIdArray);
                    foreach ($schoolIdArray as $schoolId): 
                    ?>
                        <option value="<?php echo htmlspecialchars($schoolId); ?>">
                            <?php echo htmlspecialchars($schoolId); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="semesterFilter">Semester:</label>
                <select id="semesterFilter">
                    <option value="">All</option>
                    <?php 
                    $semesterArray = array();
                    while ($row = $semesters->fetch_assoc()) {
                        $semesterArray[] = $row['semester'];
                    }
                    sort($semesterArray);
                    foreach ($semesterArray as $semester): 
                    ?>
                        <option value="<?php echo htmlspecialchars($semester); ?>">
                            <?php echo htmlspecialchars($semester); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="genderFilter">Gender:</label>
                <select id="genderFilter">
                    <option value="">All</option>
                    <?php 
                    $genderArray = array();
                    while ($row = $genders->fetch_assoc()) {
                        $genderArray[] = $row['gender'];
                    }
                    sort($genderArray);
                    foreach ($genderArray as $gender): 
                    ?>
                        <option value="<?php echo htmlspecialchars($gender); ?>">
                            <?php echo htmlspecialchars($gender); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="collegeFilter">College:</label>
                <select id="collegeFilter">
                    <option value="">All</option>
                    <?php 
                    $collegeArray = array();
                    while ($row = $colleges->fetch_assoc()) {
                        $collegeArray[] = $row['department'];
                    }
                    sort($collegeArray);
                    foreach ($collegeArray as $college): 
                    ?>
                        <option value="<?php echo htmlspecialchars($college); ?>">
                            <?php echo htmlspecialchars($college); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="programFilter">Program:</label>
                <select id="programFilter">
                    <option value="">All</option>
                    <?php 
                    $programArray = array();
                    while ($row = $programs->fetch_assoc()) {
                        $programArray[] = $row['course'];
                    }
                    sort($programArray);
                    foreach ($programArray as $program): 
                    ?>
                        <option value="<?php echo htmlspecialchars($program); ?>">
                            <?php echo htmlspecialchars($program); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter">
                    <option value="">All</option>
                    <?php 
                    $statusArray = array();
                    while ($row = $statuses->fetch_assoc()) {
                        $statusArray[] = $row['status'];
                    }
                    sort($statusArray);
                    foreach ($statusArray as $status): 
                    ?>
                        <option value="<?php echo htmlspecialchars($status); ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="apply-filter">Apply Filters</button>
                <button type="button" class="reset-filter" onclick="resetFilters()">Reset Filters</button>
                <button type="button" class="close-filter" onclick="closeFilterModal()">Close</button>
            </div>
        </form>
    </dialog>


    <!-- Export Data Button -->
    <div class="export-container">
        <button id="exportButton" onclick="openExportModal()">Export Data</button>
    </div>

    <!-- Export Modal -->
    <dialog id="exportModal">
        <form method="dialog" id="exportForm">
            <h2>Select Export Format</h2>
            <button type="button" onclick="exportData('csv')">Export as CSV</button>
            <button type="button" onclick="exportData('word')">Export as Word</button>
            <button type="button" onclick="closeExportModal()">Cancel</button>
        </form>
    </dialog>






    <script>
        // Search function
        function searchRecords() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toUpperCase();
    let table = document.getElementById("editableTable");
    let tr = table.getElementsByTagName("tr");
    let noResultsRow = document.getElementById('noResultsRow');
    let hasVisibleRows = false; // Track if any rows are visible

    // Reset to the first page if the search input is cleared
    if (filter === "") {
        currentPage = 1;
        paginateTable();
        noResultsRow.style.display = 'none'; // Hide "No Results Found" message when search is cleared
        return;
    }

    // Loop through all rows except the header and no results row
    for (let i = 1; i < tr.length - 1; i++) {
        let row = tr[i];
        let cells = row.getElementsByTagName("td");
        let textContent = "";

        // Concatenate text from desired columns for search
        for (let j = 0; j < cells.length; j++) {
        textContent += cells[j].textContent || cells[j].innerText;
        }

        // Show or hide rows based on search filter
        if (textContent.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
        hasVisibleRows = true; // Mark as having visible rows
        } else {
        tr[i].style.display = "none";
        }
    }

    // Show the "No Results Found" row if no rows are visible, otherwise hide it
    if (!hasVisibleRows) {
        noResultsRow.style.display = 'table-row';
    } else {
        noResultsRow.style.display = 'none';
    }
    }

        // Open add student modal
        function openAddModal() {
        const addModal = document.getElementById('addModal');
        addModal.showModal();
        }

    // Edit grades modal functionality
    function editGradesInfo(button) {
        const row = button.closest('tr');
        const gradesId = row.getAttribute('data-grades-id');
        const schoolId = row.getAttribute('data-school-id');
        const semester = row.querySelector('td:nth-child(7)').textContent;
        
        const prelim = row.querySelector('.prelim').textContent;
        const midterm = row.querySelector('.midterm').textContent;
        const finals = row.querySelector('.finals').textContent;
        const status = row.querySelector('.status').textContent;
        
        // Get the form and modal
        const editForm = document.getElementById('editForm');
        const editModal = document.getElementById('editModal');
        
        // Set form attributes
        editForm.setAttribute('data-grades-id', gradesId);
        editForm.setAttribute('data-school-id', schoolId);
        editForm.setAttribute('data-semester', semester);
        
        // Set select values
        document.getElementById('editPrelim').value = prelim;
        document.getElementById('editMidterm').value = midterm;
        document.getElementById('editFinals').value = finals;
        
        // Set status if it exists
        const statusSelect = document.getElementById('editStatus');
        if (status === 'INC' || status === 'DROP') {
            statusSelect.value = status;
        } else {
            statusSelect.value = '';
        }
        
        // Show the modal
        editModal.showModal();
    }

    // Add click event listeners to edit buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handlers for edit buttons
        const editButtons = document.querySelectorAll('.editButton');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                editGradesInfo(this);
            });
        });
    });

    // Handle edit form submission
    document.getElementById('editForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const editForm = event.target;
        const gradesId = editForm.getAttribute('data-grades-id');
        const schoolId = editForm.getAttribute('data-school-id');
        const semester = document.getElementById('semesterSelect').value; // Get current semester
        
        // Get form values
        const prelimValue = document.getElementById('editPrelim').value.trim();
        const midtermValue = document.getElementById('editMidterm').value.trim();
        const finalsValue = document.getElementById('editFinals').value.trim();
        const statusValue = document.getElementById('editStatus').value;

        // Create form data
        const formData = new FormData();
        formData.append('grades_id', gradesId);
        formData.append('school_id', schoolId);
        formData.append('semester', semester); // Important: Include semester
        formData.append('prelim', prelimValue);
        formData.append('midterm', midtermValue);
        formData.append('finals', finalsValue);
        if (statusValue) {
            formData.append('status', statusValue);
        }

        // Send the request
        fetch('inputgrades.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editModal').close();
                alert('Grades updated successfully!');
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to update grades');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    });







    // Function to update final grades and status
    function updateFinalGrades(row, prelim, midterm, finals) {
        let finalGrades = null;
        let status = row.querySelector('.status').textContent; // Get current status
        
        // Calculate final grades if all components are present
        if (prelim !== null && midterm !== null && finals !== null) {
            finalGrades = (prelim + midterm + finals) / 3;
            finalGrades = roundToNearestGrade(finalGrades);
            
            // Always update status if all grades are present
            status = (finalGrades >= 1 && finalGrades <= 3) ? 'PASSED' : 'FAILED';
        } else if (status !== 'INC' && status !== 'DROP') {
            // If any grade is missing and status isn't INC or DROP, clear the status
            status = '';
        }
        
        // Update the display
        row.querySelector('.final_grades').textContent = finalGrades !== null ? finalGrades.toFixed(2) : '';
        row.querySelector('.status').textContent = status;
    }

    // Function to round final grades to specified values
    function roundToNearestGrade($value) {
        const validGrades = [1.000, 1.250, 1.500, 1.750, 2.000, 2.250, 2.500, 2.750, 3.000, 4.000, 5.000];
        let closestGrade = validGrades[0];

        for (let i = 1; i < validGrades.length; i++) {
            if (Math.abs(validGrades[i] - value) < Math.abs(closestGrade - value)) {
                closestGrade = validGrades[i];
            }
        }

        return closestGrade;
    }

    // Function to update final grades
    function updateFinalGrades(row, prelim, midterm, finals) {
        let finalGrades = null;
        if (prelim !== null && midterm !== null && finals !== null) {
            finalGrades = (prelim + midterm + finals) / 3;
            finalGrades = roundToNearestGrade(finalGrades); // Round to nearest valid grade
        }
        row.querySelector('.final_grades').textContent = finalGrades !== null ? finalGrades.toFixed(2) : '';
    }




        //DELETE BUTTON

    // Function to open delete modal
    function openDeleteModal(button) {
        const row = button.closest('tr');
        const gradesId = row.getAttribute('data-grades-id');
        const schoolId = row.getAttribute('data-school-id');
        const semester = document.getElementById('semesterSelect').value; // Get current semester
        
        // Set the values in hidden inputs for submission
        document.getElementById('gradesIdInput').value = gradesId;
        document.getElementById('deleteSchoolIdInput').value = schoolId;
        document.getElementById('deleteSemesterInput').value = semester;
        
        // Add these hidden inputs to your deleteForm
        const formData = new FormData(document.getElementById('deleteForm'));
        formData.append('school_id', schoolId);
        formData.append('semester', semester);
        
        // Show the modal
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.showModal();
    }

    // Update the delete form submission handler
    document.getElementById('deleteForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const gradesId = document.getElementById('gradesIdInput').value;
        const row = document.querySelector(`tr[data-grades-id="${gradesId}"]`);
        const schoolId = row.getAttribute('data-school-id');
        const semester = document.getElementById('semesterSelect').value;
        
        // Prepare the form data
        const formData = new FormData();
        formData.append('action', 'delete_grade');
        formData.append('grades_id', gradesId);
        formData.append('school_id', schoolId);
        formData.append('semester', semester);
        
        // Send the request to the server
        fetch('inputgrades.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('deleteModal').close();
                alert('Grades deleted successfully!');
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to delete grades');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    });




    /* PAGINATION OF THE TABLE JS */
    let currentPage = 1;
    let rowsPerPage = 10; // Default value

    function initializeRowsPerPage() {
        const input = document.getElementById('rowsPerPageInput');
        const presetButtons = document.querySelectorAll('.preset-btn');

        // Set up input event handler
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
                value = 1;
            }
            rowsPerPage = value;
            currentPage = 1; // Reset to first page when changing entries
            paginateTable();
        });

        // Set up input validation
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                    value = 1;
                }
                rowsPerPage = value;
                currentPage = 1;
                paginateTable();
            }
        });

        // Set up preset buttons
        presetButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = parseInt(this.dataset.value);
                input.value = value;
                rowsPerPage = value;
                currentPage = 1;
                paginateTable();
            });
        });

        // Set initial value
        input.value = rowsPerPage;
    }

    function paginateTable() {
        let table = document.getElementById("editableTable");
        let tbody = table.getElementsByTagName("tbody")[0];
        let tr = tbody.getElementsByTagName("tr");
        let totalRows = 0;

        // Count only visible rows (excluding the "No Results Found" row)
        for (let i = 0; i < tr.length; i++) {
            if (tr[i] !== document.getElementById('noResultsRow') && 
                !tr[i].classList.contains('filtered-out')) {
                totalRows++;
            }
        }

        let totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

        // Ensure currentPage stays within valid range
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;

        let start = (currentPage - 1) * rowsPerPage;
        let end = Math.min(start + rowsPerPage, totalRows);
        let visibleIndex = 0;

        // Hide all rows first
        for (let i = 0; i < tr.length; i++) {
            if (tr[i] !== document.getElementById('noResultsRow')) {
                if (!tr[i].classList.contains('filtered-out')) {
                    if (visibleIndex >= start && visibleIndex < end) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                    visibleIndex++;
                }
            }
        }

        // Update buttons state
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages;

        // Show total entries information
        updateEntriesInfo(Math.min(start + 1, totalRows), end, totalRows);

        // Update pagination display
        updatePagination(totalPages);
    }

    function updatePagination(totalPages) {
        let paginationElement = document.getElementById('pagination');
        paginationElement.innerHTML = "";

        // Maximum number of page buttons to show
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);

        // Adjust startPage if we're near the end
        if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        // Add first page button if not visible
        if (startPage > 1) {
            addPageButton(1, paginationElement);
            if (startPage > 2) {
                let ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'ellipsis';
                paginationElement.appendChild(ellipsis);
            }
        }

        // Add numbered page buttons
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i, paginationElement);
        }

        // Add last page button if not visible
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                let ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'ellipsis';
                paginationElement.appendChild(ellipsis);
            }
            addPageButton(totalPages, paginationElement);
        }
    }

    function addPageButton(pageNum, container) {
        let pageButton = document.createElement("button");
        pageButton.innerHTML = pageNum;
        pageButton.classList.add('page-button');
        if (pageNum === currentPage) {
            pageButton.classList.add('active');
        }
        pageButton.onclick = function() {
            currentPage = pageNum;
            paginateTable();
        };
        container.appendChild(pageButton);
    }

    function updateEntriesInfo(start, end, total) {
        const entriesInfo = document.createElement('div');
        entriesInfo.className = 'entries-info';
        entriesInfo.textContent = `Showing ${start} to ${end} of ${total} entries`;
        
        // Find the existing entries info and replace it, or append it if it doesn't exist
        let existing = document.querySelector('.entries-info');
        if (existing) {
            existing.replaceWith(entriesInfo);
        } else {
            document.querySelector('.rows-per-page').appendChild(entriesInfo);
        }
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            paginateTable();
        }
    }

    function nextPage() {
        let table = document.getElementById("editableTable");
        let tbody = table.getElementsByTagName("tbody")[0];
        let tr = tbody.getElementsByTagName("tr");
        let totalRows = tr.length - 1; // Subtract "No Results" row
        let totalPages = Math.ceil(totalRows / rowsPerPage);
        
        if (currentPage < totalPages) {
            currentPage++;
            paginateTable();
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeRowsPerPage();
        paginateTable();
    });

    function applyFilters() {
        // Get filter values
        const schoolIdFilter = document.getElementById('schoolIdFilter').value.toUpperCase();
        const semesterFilter = document.getElementById('semesterFilter').value.toUpperCase();
        const genderFilter = document.getElementById('genderFilter').value.toUpperCase();
        const collegeFilter = document.getElementById('collegeFilter').value.toUpperCase();
        const programFilter = document.getElementById('programFilter').value.toUpperCase();
        const statusFilter = document.getElementById('statusFilter').value.toUpperCase();

        // Get all rows from table body
        const table = document.getElementById('editableTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = tbody.getElementsByTagName('tr');
        let hasVisibleRows = false;

        // Loop through each row (except the noResultsRow)
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.id === 'noResultsRow') continue;

            const cells = row.getElementsByTagName('td');
            if (cells.length === 0) continue;

            // Get values from the correct columns
            const schoolId = cells[0].textContent.trim().toUpperCase();
            const gender = cells[5].textContent.trim().toUpperCase();
            const semester = cells[6].textContent.trim().toUpperCase();
            const college = cells[8].textContent.trim().toUpperCase();
            const program = cells[9].textContent.trim().toUpperCase();
            const status = cells[14].textContent.trim().toUpperCase();

            // Check if the row matches all selected filters
            const matchesFilter = (
                (!schoolIdFilter || schoolId.startsWith(schoolIdFilter)) &&
                (!semesterFilter || semester === semesterFilter) &&
                (!genderFilter || gender === genderFilter) &&
                (!collegeFilter || college === collegeFilter) &&
                (!programFilter || program === programFilter) &&
                (!statusFilter || status === statusFilter)
            );

            // Show/hide row based on filter match
            if (matchesFilter) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        }

        // Show/hide "No Results" message
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            noResultsRow.style.display = hasVisibleRows ? 'none' : 'table-row';
        }

        // Close the filter modal
        closeFilterModal();
    }

    // Add event listener to the Apply Filters button
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent form submission
                applyFilters();
            });
        }

        // Remove the automatic filter application on change
        /* Remove this section
        filterIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', function() {
                    applyFilters();
                });
            }
        });
        */
    });

    function resetFilters() {
        // Reset all filter dropdowns
        const filterIds = [
            'schoolIdFilter',
            'semesterFilter',
            'genderFilter',
            'collegeFilter',
            'programFilter',
            'statusFilter'
        ];

        filterIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
            }
        });

        // Show all rows by removing filtered-out class
        const table = document.getElementById("editableTable");
        const rows = table.getElementsByTagName("tr");
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].id !== 'noResultsRow') {
                rows[i].classList.remove('filtered-out');
            }
        }

        // Hide "No Results" message
        const noResultsRow = document.getElementById('noResultsRow');
        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }

        // Reset pagination and update display
        currentPage = 1;
        paginateTable();
    }

    // Add this CSS to your existing styles
    function addFilterStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .filtered-out {
                display: none !important;
            }
        `;
        document.head.appendChild(style);
    }

    // Call this when the document loads
    document.addEventListener('DOMContentLoaded', function() {
        addFilterStyles();
        // ... your other initialization code ...
    });

    function openFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.showModal();
    }

    function closeFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.close();
    }







    function openExportModal() {
        document.getElementById('exportModal').showModal();
    }

    function closeExportModal() {
        document.getElementById('exportModal').close();
    }

    function exportData(format) {
        if (format === 'csv') {
            exportToCSV();
        } else if (format === 'word') {
            exportToWord();
        }
        closeExportModal();
    }


    function exportToCSV() {
        const table = document.getElementById("editableTable");
        const rows = table.getElementsByTagName("tr");
        let csvContent = "data:text/csv;charset=utf-8,";
        let exportCount = 0;

        // Updated header
        csvContent += "STUDENT NO.,STUDENT NAME,COURSE,FINAL GRADES,STATUS\n";

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            if (row.style.display !== "none" && row.id !== 'noResultsRow') {
                const cells = row.getElementsByTagName("td");
                if (cells.length > 0) {
                    const schoolId = cells[0].textContent;
                    const lastName = cells[1].textContent;
                    const firstName = cells[2].textContent;
                    const mi = cells[3].textContent;
                    const program = cells[9].textContent;
                    const finalGrades = cells[13].textContent;
                    const status = cells[14].textContent;

                    // Format student name as "Last Name, First Name MI."
                    const formattedName = `${lastName}, ${firstName} ${mi ? mi + '.' : ''}`;

                    const escapedValues = [
                        schoolId,
                        formattedName.includes(',') ? `"${formattedName}"` : formattedName,
                        program.includes(',') ? `"${program}"` : program,
                        finalGrades,
                        status
                    ];

                    const csvRow = escapedValues.join(',');
                    csvContent += csvRow + "\n";
                    exportCount++;
                }
            }
        }

        // Update the log message with the count
        fetch('log_export.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: 'CSV',
                description: `Exported ${exportCount} student records to CSV format`
            })
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "students_data.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportToWord() {
        const table = document.getElementById("editableTable");
        const rows = table.getElementsByTagName("tr");
        let exportCount = 0;

        let header = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word'>
            <head>
                <meta charset="utf-8">
                <title>Export HTML to Word Document with JavaScript</title>
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: center; /* Center all content */
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                </style>
            </head>
            <body>
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="text-align: center; font-size: 16px; font-weight: normal;">Republic of the Philippines</h1>
                <h1 style="text-align: center; font-size: 22px; font-weight: bold;">SOUTHERN LUZON STATE UNIVERSITY</h1>
                <h2 style="text-align: center; font-size: 16px; font-weight: bold;">MASTERS LIST</h2>
                <p style="text-align: left;">SUBJECT: The National Service Training Program</p>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: center;">
                <tr>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>STUDENT NO.</i></th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>STUDENT NAME</i></th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>COURSE</i></th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>FINAL GRADES</i></th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>STATUS</i></th>
                </tr>
        `;

        let html = header;

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            if (row.style.display !== "none" && row.id !== 'noResultsRow') {
                const cells = row.getElementsByTagName("td");
                if (cells.length > 0) {
                    const schoolId = cells[0].textContent;
                    const lastName = cells[1].textContent;
                    const firstName = cells[2].textContent;
                    const mi = cells[3].textContent;
                    const program = cells[9].textContent;
                    const finalGrades = cells[13].textContent;
                    const status = cells[14].textContent;

                    // Format student name as "Last Name, First Name MI."
                    const formattedName = `${lastName}, ${firstName} ${mi ? mi + '.' : ''}`;

                    html += `
                        <tr>
                            <td style="border: 1px solid black; padding: 5px; text-align: center;">${schoolId}</td>
                            <td style="border: 1px solid black; padding: 5px; text-align: left;">${formattedName}</td>
                            <td style="border: 1px solid black; padding: 5px; text-align: center;">${program}</td>
                            <td style="border: 1px solid black; padding: 5px; text-align: center;">${finalGrades}</td>
                            <td style="border: 1px solid black; padding: 5px; text-align: center;">${status}</td>
                        </tr>
                    `;
                    exportCount++;
                }
            }
        }

        html += `
                </table>
                <br>
                <div style="margin-top: 30px;">
                    <div style="float: left; width: 50%;">
                        <p>Prepared by:</p>
                        <br><br>
                        <p style="text-decoration: underline;">_______________________</p>
                        <p>NSTP Coordinator</p>
                    </div>
                    <div style="float: right; width: 50%; text-align: right;">
                        <p>Noted by:</p>
                        <br><br>
                        <p style="text-decoration: underline;">_______________________</p>
                        <p>NSTP Director</p>
                    </div>
                </div>
            </body>
            </html>
        `;

        // Update the log message with the count
        fetch('log_export.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: 'WORD',
                description: `Exported ${exportCount} student records to Word format`
            })
        });

        const blob = new Blob([html], { type: 'application/msword' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "students_data.doc";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }











        </script>

        <script>
        // Add this function to handle semester changes
        function changeSemester() {
            const semester = document.getElementById('semesterSelect').value;
            
            // Store the current page number and rows per page
            const currentRowsPerPage = document.getElementById('rowsPerPageInput').value;
            
            // Update URL with new semester parameter
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('semester', semester);
            currentUrl.searchParams.set('rows', currentRowsPerPage);
            
            // Reload the page with the new parameters
            window.location.href = currentUrl.toString();
        }

        // Initialize semester selector based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const semester = urlParams.get('semester') || '1st';
            
            // Set the semester dropdown value
            const semesterSelect = document.getElementById('semesterSelect');
            if (semesterSelect) {
                semesterSelect.value = semester;
            }
        });
        
                function confirmLogout() {
            if (confirm("Do you want to Logout?")) {
                window.location.href = "logout.php";
            }
        }
        </script>

        <!-- Add this right before the closing </body> tag -->
        <script>
        function finishSemesterGrades(semester) {
            if (!confirm(`Are you sure you want to finish ${semester} semester grades? This action cannot be undone and will lock all grades for editing.`)) {
                return;
            }

            fetch('complete_semester.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    semester: semester
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`${semester} semester grades have been finalized successfully.`);
                    // Hide all edit and delete buttons
                    document.querySelectorAll('.editButton, .deleteButton').forEach(btn => {
                        btn.style.display = 'none';
                    });
                    // Disable the finish grades button
                    document.getElementById('finishGradesBtn').disabled = true;
                    // Optionally reload the page
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to finalize grades'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while finalizing grades.');
            });
        }

        // Add this to check semester status on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentSemester = '<?php echo $currentSemester; ?>';
            
            // Check if semester is already completed
            fetch('check_semester_status.php?semester=' + currentSemester)
                .then(response => response.json())
                .then(data => {
                    if (data.completed) {
                        // Hide all edit and delete buttons
                        document.querySelectorAll('.editButton, .deleteButton').forEach(btn => {
                            btn.style.display = 'none';
                        });
                        // Disable the finish grades button
                        document.getElementById('finishGradesBtn').disabled = true;
                    }
                });
        });
        </script>

        </body>

        </html>
