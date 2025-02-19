<?php
require_once("db_conn.php");
require_once 'audit_logger.php';


$conn = connect_db();
// Update the existing query that fetches students
$sql = "SELECT * FROM tbl_cwts WHERE transferred = 0 OR transferred IS NULL";
$results = $conn->query($sql);
?>


<?php
require_once 'db_conn.php';
session_start();

// Check if the session ID stored in the cookie matches the current session
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "admin")) {
  // If no valid session, redirect to login page
  header('Location: faculty.php');
  exit();
}

$conn = connect_db();
$user_id = $_SESSION['user_id'] ?? null;
?>

<?php
require_once("db_conn.php");

$conn = connect_db();

// Fetch all CWTS instructors from the database
$instructors = [];
$sql = "SELECT user_info.first_name, registration.username 
        FROM user_info 
        INNER JOIN registration 
        ON user_info.registration_id = registration.id
        WHERE registration.user_type = 'instructor' 
        AND user_info.area_assignment = 'CWTS'";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = [
            'first_name' => $row['first_name'],
            'username' => $row['username']
        ];
    }
} else {
    // Handle query error
    echo "Error: " . $conn->error;
}


?>

<?php
// In transfer_students.php
if (isset($_POST['transfer_students'])) {
  $student_ids = $_POST['student_ids'];
  $new_instructor = $_POST['new_instructor'];
  $old_instructor = $_POST['old_instructor'];
  
  // Add transfer logic
  $transfer_successful = true; // Initialize the variable
  
  try {
      // Update the students' instructor in the database
      foreach ($student_ids as $student_id) {
          $sql = "UPDATE tbl_cwts SET instructor = ? WHERE school_id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("ss", $new_instructor, $student_id);
          
          if (!$stmt->execute()) {
              $transfer_successful = false;
              break;
          }
      }
      
      // If transfer was successful, log the activity
      if ($transfer_successful) {
          logTransferActivity(
              $_SESSION['username'],
              "From: $old_instructor To: $new_instructor, Students: " . implode(', ', $student_ids)
          );
          
          echo json_encode(['success' => true, 'message' => 'Students transferred successfully']);
      } else {
          echo json_encode(['success' => false, 'message' => 'Error transferring students']);
      }
      
  } catch (Exception $e) {
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
  }
}

// Add the logging function if it doesn't exist
function logTransferActivity($username, $action) {
  global $conn;
  
  $sql = "INSERT INTO audit_log (username, action, timestamp) VALUES (?, ?, NOW())";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $username, $action);
  $stmt->execute();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="slsulogo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CWTS Students</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" type="text/css" href="cwtsStud.css">
</head>

<body>


  <!-- Add this style section after your existing header div -->
  <style>
  /* Center all table content */
  #editableTable th,
  #editableTable td {
      text-align: center !important;
      vertical-align: middle !important;
  }

  /* Ensure checkbox column stays centered */
  #editableTable td:first-child,
  #editableTable th:first-child {
      text-align: center !important;
  }

  /* Keep action buttons centered */
  #editableTable td:last-child {
      text-align: center !important;
  }

  /* Style for file input */
  input[type="file"] {
      color: black;
      background-color: white;
      padding: 5px;
      border-radius: 4px;
      width: 100%;
  }

  /* Style for the file input text */
  input[type="file"]::file-selector-button {
      background-color: #096c37;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 10px;
  }

  /* Hover effect for the button */
  input[type="file"]::file-selector-button:hover {
      background-color: #074d27;
  }
  </style>

  <table id="editableTable" class="table">
    <thead>
      <tr>
        <th><input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)"></th>
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
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="tableBody">
      <?php
      while ($rows = $results->fetch_assoc()) {
        if ($rows["nstp"] === "CWTS") {
          echo "<tr data-id='" . $rows["school_id"] . "'>";
          echo "<td><input type='checkbox' class='selectStudentCheckbox' onclick='toggleSelectionActions()'></td>";
          echo "<td>" . $rows["school_id"] . "</td>";
          echo "<td>" . $rows["last_name"] . "</td>";
          echo "<td>" . $rows["first_name"] . "</td>";
          echo "<td>" . $rows["mi"] . "</td>";
          echo "<td>" . $rows["suffix"] . "</td>";
          echo "<td>" . $rows["gender"] . "</td>";
          echo "<td>" . $rows["semester"] . "</td>";
          echo "<td>" . $rows["nstp"] . "</td>";
          echo "<td>" . $rows["department"] . "</td>";
          echo "<td>" . $rows["course"] . "</td>";
          echo "<td>";
          echo "<button class='editButton' onclick='editStudentInfo(this)'>";
          echo "<i class='fa-solid fa-pen-to-square'></i>";
          echo "</button>";
          echo "<button id='deleteBtn' class='deleteButton' onclick='deleteStudent(this)'><i class='fa-solid fa-trash'></i></button>";
          echo "<button class='assignButton' onclick='checkAndOpenConfirmPopup()'><i class='fa-solid fa-user-plus'></i></button>";
          echo "</td>";
          echo "</tr>";
        }
      }
      ?>

      <tr id="noResultsRow" style="display: none;">
        <td colspan="8" style="text-align: center; color: red;">No Results Found</td>
      </tr>
    </tbody>
  </table>


  <!-- Confirmation Popup -->
  <div id="confirmPopup" class="popup">
    <div class="popup-content">
      <h3>Assign Selection</h3>
      <p id="studentList"></p>
      <button onclick="openInstructorPopup()">Proceed to Select Instructor</button>
      <button onclick="closePopup('confirmPopup')">Cancel</button>
    </div>
  </div>


  <!-- Instructor Selection Popup -->
  <div id="instructorPopup" class="popup" style="display: none;">
    <div class="popup-content">
      <h3>Select Instructor</h3>
