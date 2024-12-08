<?php
require_once("db_conn.php");
session_start();

// At the top of your file after session_start()
if (isset($_SESSION['username'])) {
    $current_instructor = $_SESSION['username'];
} else {
    // Redirect to login if not logged in
    header("Location: faculty.php");
    exit();
}


// Handle grade deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_grade') {
    header('Content-Type: application/json');
    
    try {
        $gradesId = isset($_POST['grades_id']) ? intval($_POST['grades_id']) : 0;
        
        if ($gradesId <= 0) {
            throw new Exception('Invalid grades ID');
        }

        // Connect to database
        $conn = connect_db();

        // Update query to set ALL grade-related fields to NULL
        $sql = "UPDATE tbl_students_grades 
                SET prelim = NULL, 
                    midterm = NULL, 
                    finals = NULL, 
                    final_grades = NULL, 
                    status = NULL 
                WHERE grades_id = ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$gradesId])) {
            // Check if the update was successful
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Grades deleted successfully'
                ]);
            } else {
                throw new Exception('No records were updated');
            }
        } else {
            throw new Exception('Failed to execute update query');
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  error_log("Received POST request: " . print_r($_POST, true));

  $grades_id = isset($_POST['grades_id']) ? intval($_POST['grades_id']) : 0;
  $school_id = isset($_POST['school_id']) ? $_POST['school_id'] : '';

  // Use null if the field is empty, not set, or zero
  $prelim = (isset($_POST['prelim']) && $_POST['prelim'] !== '' && floatval($_POST['prelim']) !== 0.0) ? floatval($_POST['prelim']) : null;
  $midterm = (isset($_POST['midterm']) && $_POST['midterm'] !== '' && floatval($_POST['midterm']) !== 0.0) ? floatval($_POST['midterm']) : null;
  $finals = (isset($_POST['finals']) && $_POST['finals'] !== '' && floatval($_POST['finals']) !== 0.0) ? floatval($_POST['finals']) : null;

  $conn = connect_db();

  // Prepare the statement
  if ($grades_id > 0) {
    // Update existing grades
    $stmt = $conn->prepare("UPDATE tbl_students_grades SET prelim = ?, midterm = ?, finals = ? WHERE grades_id = ?");
    $stmt->bind_param("dddi", $prelim, $midterm, $finals, $grades_id);
  } else {
    // Insert new grades
    $stmt = $conn->prepare("INSERT INTO tbl_students_grades (school_id, prelim, midterm, finals) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddd", $school_id, $prelim, $midterm, $finals);
  }

  if ($stmt->execute()) {
    if ($grades_id == 0) {
      $grades_id = $stmt->insert_id;
    }

    // Calculate final grades
    $finalGrades = null;
    if ($prelim !== null && $midterm !== null && $finals !== null) {
        $finalGrades = ($prelim + $midterm + $finals) / 3;
        $finalGrades = roundToNearestGrade($finalGrades); // Round to nearest valid grade

        // Determine status based on final grades
        $status = ($finalGrades >= 1 && $finalGrades <= 3) ? 'PASSED' : 'FAILED';

        // Update final grades and status in the database
        $updateFinalsStmt = $conn->prepare("UPDATE tbl_students_grades SET final_grades = ?, status = ? WHERE grades_id = ?");
        $updateFinalsStmt->bind_param("ssi", $finalGrades, $status, $grades_id);
        
        if ($updateFinalsStmt->execute()) {
            // Return the updated values in the response
            echo json_encode([
                'success' => true,
                'final_grades' => $finalGrades,
                'status' => $status,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
        }
        $updateFinalsStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid grades data.']);
    }
    $conn->close();
    exit;
}



  if ($grades_id > 0) {
    // Update existing grades, allow NULL for midterm and finals
    $stmt = $conn->prepare("UPDATE tbl_students_grades SET prelim = ?, midterm = ?, finals = ? WHERE grades_id = ?");
    $stmt->bind_param("dddi", $prelim, $midterm, $finals, $grades_id);
  } else {
    // Insert new grades, allow NULL for midterm and finals
    $stmt = $conn->prepare("INSERT INTO tbl_students_grades (school_id, prelim, midterm, finals) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddd", $school_id, $prelim, $midterm, $finals);
  }
}



// If it's not a POST request, log this information
error_log("Received non-POST request: " . $_SERVER['REQUEST_METHOD']);

// Fetch student data for displaying in the table
  $conn = connect_db();
 // Near the top of the file, update the query that fetches students
$instructor = $_SESSION['username']; // Assuming username is stored in session
$sql = "SELECT 
    c.school_id, 
    c.first_name, 
    c.last_name, 
    c.gender,
    c.semester,
    c.nstp,
    c.department,
    c.course,
    COALESCE(g.grades_id, 0) AS grades_id,
    g.prelim, 
    g.midterm, 
    g.finals,
    g.final_grades,  -- Change this line to directly use final_grades from database
    g.status
FROM 
    tbl_cwts c
LEFT JOIN 
    tbl_students_grades g ON c.school_id = g.school_id
WHERE 
    c.instructor = '$current_instructor'";

 


$results = $conn->query($sql);


$stmt = $conn->prepare($sql);

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
$instructor_id = $_SESSION['user_id']; // or however you store the current user's ID

// Modify the filter queries
$schoolIds = $conn->query("SELECT DISTINCT SUBSTRING(c.school_id, 1, 3) AS school_prefix 
    FROM tbl_cwts c 
    WHERE c.instructor = '$current_instructor'");

$semesters = $conn->query("SELECT DISTINCT c.semester 
    FROM tbl_cwts c 
    WHERE c.instructor = '$current_instructor'");

$genders = $conn->query("SELECT DISTINCT c.gender 
    FROM tbl_cwts c 
    WHERE c.instructor = '$current_instructor'");

$colleges = $conn->query("SELECT DISTINCT c.department 
    FROM tbl_cwts c 
    WHERE c.instructor = '$current_instructor'");

$programs = $conn->query("SELECT DISTINCT c.course 
    FROM tbl_cwts c 
    WHERE c.instructor = '$current_instructor'");

$statuses = $conn->query("SELECT DISTINCT g.status 
    FROM tbl_cwts c 
    JOIN tbl_students_grades g ON c.school_id = g.school_id 
    WHERE c.instructor = '$current_instructor'");
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" type="text/css" href="cwtsStud.css">
</head>

<body>

  <div class="header">
    <a href="professor.php"><img src="slsulogo.png" class="headlogo"></a>
    <h1>Southern Luzon State University</h1>
    <p>National Service Training Program</p>
  </div>
  

  <!-- Table of students with grades -->
  <table id="editableTable" class="table">
    <thead>
      <tr>
        <th>School ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Gender</th>
        <th>Semester</th>
        <th>NSTP</th>
        <th>Department</th>
        <th>Course</th>
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
        echo "<tr data-grades-id='{$rows["grades_id"]}' data-school-id='{$rows["school_id"]}'>";
        echo "<td>{$rows["school_id"]}</td>";
        echo "<td>{$rows["first_name"]}</td>";
        echo "<td>{$rows["last_name"]}</td>";
        echo "<td>{$rows["gender"]}</td>";
        echo "<td>{$rows["semester"]}</td>";
        echo "<td>{$rows["nstp"]}</td>";
        echo "<td>{$rows["department"]}</td>";
        echo "<td>{$rows["course"]}</td>";
        echo "<td class='prelim'>{$rows["prelim"]}</td>";
        echo "<td class='midterm'>{$rows["midterm"]}</td>";
        echo "<td class='finals'>{$rows["finals"]}</td>";
        // Changed this line to use final_grades directly from database
        echo "<td class='final_grades' style='font-weight: bold;'>" . ($rows["final_grades"] !== null ? number_format($rows["final_grades"], 2) : '') . "</td>";
        echo "<td class='status'>{$rows["status"]}</td>";
        echo "<td>";
        echo "<button class='editButton' onclick='editGradesInfo(this)'><i class='fa-solid fa-pen-to-square'></i></button>";
        echo "<button class='deleteButton' onclick='openDeleteModal(this)'><i class='fa-solid fa-trash'></i></button>";
        echo "</td>";
        echo "</tr>";
    }  
  } else {
    // Show "No Student Assigned" message
    echo "<tr>";
    echo "<td colspan='12' style='text-align: center; padding: 20px; font-weight: bold; color: #666;'>No Student Assigned</td>";
    echo "</tr>";
  }
  ?>
  <tr id="noResultsRow" style="display: none;">
    <td colspan="12" style="text-align: center; color: red;">No Results Found</td>
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
            <li><a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
            </form>
        </ul>
    </div>

  <style>

body {
    background: url('backgroundss.jpg');
    background-position: center;
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
    left: 250px;
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



  </style>


  <!-- Search functionality -->
  <div class="search-container">
    <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
  </div>
  <div class="pagination-container">
    <button id="prevPage" onclick="prevPage()">Previous</button>
    <span id="pagination"></span>
    <button id="nextPage" onclick="nextPage()">Next</button>
</div>
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
    <label></label>
</div>
  <!-- Modal dialogs -->
  <dialog id="editModal">
    <form method="dialog" id="editForm">
      <h2>Edit Student Grades</h2>
      <label for="editPrelims">Prelims:</label>
      <input type="number" id="editPrelim" name="prelim" min="1.00" max="5.00" step="0.25"><br>

      <label for="editMidterm">Midterms:</label>
      <input type="number" id="editMidterm" name="midterm" min="1.00" max="5.00" step="0.25"><br>

      <label for="editFinals">Finals:</label>
      <input type="number" id="editFinals" name="finals" min="1.00" max="5.00" step="0.25"><br>

<label for="editStatus">Status:</label>
<select id="editStatus" name="status">
    <option value="">-- Select Status --</option>
    <option value="INCOMPLETE">INCOMPLETE</option>
    <option value="DROP">DROP</option>
</select><br>


      <button type="submit">Save</button>
      <button type="button" onclick="editModal.close()">Cancel</button>
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
    <form method="dialog" id="filterForm">
        <h2>Filter Options</h2>

        <label for="schoolIdFilter">School Year:</label>
        <select id="schoolIdFilter">
            <option value="">All</option>
            <?php while ($row = $schoolIds->fetch_assoc()): ?>
                <option value="<?php echo $row['school_prefix']; ?>"><?php echo $row['school_prefix']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="semesterFilter">Semester:</label>
        <select id="semesterFilter">
            <option value="">All</option>
            <?php while ($row = $semesters->fetch_assoc()): ?>
                <option value="<?php echo $row['semester']; ?>"><?php echo $row['semester']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="genderFilter">Gender:</label>
        <select id="genderFilter">
            <option value="">All</option>
            <?php while ($row = $genders->fetch_assoc()): ?>
                <option value="<?php echo $row['gender']; ?>"><?php echo $row['gender']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="collegeFilter">College:</label>
        <select id="collegeFilter">
            <option value="">All</option>
            <?php while ($row = $colleges->fetch_assoc()): ?>
                <option value="<?php echo $row['department']; ?>"><?php echo $row['department']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="programFilter">Program:</label>
        <select id="programFilter">
            <option value="">All</option>
            <?php while ($row = $programs->fetch_assoc()): ?>
                <option value="<?php echo $row['course']; ?>"><?php echo $row['course']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="statusFilter">Status:</label>
        <select id="statusFilter">
            <option value="">All</option>
            <?php while ($row = $statuses->fetch_assoc()): ?>
                <option value="<?php echo $row['status']; ?>"><?php echo $row['status']; ?></option>
            <?php endwhile; ?>
        </select>

        <button type="button" onclick="applyFilters()">Apply Filters</button>
        <button type="button" onclick="closeFilterModal()">Cancel</button>
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
    const prelim = row.querySelector('.prelim').textContent;
    const midterm = row.querySelector('.midterm').textContent;
    const finals = row.querySelector('.finals').textContent;

    const editForm = document.getElementById('editForm');
    document.getElementById('editPrelim').value = prelim;
    document.getElementById('editMidterm').value = midterm;
    document.getElementById('editFinals').value = finals;

    editForm.setAttribute('data-grades-id', gradesId);
    editForm.setAttribute('data-school-id', schoolId);

    const editModal = document.getElementById('editModal');
    editModal.showModal();
}

// Handle edit form submission
document.getElementById('editForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const editForm = event.target;
    const gradesId = editForm.getAttribute('data-grades-id');
    const schoolId = editForm.getAttribute('data-school-id');

    // Get the grade values and set to null if empty
    const prelim = document.getElementById('editPrelim').value.trim() !== '' ? parseFloat(document.getElementById('editPrelim').value) : null;
    const midterm = document.getElementById('editMidterm').value.trim() !== '' ? parseFloat(document.getElementById('editMidterm').value) : null;
    const finals = document.getElementById('editFinals').value.trim() !== '' ? parseFloat(document.getElementById('editFinals').value) : null;

    // Prepare the form data
    const formData = new FormData();
    formData.append('grades_id', gradesId);
    formData.append('school_id', schoolId);
    formData.append('prelim', prelim);
    formData.append('midterm', midterm);
    formData.append('finals', finals);

    // Send the request to the server
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the table row with the new grades and status
            const row = document.querySelector(`tr[data-school-id="${schoolId}"]`);
            row.querySelector('.prelim').textContent = prelim !== null ? prelim.toFixed(2) : '';
            row.querySelector('.midterm').textContent = midterm !== null ? midterm.toFixed(2) : '';
            row.querySelector('.finals').textContent = finals !== null ? finals.toFixed(2) : '';
            row.querySelector('.final_grades').textContent = data.final_grades !== null ? data.final_grades.toFixed(2) : '';
            row.querySelector('.status').textContent = data.status; // Update status in the table

            // Close the modal
            const editModal = document.getElementById('editModal');
            editModal.close();

            // Show success message
            alert('Grades updated successfully!');
        } else {
            console.error('Error:', data.message);
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred. Please check the console for details.');
    });
});

// Function to update final grades and status
function updateFinalGrades(row, prelim, midterm, finals) {
    let finalGrades = null;
    if (prelim !== null && midterm !== null && finals !== null) {
        finalGrades = (prelim + midterm + finals) / 3;
        finalGrades = roundToNearestGrade(finalGrades); // Round to nearest valid grade
    }
    row.querySelector('.final_grades').textContent = finalGrades !== null ? finalGrades.toFixed(2) : '';

    // Update status based on final grades
    const statusCell = row.querySelector('.status');
    if (finalGrades !== null) {
        const status = (finalGrades >= 1 && finalGrades <= 3) ? 'PASSED' : 'FAILED';
        statusCell.textContent = status; // Update status in the table
    } else {
        statusCell.textContent = ''; // Clear status if final grades are null
    }
}

// Function to round final grades to specified values
function roundToNearestGrade(value) {
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
    
    // Set the grades_id in the hidden input for submission
    document.getElementById('gradesIdInput').value = gradesId;
    
    // Show the modal
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.showModal();
}

// Handle delete form submission
document.getElementById('deleteForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const gradesId = document.getElementById('gradesIdInput').value;
    
    // Check if gradesId is valid
    if (!gradesId || gradesId === "0") {
        alert('Invalid grades ID.');
        return;
    }
    
    // Prepare the form data
    const formData = new FormData();
    formData.append('action', 'delete_grade');
    formData.append('grades_id', gradesId);
    
    // Send the request to the server
    fetch('inputgrades.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find the row and clear all grade-related fields
            const row = document.querySelector(`tr[data-grades-id="${gradesId}"]`);
            if (row) {
                // Clear all grade fields
                row.querySelector('.prelim').textContent = '';
                row.querySelector('.midterm').textContent = '';
                row.querySelector('.finals').textContent = '';
                row.querySelector('.final_grades').textContent = '';
                row.querySelector('.status').textContent = '';
                
                // Reset the grades_id attribute
                row.setAttribute('data-grades-id', '0');
            }
            
            // Close the modal
            document.getElementById('deleteModal').close();
            
            alert('Grades deleted successfully!');
        } else {
            console.error('Error:', data.message);
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred. Please check the console for details.');
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

function openFilterModal() {
    document.getElementById('filterModal').showModal();
}

function closeFilterModal() {
    document.getElementById('filterModal').close();
}

function applyFilters() {
    const schoolIdFilter = document.getElementById('schoolIdFilter').value;
    const semesterFilter = document.getElementById('semesterFilter').value;
    const genderFilter = document.getElementById('genderFilter').value;
    const collegeFilter = document.getElementById('collegeFilter').value;
    const programFilter = document.getElementById('programFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    const table = document.getElementById("editableTable");
    const tr = table.getElementsByTagName("tr");
    let hasVisibleRows = false;

    for (let i = 1; i < tr.length; i++) {
        const row = tr[i];
        const schoolId = row.getAttribute('data-school-id');
        const semester = row.getAttribute('data-semester');
        const gender = row.getAttribute('data-gender');
        const college = row.getAttribute('data-college');
        const program = row.getAttribute('data-program');
        const status = row.getAttribute('data-status');

        const schoolIdMatch = schoolIdFilter ? schoolId.startsWith(schoolIdFilter) : true;
        const semesterMatch = semesterFilter ? semester === semesterFilter : true;
        const genderMatch = genderFilter ? gender === genderFilter : true;
        const collegeMatch = collegeFilter ? college === collegeFilter : true;
        const programMatch = programFilter ? program === programFilter : true;
        const statusMatch = statusFilter ? status === statusFilter : true;

        if (schoolIdMatch && semesterMatch && genderMatch &&  
            collegeMatch && programMatch && statusMatch) {
            row.style.display = "";
            hasVisibleRows = true;
        } else {
            row.style.display = "none";
        }
    }

    const noResultsRow = document.getElementById('noResultsRow');
    if (!hasVisibleRows) {
        noResultsRow.style.display = 'table-row';
    } else {
        noResultsRow.style.display = 'none';
    }

    closeFilterModal();
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

    csvContent += "School ID,Last Name,First Name,Program\n";

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (row.style.display !== "none") {
            const cells = row.getElementsByTagName("td");
            if (cells.length > 0) {
                const schoolId = cells[0].textContent;
                const lastName = cells[2].textContent;
                const firstName = cells[1].textContent;
                const program = cells[7].textContent;

                const csvRow = `${schoolId},${lastName},${firstName},${program}`;
                csvContent += csvRow + "\n";
            }
        }
    }

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

    let header = `
        <h1 style="text-align: center; font-size: 16px; font-weight: normal;">Republic of the Philippines</h1>
        <h1 style="text-align: center; font-size: 22px; font-weight: bold;">SOUTHERN LUZON STATE UNIVERSITY</h1>
        <h2 style="text-align: center; font-size: 16px; font-weight: bold;">MASTERS LIST</h2>
        <p style="text-align: left;">SUBJECT: The National Service Training Program</p>
        <br>
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <tr>
                <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>STUDENT NO.</i></th>
                <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>STUDENT NAME</i></th>
                <th style="border: 1px solid black; padding: 5px; text-align: center;"><i>COURSE</i></th>
            </tr>
    `;

    let html = header;

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (row.style.display !== "none") {
            const cells = row.getElementsByTagName("td");
            if (cells.length > 0) {
                const schoolId = cells[0].textContent;
                const lastName = cells[2].textContent;
                const firstName = cells[1].textContent;
                const program = cells[7].textContent;

                html += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; text-align: left;">${schoolId}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: left;">${lastName}, ${firstName}</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: left;">${program}</td>
                    </tr>
                `;
            }
        }
    }
    html += "</table>";

    const blob = new Blob([html], {
        type: 'application/msword'
    });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "students_data.doc";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}



  </script>

</body>

</html>