<?php
require_once("db_conn.php");

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
        $finalGrades = round($finalGrades, 3); // Round to 3 decimal places

        // Update final grades in the database
        $updateFinalsStmt = $conn->prepare("UPDATE tbl_students_grades SET final_grades = ? WHERE grades_id = ?");
        $updateFinalsStmt->bind_param("di", $finalGrades, $grades_id);
        
        if (!$updateFinalsStmt->execute()) {
            error_log("Failed to update final grades: " . $updateFinalsStmt->error);
        }
        $updateFinalsStmt->close();
    }

    echo json_encode([
      'success' => true,
      'grades_id' => $grades_id,
      'prelim' => $prelim,
      'midterm' => $midterm,
      'finals' => $finals,
      'final_grades' => $finalGrades // Include final grades in the response
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
  }

  $stmt->close();
  $conn->close();
  exit;


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
  $sql = "SELECT 
      c.school_id, 
      c.first_name, 
      c.last_name, 
      c.gender,
      c.nstp,
      c.department,
      c.course,
      COALESCE(g.grades_id, 0) AS grades_id,
      g.prelim, 
      g.midterm, 
      g.finals,
      -- Calculate final grades only if all grades are filled
      CASE 
          WHEN g.prelim IS NOT NULL AND g.midterm IS NOT NULL AND g.finals IS NOT NULL 
          THEN ROUND((g.prelim + g.midterm + g.finals) / 3, 3) 
          ELSE NULL 
      END AS final_grades
  FROM 
      tbl_cwts c
  LEFT JOIN 
      tbl_students_grades g
  ON 
      c.school_id = g.school_id";
  $results = $conn->query($sql);
  ?>

<!-- #region -->
<?php
require_once("db_conn.php");

// Data processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Existing code...

  // Handling delete requests
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete_grade') {
    $grades_id = isset($_POST['grades_id']) ? intval($_POST['grades_id']) : 0;

    if ($grades_id > 0) {
        // Prepare the SQL to set the grades to NULL for the specific grades_id
        $sql = "UPDATE tbl_students_grades SET prelim = NULL, midterm = NULL, finals = NULL WHERE grades_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $grades_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Grades deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete grades: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid grades ID.']);
    }
}

}

?>

<?php
require_once 'db_conn.php';
session_start();

// Check if the session ID stored in the cookie matches the current session
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "instructor")) {
    // If no valid session, redirect to login page
    header('Location: faculty.php');
    exit();
}

$conn = connect_db();
$user_id = $_SESSION['user_id'] ?? null;
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
        <th>NSTP</th>
        <th>Department</th>
        <th>Course</th>
        <th>Prelims</th>
        <th>Midterms</th>
        <th>Finals</th> 
        <th>Final Grades</th> 
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="tableBody">
      <?php
      while ($rows = $results->fetch_assoc()) {
        echo "<tr data-grades-id='{$rows["grades_id"]}' data-school-id='{$rows["school_id"]}'>";
        echo "<td>{$rows["school_id"]}</td>";
        echo "<td>{$rows["first_name"]}</td>";
        echo "<td>{$rows["last_name"]}</td>";
        echo "<td>{$rows["gender"]}</td>";
        echo "<td>{$rows["nstp"]}</td>";
        echo "<td>{$rows["department"]}</td>";
        echo "<td>{$rows["course"]}</td>";
        echo "<td class='prelim'>{$rows["prelim"]}</td>";
        echo "<td class='midterm'>{$rows["midterm"]}</td>";
        echo "<td class='finals'>{$rows["finals"]}</td>";
        echo "<td class='final_grades' style='font-weight: bold;'>" . ($rows["final_grades"] !== null ? number_format($rows["final_grades"], 3) : '') . "</td>";
        echo "<td>";
        // Edit button
        echo "<button class='editButton' onclick='editGradesInfo(this)'><i class='fa-solid fa-pen-to-square'></i></button>";
        // Delete button
        echo "<button class='deleteButton' onclick='openDeleteModal(this)'><i class='fa-solid fa-trash'></i></button>";
        echo "</td>";
        echo "</tr>";
      }
      ?>
<tr id="noResultsRow" style="display: none;">
      <td colspan="8" style="text-align: center; color: red;">No Results Found</td>
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
    background: url('greens.jpg');
    background-position: center;
    background-size: cover; 
  }

  .user-avatar {
    width: 80px; /* Adjust the size as needed */
    height: 80px; /* Keep it the same as width for a circle */
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image covers the area without distortion */
    margin-top: 11px; /* Center the image in the sidebar */
}

h2{
    margin-top: -30px;
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
    justify-content: center; /* Align to the left */
    align-items: center;
    margin-bottom: 20px; /* Space between pagination and table */
    margin-top: -30px;  /* Adjust to align with the search bar and add button */
}

.pagination-container button {
    margin: 0 5px;
    padding: 5px 10px;
    border: none;
    background-color: #096c37;
    color: white;
    cursor: pointer;
}

.pagination-container button.active {
    background-color: #0a3a20;
}

.pagination-container button[disabled] {
    background-color: grey;
    cursor: not-allowed;
}