<select id="instructorSelect">
  <option value="">--Select Instructor--</option>
  <?php foreach ($instructors as $instructor): ?>
    <option value="<?php echo htmlspecialchars($instructor['username']); ?>">
      <?php echo htmlspecialchars($instructor['first_name']); ?>
    </option>
  <?php endforeach; ?>
</select>

      <br><br>
      <button onclick="confirmInstructor()">Confirm Instructor</button>
      <button onclick="closePopup('instructorPopup')">Cancel</button>
    </div>
  </div>



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
      } else {
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
      <header>Administrator</header>
    </header>
    <ul>
      <li><a href="homepage.php"><i class="fa-solid fa-house"></i>Homepage</a></li>
      <li><a href="dashboard.php"><i class="fas fa-qrcode"></i>Dashboard</a></li>
      <li><a href="viewgrades.php"><i class="fas fa-link"></i>View Grades</a></li>
      <li><a href="cwtsStud.php"><i class="fa-solid fa-user"></i>CWTS Students</a></li>
      <li><a href="rotcStud.php"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
      <li><a href="instructor.php"><i class="fa-regular fa-user"></i>Instructor</a></li>
      <li><a href="audit_log.php"><i class="fa-solid fa-folder-open"></i>Audit Log</a></li>
      <li><a href="#" onclick="confirmLogout()" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
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
  margin-top: -5px;
  font-size: 22px;
  color: white;
  text-align: center;
  line-height: 43.5px;
  background: #096c37;
  user-select: none;
}

    /* Sidebar links styling */
    .sidebar ul a {
      display: block;
      line-height: 65px;
      font-size: 20px;
      color: white;
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
      color: white;
      /* Set the text color to red */
    }

    /* Logout link hover effect */
    ul li:hover a.logout-link {
      padding-left: 50px;
      color: #ff5c5c;
      /* Lighter red on hover */
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
    .user-avatar {
      width: 80px;
      /* Adjust the size as needed */
      height: 80px;
      /* Keep it the same as width for a circle */
      border-radius: 50%;
      /* Makes the image circular */
      object-fit: cover;
      /* Ensures the image covers the area without distortion */
      margin-top: 11px;
      /* Center the image in the sidebar */
    }

    h2 {
      margin-bottom: 10px;
    }

    h5 {
  margin-bottom: -10px;
  margin-top: -15px;
  font-size: 20px;
}

    /*PAGINATION OF THE TABLE CSS*/

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

    /* Common dialog styling for both edit and add modals */
dialog {
    height: fit-content;
  width: 500px;  /* Increased width to match edit modal */
  padding: 20px;
  border: none;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: white;
}

/* Common form styling for both edit and add forms */
#editForm,
#addForm {
  display: flex;
  flex-direction: column;
}

#editForm label,
#addForm label {
  margin: 0px 0 2px;
}

