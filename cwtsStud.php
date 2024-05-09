<?php
  require_once("db_conn.php");

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
    <a href="login.html"><img src="slsulogo.png" class="headlogo"></a>
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
      <th>Depatment</th>
      <th>Course</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="tableBody">
    <?php
    while ($rows = $results->fetch_assoc()) {
      echo "<tr data-id='" . $rows["school_id"] . "'>";
      echo "<td>" . $rows["school_id"] . "</td>";
      echo "<td>" . $rows["first_name"] . "</td>";
      echo "<td>" . $rows["last_name"] . "</td>";
      echo "<td>" . $rows["gender"] . "</td>";
      echo "<td>" . $rows["nstp"] . "</td>";
      echo "<td>" . $rows["deparment"] . "</td>";
      echo "<td>" . $rows["course"] . "</td>";
      echo "<td>";
      echo "<button id=\"editBtn\" class='editButton'>Edit</button>";
      echo "<button id=\"deleteBtn\" class='deleteButton' onclick=\"deleteStudent(this)\">Delete</button>";
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
    <header>Administrator</header>
    <ul>
      <li><a href="dashboard.html"><i class="fas fa-qrcode"></i>Dashboard</a></li>
      <li><a href="viewgrades.html"><i class="fas fa-link"></i>View Grades</a></li>
      <li><a href="cwtsStud.html"><i class="fa-solid fa-user"></i>CWTS Students</i></a></li>
      <li><a href="rotcStud.html"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
      <li><a href="instructor.html"><i class="fa-regular fa-user"></i></i>Instructor</a></li>
    </ul>
  </div>
  <div class="search-container">
    <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by First Name...">
  </div>
  <div class="button-container">
  </div>

  <script src="./crud_function.js"></script>
</body>

</html>