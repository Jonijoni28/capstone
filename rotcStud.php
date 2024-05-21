<?php
require_once ("db_conn.php");

$conn = connect_db();
$sql = "SELECT * FROM tbl_cwts";
$results = $conn->query($sql);
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
  <div class="navbar">
    <a href="#" class="action_btn">Administrator</a>
    <div class="toggle_btn">
      <i class="fa-solid fa-bars"></i>
    </div>
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
    if ($rows["nstp"] === "ROTC") {
        echo "<tr data-id='" . $rows["school_id"] . "'>";
        echo "<td>" . $rows["school_id"] . "</td>";
        echo "<td>" . $rows["first_name"] . "</td>";
        echo "<td>" . $rows["last_name"] . "</td>";
        echo "<td>" . $rows["gender"] . "</td>";
        echo "<td>" . $rows["nstp"] . "</td>";
        echo "<td>" . $rows["deparment"] . "</td>";
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
    <header>Administrator</header>
    <ul>
      <li><a href="homepage.php"><i class="fa-solid fa-house"></i></i>Homepage</a></li>
      <li><a href="dashboard.php"><i class="fas fa-qrcode"></i>Dashboard</a></li>
      <li><a href="viewgrades.php"><i class="fas fa-link"></i>View Grades</a></li>
      <li><a href="cwtsStud.php"><i class="fa-solid fa-user"></i>CWTS Students</i></a></li>
      <li><a href="rotcStud.php"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
      <li><a href="instructor.php"><i class="fa-regular fa-user"></i></i>Instructor</a></li>
    </ul>
  </div>
  <div class="search-container">
    <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by First Name...">
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
      <select id="editNSTP" name="nstp" required>
        <option value="ROTC">ROTC</option>
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
        <option value="ROTC">ROTC</option>
      </select><br>

      <label for="addDepartment">Department:</label>
      <input type="text" id="addDepartment" name="department" required><br>

      <label for="addCourse">Course:</label>
      <input type="text" id="addCourse" name="course" required><br>

      <button type="submit">Save</button>
      <button type="button" onclick="closeAddModal()">Cancel</button>
    </form>
  </dialog>

  <script src="./crud_function.js"></script>
</body>

</html>