#editForm input,
#editForm select,
#addForm input,
#addForm select {
  width: 100%;
  margin-bottom: 0px;
  padding: 4px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

#editForm button,
#addForm button {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background: white;
  cursor: pointer;
}

.addButton {
  background-color: #0a3a20; /* Match the sidebar color */
  color: white; /* Set text color to white for better contrast */

}

/* Common backdrop blur for both modals */
dialog::backdrop {
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
}

    /* Styles for the popup */
    .popup {
      display: none;
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content {
      background: white;
      padding: 20px;
      border-radius: 5px;
      text-align: center;
      width: 300px;
    }

/* Style for checkboxes */
input[type="checkbox"] {
  accent-color: #096c37;
  cursor: pointer;
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

/* Update the popup styles in your <style> tag */
.popup {
  display: none;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  text-align: center;
  width: 500px; /* Match the width of edit/add modals */
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Style for popup headings */
.popup-content h3 {
  font-size: 24px;
  margin-bottom: 20px;
  color: #333;
}

/* Style for the student list text */
#studentList {
  font-size: 16px;
  margin: 20px 0;
  line-height: 1.5;
}

/* Style for the instructor select dropdown */
#instructorSelect {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  margin: 20px 0;
  border: 1px solid #ccc;
  border-radius: 4px;
}

/* Style for popup buttons */
.popup-content button {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  transition: background-color 0.3s;
}

.popup-content button:hover {
  background-color: #f0f0f0;
}

/* Add spacing between buttons */
.popup-content button + button {
  margin-top: 10px;
}

/* Style for the student list text */
#studentList {
  font-size: 20px;  /* Increased from 16px */
  margin: 20px 0;
  line-height: 1.5;
  color: #000;  /* Set to black */
  font-weight: 500;  /* Added medium font weight for better readability */
}

.assignButton {
        background: none;
        border: none;
        padding: 6px 10px;
        text-align: center;
        display: inline-block !important; /* Force display */
        font-size: 16px;
        position: relative;
        cursor: pointer;
        border-radius: 12px;
    
    }
    
    .assignButton i {
        font-size: 18px;
        color: black;
    }
    
    /* Optional hover effect */
    .assignButton:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }
    
    .editButton:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }
    
    .deleteButton:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }
    
    /* Ensure the actions column width stays consistent */
    table td:last-child {
        white-space: nowrap;
        min-width: 120px; /* Adjust if needed */
    }

/* Style for suffix dropdowns specifically */
#editSuffix,
#addSuffix {
  width: 100%;
  padding: 4px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: white;
}

/* Style for the dropdown arrow */
#editSuffix::-ms-expand,
#addSuffix::-ms-expand {
  background-color: transparent;
  border: none;
}


/* Media queries using em or rem units instead of pixels */
@media screen and (max-width: 157.5em) { /* 2520px */
    .addButton {
        left: 26.875em; /* 430px */
    }
    #searchInput {
        left: 148.75em; /* 1980px */
    }
}

@media screen and (max-width: 132.5em) { /* 2120px */
    .addButton {
        left: 23.75em; /* 380px */
    }
    #searchInput {
        left: 128.5em; /* 1720px */
    }
}

@media screen and (max-width: 120em) { /* 1920px */
    .addButton {
        left: 22.5em; /* 360px */
    }
    #searchInput {
        left: 120em; /* 1600px */
    }
}

@media screen and (max-width: 107.5em) { /* 1720px */
    .addButton {
        left: 20.3125em; /* 325px */
    }
    #searchInput {
        left: 103.25em; /* 1380px */
    }
}

@media screen and (max-width: 100em) { /* 1600px */
    .addButton {
        left: 18.75em; /* 300px */
    }
    #searchInput {
        left: 90.25em; /* 1220px */
    }
}

@media screen and (max-width: 93.75em) { /* 1500px */
    .addButton {
        left: 17.6875em; /* 155px */
    }
    #searchInput {
        left: 80em; /* 1200px */
    }
}



</style>

<div class="content-wrapper">
  <div class="header">
    <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
    <h1>Southern Luzon State University</h1>
    <p>National Service Training Program</p>
  </div>
</div>

<div class="search-container">
  <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
  <button id="addBtn" class="addButton" onclick="openAddModal()"><i class="fa-solid fa-plus"></i></button>
