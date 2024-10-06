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
    echo json_encode([
      'success' => true,
      'grades_id' => $grades_id,
      'prelim' => $prelim,
      'midterm' => $midterm,
      'finals' => $finals
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
    g.finals
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
  if (isset($_POST['action']) && $_POST['action'] === 'delete_grade') {
    $grades_id = isset($_POST['grades_id']) ? intval($_POST['grades_id']) : 0;
    $selected_grades = isset($_POST['selected_grades']) ? json_decode($_POST['selected_grades'], true) : [];

    if ($grades_id > 0 && !empty($selected_grades)) {
      $conn = connect_db();
      $updates = [];
      $params = [];

      // Prepare the SQL updates for only the selected grades
      foreach ($selected_grades as $grade) {
        $updates[] = "$grade = NULL"; // Set the selected grade column to NULL
      }

      $sql = "UPDATE tbl_students_grades SET " . implode(', ', $updates) . " WHERE grades_id = ?";
      $stmt = $conn->prepare($sql);
      $params[] = $grades_id;

      // Bind parameters and execute
      $stmt->bind_param(str_repeat('s', count($selected_grades)) . 'i', ...array_merge(array_fill(0, count($selected_grades), null), $params));

      if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Selected grades deleted successfully']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete grades: ' . $conn->error]);
      }

      $stmt->close();
      $conn->close();
      exit;
    } else {
      echo json_encode(['success' => false, 'message' => 'Invalid request']);
      exit;
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
  <table id="editableTable" style="border-collapse: collapse; empty-cells: show;" class="table">
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
        echo "<td>";
        // Edit button
        echo "<button class='editButton' onclick='editGradesInfo(this)'><i class='fa-solid fa-pen-to-square'></i></button>";
        // Delete button
        echo "<button class='deleteButton' onclick='openDeleteModal(this)'><i class='fa-solid fa-trash'></i></button>";
        echo "</td>";
        echo "</tr>";
      }
      ?>

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
        <h2><?php echo $fetch['first_name'] . ' ' . $fetch['last_name']; ?></h2>
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
  </style>
  <!-- Search functionality -->
  <div class="search-container">
    <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
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
      <p>Select the grades you want to delete:</p>

      <div>
        <input type="checkbox" id="deletePrelim" name="gradeType[]" value="prelim">
        <label for="deletePrelim" id="deletePrelimLabel">Prelim: </label>
      </div>

      <div>
        <input type="checkbox" id="deleteMidterm" name="gradeType[]" value="midterm">
        <label for="deleteMidterm" id="deleteMidtermLabel">Midterm: </label>
      </div>

      <div>
        <input type="checkbox" id="deleteFinals" name="gradeType[]" value="finals">
        <label for="deleteFinals" id="deleteFinalsLabel">Finals: </label>
      </div>

      <!-- Hidden Action Field -->
      <input type="hidden" name="action" value="delete_grade">

      <button type="submit">Delete</button>
      <button type="button" onclick="document.getElementById('deleteModal').close()">Cancel</button>
    </form>
  </dialog>



  <script>
    // Search function
    function searchRecords() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const rows = document.querySelectorAll("#editableTable tbody tr");

      rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(" ");
        row.style.display = rowText.includes(input) ? "" : "none";
      });
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
            // Update the table row with the new grades
            const row = document.querySelector(`tr[data-school-id="${schoolId}"]`);
            row.querySelector('.prelim').textContent = data.prelim !== null ? data.prelim : '';
            row.querySelector('.midterm').textContent = data.midterm !== null ? data.midterm : '';
            row.querySelector('.finals').textContent = data.finals !== null ? data.finals : '';

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



    //DELETE BUTTON

    // Open delete modal and populate with current grades
    function openDeleteModal(button) {
      const row = button.closest('tr');
      const gradesId = row.getAttribute('data-grades-id');

      // Get current grades
      const prelim = row.querySelector('.prelim').textContent.trim();
      const midterm = row.querySelector('.midterm').textContent.trim();
      const finals = row.querySelector('.finals').textContent.trim();

      // Populate the delete modal with current grades
      document.getElementById('deletePrelimLabel').textContent = `Prelim: ${prelim || 'N/A'}`;
      document.getElementById('deleteMidtermLabel').textContent = `Midterm: ${midterm || 'N/A'}`;
      document.getElementById('deleteFinalsLabel').textContent = `Finals: ${finals || 'N/A'}`;

      // Set checkboxes based on grades
      document.getElementById('deletePrelim').checked = prelim !== '';
      document.getElementById('deleteMidterm').checked = midterm !== '';
      document.getElementById('deleteFinals').checked = finals !== '';

      // Store grades_id in the form for submission
      const deleteForm = document.getElementById('deleteForm');
      deleteForm.setAttribute('data-grades-id', gradesId);

      // Show the modal
      const deleteModal = document.getElementById('deleteModal');
      deleteModal.showModal();
    }

    // Update delete form submission handler
    document.getElementById('deleteForm').addEventListener('submit', function(event) {
      event.preventDefault();

      const deleteForm = event.target;
      const gradesId = deleteForm.getAttribute('data-grades-id');

      // Get all checked checkboxes
      const selectedGrades = Array.from(deleteForm.querySelectorAll('input[name="gradeType[]"]:checked')).map(input => input.value);

      if (selectedGrades.length === 0) {
        alert('Please select at least one grade to delete.');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'delete_grade');
      formData.append('grades_id', gradesId);
      formData.append('selected_grades', JSON.stringify(selectedGrades));

      // Send the request to the server
      fetch('', { // Adjust URL if needed
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          if (data.success) {
            const row = document.querySelector(`tr[data-grades-id="${gradesId}"]`);
            selectedGrades.forEach(grade => {
              row.querySelector(`.${grade}`).textContent = ''; // Clear the grade cell
            });

            const deleteModal = document.getElementById('deleteModal');
            deleteModal.close();

            alert('Selected grades deleted successfully!');
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('An unexpected error occurred. Please check the console for details.');
        });
    });
  </script>

</body>

</html>