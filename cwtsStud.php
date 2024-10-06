<?php
require_once ("db_conn.php");

$conn = connect_db();
$sql = "SELECT * FROM tbl_cwts";
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
    <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
    <h1>Southern Luzon State University</h1>
    <p>National Service Training Program</p>
  </div>
  
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
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="tableBody">
      <?php
      while ($rows = $results->fetch_assoc()) {
        if ($rows["nstp"] === "CWTS") {
          echo "<tr data-id='" . $rows["school_id"] . "'>";
          echo "<td>" . $rows["school_id"] . "</td>";
          echo "<td>" . $rows["first_name"] . "</td>";
          echo "<td>" . $rows["last_name"] . "</td>";
          echo "<td>" . $rows["gender"] . "</td>";
          echo "<td>" . $rows["nstp"] . "</td>";
          echo "<td>" . $rows["department"] . "</td>";
          echo "<td>" . $rows["course"] . "</td>";
          echo "<td>";
          echo "<button id='editBtn' class='editButton' onclick='editStudentInfo(this)'><i class='fa-solid fa-pen-to-square'></i></button>";
          echo "<button id='deleteBtn' class='deleteButton' onclick='deleteStudent(this)'><i class='fa-solid fa-trash'></i></button>";
          echo "</td>";
          echo "</tr>";
        }
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
        <header>Administrator</header>
    </header>
    <ul>
        <li><a href="homepage.php"><i class="fa-solid fa-house"></i>Homepage</a></li>
        <li><a href="dashboard.php"><i class="fas fa-qrcode"></i>Dashboard</a></li>
        <li><a href="viewgrades.php"><i class="fas fa-link"></i>View Grades</a></li>
        <li><a href="cwtsStud.php"><i class="fa-solid fa-user"></i>CWTS Students</a></li>
        <li><a href="rotcStud.php"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
        <li><a href="instructor.php"><i class="fa-regular fa-user"></i>Instructor</a></li>
        <li><a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
    </ul>
</div>


  <style>
    body {
    background: url('greens.jpg') no-repeat;
    background-position: center;
    background-size: cover;
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

  </style>
  <div class="search-container">
  <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
    <button id="addBtn" class="addButton" onclick="openAddModal()"><i class="fa-solid fa-plus"></i></button>
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

      <label for="editGender">Gender:</label>
      <select id="editGender" name="gender" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select><br>

      <label for="editNSTP">NSTP:</label>
      <select id="editNSTP" name="nstp">
        <option value="CWTS">CWTS</option>
      </select>

      <label for="editDepartment">Department:</label>
      <input type="text" id="editDepartment" name="department" required><br>

      <label for="editCourse">Course:</label>
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

      <label for="addGender">Gender:</label>
      <select id="addGender" name="gender" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select><br>

      <label for="addNSTP">NSTP:</label>
      <select id="addNSTP" name="nstp">
        <option value="CWTS">CWTS</option>
      </select><br>

      <label for="addDepartment">Department:</label>
      <input type="text" id="addDepartment" name="department" required><br>

      <label for="addCourse">Course:</label>
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

  // Loop through all rows except the header
  for (let i = 1; i < tr.length; i++) {
    let row = tr[i];
    let cells = row.getElementsByTagName("td");
    let textContent = "";
    // Concatenate text from desired columns
    for (let j = 0; j < cells.length; j++) {
      // Only add columns that are relevant to the search
      if (j === 0 || j === 1 || j === 2 || j === 3 || j === 4 || j === 5 || j === 6) { // Indices of the columns School ID, First Name, etc.
        textContent += cells[j].textContent || cells[j].innerText;
      }
    }
    // Check if row should be displayed
    if (textContent.toUpperCase().indexOf(filter) > -1) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }
}

  </script>

  <script src="./crud_function.js"></script>
</body>

</html>