</div>
<div class="pagination-container">
<button id="prevPage" onclick="prevPage()">Previous</button>
<span id="pagination"></span>
<button id="nextPage" onclick="nextPage()">Next</button>
</div>
<div class="button-container">
</div>

<!-- Add this HTML for the modal dialog inside the <body> tag -->
<dialog id="editModal">
  <form method="dialog" id="editForm">
    <h2>Edit Student Information</h2>
    <label for="editSchoolId">School ID:</label>
    <input type="text" id="editSchoolId" name="school_id" readonly><br>

    <label for="editFirstName">First Name:</label>
    <input type="text" id="editFirstName" name="first_name" required><br>

    <label for="editLastName">Last Name:</label>
    <input type="text" id="editLastName" name="last_name" required><br>

    <label for="editMI">MI:</label>
    <input type="text" id="editMI" name="mi" maxlength="2"><br>

    <label for="editSuffix">Suffix:</label>
    <select id="editSuffix" name="suffix">
    <option value="None">None</option>
      <option value="Jr.">Jr.</option>
      <option value="Sr.">Sr.</option>
      <option value="I">I</option>
      <option value="II">II</option>
      <option value="III">III</option>
      <option value="IV">IV</option>
      <option value="V">V</option>
    </select><br>

    <label for="editGender">Gender:</label>
    <select id="editGender" name="gender" required>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select><br>

    <label for="editSemester">Semester:</label>
    <select id="editSemester" name="semester" required>
      <option value="1st Semester">1st Semester</option>
      <option value="2nd Semester">2nd Semester</option>
    </select><br>

    <label for="editNSTP">NSTP:</label>
    <select id="editNSTP" name="nstp">
      <option value="CWTS">CWTS</option>
      <option value="ROTC">ROTC</option>
    </select>

    <label for="editDepartment">College:</label>
    <input type="text" id="editDepartment" name="department" required><br>

    <label for="editCourse">Program:</label>
    <input type="text" id="editCourse" name="course" required><br>

    <button type="submit">Save</button>
    <button type="button" onclick="closeModal()">Cancel</button>
  </form>
</dialog>

<!-- Add this HTML for the modal dialog inside the <body> tag -->
<dialog id="addModal">
<form method="dialog" id="addForm">
  <h2>Add New Student</h2>

  <label for="addSchoolId">School ID:</label>
  <input type="text" id="addSchoolId" name="school_id" required><br>

  <label for="addFirstName">First Name:</label>
  <input type="text" id="addFirstName" name="first_name" required><br>

  <label for="addLastName">Last Name:</label>
  <input type="text" id="addLastName" name="last_name" required><br>

  <label for="addMI">MI:</label>
  <input type="text" id="addMI" name="mi" maxlength="2"><br>

  <label for="addSuffix">Suffix:</label>
  <select id="addSuffix" name="suffix">
    <option value="None">None</option>
    <option value="Jr.">Jr.</option>
    <option value="Sr.">Sr.</option>
    <option value="I">I</option>
    <option value="II">II</option>
    <option value="III">III</option>
    <option value="IV">IV</option>
    <option value="V">V</option>
  </select><br>

  <label for="addGender">Gender:</label>
  <select id="addGender" name="gender" required>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
  </select><br>

  <label for="addSemester">Semester:</label>
  <select id="addSemester" name="semester" required>
    <option value="1st Semester">1st Semester</option>
    <option value="2nd Semester">2nd Semester</option>
  </select><br>

  <label for="addNSTP">NSTP:</label>
  <select id="addNSTP" name="nstp">
    <option value="CWTS">CWTS</option>
  </select><br>

  <label for="addDepartment">College:</label>
  <input type="text" id="addDepartment" name="department" required><br>

  <label for="addCourse">Program:</label>
  <input type="text" id="addCourse" name="course" required><br>

  <button type="submit">Save</button>
  <button type="button" onclick="closeAddModal()">Cancel</button>
</form>
</dialog>


<script>
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

let currentPage = 1;
let rowsPerPage = 35; // Changed to show 10 records per page