.page-button {
    padding: 5px 10px;
    margin: 0 5px;
    cursor: pointer;
}

.page-button.active {
    background-color: #0a3a20;
    color: white;
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

  <!-- Modal dialogs -->
  <dialog id="editModal">
    <form method="dialog" id="editForm">
      <h2>Edit Student Grades</h2>
      <label for="editPrelims">Prelims:</label>
      <input type="number" id="editPrelim" name="prelim" min="1.000" max="5.000" step="0.250"><br>

      <label for="editMidterm">Midterms:</label>
      <input type="number" id="editMidterm" name="midterm" min="1.000" max="5.000" step="0.250"><br>

      <label for="editFinals">Finals:</label>
      <input type="number" id="editFinals" name="finals" min="1.000" max="5.000" step="0.250"><br>

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

 <!-- Delete Modal -->
<dialog id="deleteModal">
    <form id="deleteForm">
        <h2>Delete Student Grades</h2>
        <p>Are you sure you want to delete all grades for this student?</p>

        <!-- Hidden Action Field -->
        <input type="hidden" name="action" value="delete_grade">

        <!-- This field will hold the grades_id for submission -->
        <input type="hidden" name="grades_id" id="gradesIdInput">

        <button type="submit">Delete All Grades</button>
        <button type="button" onclick="document.getElementById('deleteModal').close()">Cancel</button>
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
    fetch('', { // The same PHP file to process form submissions
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the table row with the new grades formatted to three decimal places
            const row = document.querySelector(`tr[data-school-id="${schoolId}"]`);
            row.querySelector('.prelim').textContent = prelim !== null ? prelim.toFixed(3) : '';
            row.querySelector('.midterm').textContent = midterm !== null ? midterm.toFixed(3) : '';
            row.querySelector('.finals').textContent = finals !== null ? finals.toFixed(3) : '';

            // Recalculate and update final grades
            updateFinalGrades(row, prelim, midterm, finals);

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

// Handle delete form submission
document.getElementById('deleteForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const gradesId = document.getElementById('gradesIdInput').value;

    // Prepare the form data
    const formData = new FormData();
    formData.append('action', 'delete_grade');
    formData.append('grades_id', gradesId);

    // Send the request to the server
    fetch('', { // The same PHP file to process form submissions
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the table row to clear the grades
            const row = document.querySelector(`tr[data-grades-id="${gradesId}"]`);
            if (row) {
                // Clear the grades from the row
                row.querySelector('.prelim').textContent = '';
                row.querySelector('.midterm').textContent = '';
                row.querySelector('.finals').textContent = '';
                // Clear the final grades as well
                row.querySelector('.final_grades').textContent = '';
            }

            // Close the modal
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.close();

            // Show success message
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
    row.querySelector('.final_grades').textContent = finalGrades !== null ? finalGrades.toFixed(3) : '';
}




    //DELETE BUTTON

    // Open delete modal and populate with grades ID
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
// Handle delete form submission
document.getElementById('deleteForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const gradesId = document.getElementById('gradesIdInput').value;

    // Prepare the form data
    const formData = new FormData();
    formData.append('action', 'delete_grade');
    formData.append('grades_id', gradesId);

    // Send the request to the server
    fetch('', { // The same PHP file to process form submissions
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the table row to clear the grades
            const row = document.querySelector(`tr[data-grades-id="${gradesId}"]`);
            if (row) {
                // Clear the grades from the row
                row.querySelector('.prelim').textContent = '';
                row.querySelector('.midterm').textContent = '';
                row.querySelector('.finals').textContent = '';
            }

            // Close the modal
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.close();

            // Show success message
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
let rowsPerPage = 2;

function paginateTable() {
    let table = document.getElementById("editableTable");
    let tr = table.getElementsByTagName("tr");
    let totalRows = tr.length - 2; // excluding the header row and "No Results Found" row
    let totalPages = Math.ceil(totalRows / rowsPerPage);

    let start = (currentPage - 1) * rowsPerPage + 1; // skip the header row
    let end = start + rowsPerPage - 1;

    // Show only the rows for the current page
    for (let i = 1; i < tr.length - 1; i++) {
        if (i >= start && i <= end) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }

    // Disable/Enable Previous and Next buttons
    document.getElementById('prevPage').disabled = (currentPage === 1);
    document.getElementById('nextPage').disabled = (currentPage === totalPages);

    // Update the pagination display
    updatePagination(totalPages);
}

function updatePagination(totalPages) {
    let paginationElement = document.getElementById('pagination');
    paginationElement.innerHTML = "";

    // Create pagination buttons
    for (let i = 1; i <= totalPages; i++) {
        let pageButton = document.createElement("button");
        pageButton.innerHTML = i;
        pageButton.classList.add('page-button');
        if (i === currentPage) {
            pageButton.classList.add('active');
        }
        pageButton.onclick = function () {
            currentPage = i;
            paginateTable();
        };
        paginationElement.appendChild(pageButton);
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
    let totalRows = table.getElementsByTagName("tr").length - 2;
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        paginateTable();
    }
}

// Initialize pagination on page load
window.onload = function() {
    paginateTable();
};

  </script>

</body>

</html>