function paginateTable() {
    let table = document.getElementById("editableTable");
    let tbody = table.getElementsByTagName("tbody")[0];
    let tr = tbody.getElementsByTagName("tr");
    let totalRows = tr.length - 1; // excluding the "No Results Found" row
    let totalPages = Math.ceil(totalRows / rowsPerPage);

    // Ensure currentPage stays within valid range
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;

    let start = (currentPage - 1) * rowsPerPage;
    let end = start + rowsPerPage;

    // Hide all rows first
    for (let i = 0; i < tr.length - 1; i++) { // Skip the "No Results Found" row
        if (tr[i] !== document.getElementById('noResultsRow')) {
            tr[i].style.display = "none";
        }
    }

    // Show rows for current page
    for (let i = start; i < Math.min(end, totalRows); i++) {
        if (tr[i] !== document.getElementById('noResultsRow')) {
            tr[i].style.display = "";
        }
    }

    // Update buttons state
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;

    // Update pagination display
    updatePagination(totalPages);
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
    let totalRows = tbody.getElementsByTagName("tr").length - 1; // Subtract "No Results" row
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    
    if (currentPage < totalPages) {
        currentPage++;
        paginateTable();
    }
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
            paginationElement.appendChild(document.createTextNode('...'));
        }
    }

    // Add numbered page buttons
    for (let i = startPage; i <= endPage; i++) {
        addPageButton(i, paginationElement);
    }

    // Add last page button if not visible
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationElement.appendChild(document.createTextNode('...'));
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

// Make sure to initialize pagination when the page loads
document.addEventListener('DOMContentLoaded', function() {
    paginateTable();
});


// ASSIGN STUDENTS

    function openModal() {
      let modal = document.getElementById('myModal');
      modal.style.display = 'block';

      // Ensure the modal is centered
      modal.style.top = (window.innerHeight - modal.offsetHeight) / 2 + 'px';
    }

// Update the toggleSelectionActions function to only count visible checkboxes
function toggleSelectionActions() {
  const startIndex = (paginationState.currentPage - 1) * paginationState.rowsPerPage;
  const endIndex = startIndex + paginationState.rowsPerPage;
  
  const checkboxes = Array.from(document.querySelectorAll('.selectStudentCheckbox'))
    .slice(startIndex, endIndex);
    
  const selectionActions = document.getElementById('selectionActions');
  const anyChecked = checkboxes.some(checkbox => checkbox.checked);
  selectionActions.style.display = anyChecked ? 'block' : 'none';
}

// Add these functions to handle selection
function toggleSelectAll(selectAllCheckbox) {
  const table = document.getElementById("editableTable");
  const tbody = table.querySelector('tbody');
  const visibleRows = Array.from(tbody.getElementsByTagName("tr")).filter(row => 
    row.style.display !== 'none' && !row.id.includes('noResultsRow')
  );

  // Toggle checkboxes only for visible rows
  visibleRows.forEach(row => {
    const checkbox = row.querySelector('.selectStudentCheckbox');
    if (checkbox) {
      checkbox.checked = selectAllCheckbox.checked;
    }
  });

  toggleSelectionActions();
}

    // Confirm button functionality
    function confirmSelection() {
      const selectedStudents = [];
      document.querySelectorAll('.selectStudentCheckbox:checked').forEach(checkbox => {
        selectedStudents.push(checkbox.closest('tr').dataset.id);
      });
      alert("Selected Student IDs: " + selectedStudents.join(", "));
    }

    // Cancel button functionality
    function cancelSelection() {
      document.querySelectorAll('.selectStudentCheckbox').forEach(checkbox => checkbox.checked = false);
      document.getElementById('selectAllCheckbox').checked = false;
      toggleSelectionActions();
    }

    let selectedStudentIds = [];

    // Show/hide Confirm and Cancel buttons based on selection
    function toggleSelectionActions() {
const table = document.getElementById("editableTable");
const tbody = table.querySelector('tbody');
const visibleRows = Array.from(tbody.getElementsByTagName("tr")).filter(row => 
  row.style.display !== 'none' && !row.id.includes('noResultsRow')
);

const checkedBoxes = visibleRows.filter(row => 
  row.querySelector('.selectStudentCheckbox')?.checked
);

const selectionActions = document.getElementById('selectionActions');
selectionActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
}


    // Open confirmation popup
    function openConfirmPopup() {
      selectedStudentIds = Array.from(document.querySelectorAll('.selectStudentCheckbox:checked')).map(checkbox => {
        return checkbox.closest('tr').dataset.id;
      });
      const studentList = document.getElementById('studentList');
      studentList.textContent = selectedStudentIds.length > 0 ? "Selected Student IDs: " + selectedStudentIds.join(", ") : "No students selected.";
      document.getElementById('confirmPopup').style.display = 'flex';
      document.body.classList.add('blur');
    }

    // Close popup
    function closePopup(popupId) {
      document.getElementById(popupId).style.display = 'none';
      document.body.classList.remove('blur');
    }

    // Open instructor selection popup
    function openInstructorPopup() {
      closePopup('confirmPopup');
      document.getElementById('instructorPopup').style.display = 'flex';
      document.body.classList.add('blur');
    }

// Replace the existing confirmInstructor() function with this:
function confirmInstructor() {
    const instructor = document.getElementById('instructorSelect').value;
    if (!instructor) {
        alert("Please select an instructor.");
        return;
    }

    // Get all selected student IDs
    const selectedStudents = Array.from(document.querySelectorAll('.selectStudentCheckbox:checked')).map(checkbox => {
        return checkbox.closest('tr').dataset.id;
    });

    // Show loading state
    const confirmButton = document.querySelector('#instructorPopup button');  // Changed this line
    const originalText = confirmButton.textContent;
    confirmButton.textContent = 'Transferring...';
    confirmButton.disabled = true;

    // Make the AJAX call to transfer students
    fetch('transfer_students.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            instructor: instructor,
            studentIds: selectedStudents
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove transferred students from the table
            selectedStudents.forEach(studentId => {
                const row = document.querySelector(`tr[data-id="${studentId}"]`);
                if (row) row.remove();
            });
            
            // Close popups and reset checkboxes
            closePopup('instructorPopup');
            document.getElementById('selectAllCheckbox').checked = false;
            
            alert('Students successfully transferred to instructor.');
            
            // Refresh the page to update the table
            location.reload();
        } else {
            throw new Error(data.message || 'Failed to transfer students');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while transferring students: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        confirmButton.textContent = originalText;
        confirmButton.disabled = false;
    });
}

    // Cancel button functionality
    function cancelSelection() {
      document.querySelectorAll('.selectStudentCheckbox').forEach(checkbox => checkbox.checked = false);
      document.getElementById('selectAllCheckbox').checked = false;
      toggleSelectionActions();
    }


    function toggleSelectAll(selectAllCheckbox) {
          // Get the current page's visible rows only
          const currentPageRows = Array.from(document.querySelectorAll('#tableBody tr'))
              .filter(row => row.style.display !== 'none' && !row.id.includes('noResultsRow'));
      
          // Toggle checkboxes only for visible rows on the current page
          currentPageRows.forEach(row => {
              const checkbox = row.querySelector('.selectStudentCheckbox');
              if (checkbox) {
                  checkbox.checked = selectAllCheckbox.checked;
              }
          });
      
          toggleSelectionActions();
      }
      
      // Update openConfirmPopup to only get selected students from visible rows
      function openConfirmPopup() {
          // Get only the visible and checked students
          const visibleCheckedStudents = Array.from(document.querySelectorAll('#tableBody tr'))
              .filter(row => 
                  row.style.display !== 'none' && 
                  !row.id.includes('noResultsRow') && 
                  row.querySelector('.selectStudentCheckbox')?.checked
              )
              .map(row => row.dataset.id);
      
          if (visibleCheckedStudents.length === 0) {
              alert("Please select at least one student before assigning.");
              return;
          }
      
          const studentList = document.getElementById('studentList');
          studentList.textContent = "Selected Student IDs: " + visibleCheckedStudents.join(", ");
          document.getElementById('confirmPopup').style.display = 'flex';
          document.body.classList.add('blur');
      }
      
      // Update checkAndOpenConfirmPopup function
      function checkAndOpenConfirmPopup() {
          // Check only visible and checked students
          const visibleCheckedStudents = Array.from(document.querySelectorAll('#tableBody tr'))
              .filter(row => 
                  row.style.display !== 'none' && 
                  !row.id.includes('noResultsRow') && 
                  row.querySelector('.selectStudentCheckbox')?.checked
              );
          
          if (visibleCheckedStudents.length === 0) {
              alert("Please select at least one student before assigning.");
              return;
          }
          
          openConfirmPopup();
      }

        function confirmLogout() {
            if (confirm("Do you want to Logout?")) {
                window.location.href = "logout.php";
            }
        }
  
</script>

<script src="./crud_function.js"></script>
</body>

</html>