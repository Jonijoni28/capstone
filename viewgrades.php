<?php
require_once ("db_conn.php");

$conn = connect_db();
$sql = "SELECT 
    c.school_id, 
    c.first_name, 
    c.last_name, 
    c.gender,
    c.semester,
    c.nstp,
    c.department,
    c.course,
    c.instructor,
    COALESCE(g.grades_id, 0) AS grades_id,
    g.prelim, 
    g.midterm, 
    g.finals,
    g.status,
    CASE 
        WHEN g.prelim IS NOT NULL AND g.midterm IS NOT NULL AND g.finals IS NOT NULL 
        THEN ROUND((g.prelim + g.midterm + g.finals) / 3, 3) 
        ELSE NULL 
    END AS final_grades
FROM 
    tbl_cwts c
LEFT JOIN 
    tbl_students_grades g ON c.school_id = g.school_id";

// Execute the query and store the result
$results = $conn->query($sql);

// Check if query was successful
if (!$results) {
    die("Query failed: " . $conn->error);
}
?>
<?php
// ... existing PHP code ...

// Fetch distinct School IDs and Semesters for the filters
$schoolIds = $conn->query("SELECT DISTINCT SUBSTRING(school_id, 1, 3) AS school_prefix FROM tbl_cwts");
$semesters = $conn->query("SELECT DISTINCT semester FROM tbl_cwts");
$genders = $conn->query("SELECT DISTINCT gender FROM tbl_cwts");
$nstps = $conn->query("SELECT DISTINCT nstp FROM tbl_cwts");
$colleges = $conn->query("SELECT DISTINCT department FROM tbl_cwts");
$programs = $conn->query("SELECT DISTINCT course FROM tbl_cwts");
$instructors = $conn->query("SELECT DISTINCT instructor FROM tbl_cwts");
$statuses = $conn->query("SELECT DISTINCT status FROM tbl_students_grades");
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

    <!-- Filter Button -->
    <button class="filter-button" onclick="openFilterModal()">
    <i class="fa-solid fa-filter"></i> <!-- Assuming you're using Font Awesome for the icon -->
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

    <label for="nstpFilter">NSTP:</label>
    <select id="nstpFilter">
      <option value="">All</option>
      <?php while ($row = $nstps->fetch_assoc()): ?>
        <option value="<?php echo $row['nstp']; ?>"><?php echo $row['nstp']; ?></option>
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

    <label for="instructorFilter">Instructor:</label>
    <select id="instructorFilter">
      <option value="">All</option>
      <?php while ($row = $instructors->fetch_assoc()): ?>
        <option value="<?php echo $row['instructor']; ?>"><?php echo $row['instructor']; ?></option>
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

<table id="editableTable" class="table">
  <thead>
    <tr>
      <th>School ID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Gender</th>
      <th>Semester</th>
      <th>NSTP</th>
      <th>College</th>
      <th>Program</th>
      <th>Instructor</th>
      <th>Prelims</th>
      <th>Midterms</th>
      <th>Finals</th>
      <th>Final Grades</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody id="tableBody">
    <?php
    while ($rows = $results->fetch_assoc()) {
      echo "<tr data-grades-id='{$rows["grades_id"]}' data-school-id='{$rows["school_id"]}' data-semester='{$rows["semester"]}' data-gender='{$rows["gender"]}' data-nstp='{$rows["nstp"]}' data-college='{$rows["department"]}' data-program='{$rows["course"]}' data-instructor='{$rows["instructor"]}' data-status='{$rows["status"]}'>";
      echo "<td>{$rows["school_id"]}</td>";
      echo "<td>{$rows["first_name"]}</td>";
      echo "<td>{$rows["last_name"]}</td>";
      echo "<td>{$rows["gender"]}</td>";
      echo "<td>{$rows["semester"]}</td>";
      echo "<td>{$rows["nstp"]}</td>";
      echo "<td>{$rows["department"]}</td>";
      echo "<td>{$rows["course"]}</td>";
      echo "<td>" . ($rows["instructor"] ? $rows["instructor"] : "Not Assigned") . "</td>";
      echo "<td>{$rows["prelim"]}</td>";
      echo "<td>{$rows["midterm"]}</td>";
      echo "<td>{$rows["finals"]}</td>";
      echo "<td class='final_grades'>" . ($rows["final_grades"] !== null ? number_format($rows["final_grades"], 2) : '') . "</td>";
      echo "<td>{$rows["status"]}</td>";
      echo "</tr>";
    }
    ?>
  </tbody>
</table>

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
        <li><a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
    </ul>
</div>



<style>
  body {
    background: url('backgroundss.jpg');
    background-position: center;

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

h5 {
    margin-bottom: -10px;
    margin-top: -15px;
    font-size: 20px;
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



.filter-button {
    position: absolute; /* Use absolute positioning */
    top: 200px; /* Adjust the top position */
    left: 217px; /* Adjust the right position */
    /* You can also use left, bottom, etc. */
}

/* Style for the filter button */
.filter-button {
    font-size: 18px; /* Increase font size for the icon */
    padding: 18px; /* Add padding to increase the button size */
    width: 30px; /* Set a specific width */
    height: 20px; /* Set a specific height */

    background-color: white; /* Background color */
    color: black; /* Text/icon color */
    border: none; /* Remove border */
    cursor: pointer; /* Change cursor on hover */
    display: flex; /* Use flexbox for centering */
    align-items: center; /* Center icon vertically */
    justify-content: center; /* Center icon horizontally */
}

/* Modal Styles */
dialog {
    border: none; /* Remove default border */
    border-radius: 8px; /* Rounded corners */
    padding: 20px; /* Padding inside the modal */
    width: 400px; /* Set a fixed width */
    background-color: white; /* Background color */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Shadow effect */
}

/* Modal Header */
dialog h2 {
    margin: 0 0 15px; /* Margin for the header */
    font-size: 24px; /* Font size for the header */
    text-align: center; /* Center the header text */
}

/* Form Elements */
dialog label {
    display: block; /* Block display for labels */
    margin-bottom: 5px; /* Space below labels */
    font-weight: bold; /* Bold labels */
}

dialog select {
    width: 100%; /* Full width for select elements */
    padding: 8px; /* Padding inside select */
    margin-bottom: 10px; /* Space below select */
    border: 1px solid #ccc; /* Border for select */
    border-radius: 4px; /* Rounded corners */
}

/* Buttons */
dialog button {
    
    background-color: #096c37; /* Button background color */
    color: white; /* Button text color */
    border: none; /* Remove border */
    border-radius: 4px; /* Rounded corners */
    padding: 10px 15px; /* Padding for buttons */
    cursor: pointer; /* Pointer cursor on hover */
    margin-right: 10px; /* Space between buttons */
}

dialog button:hover {
    background-color: #0a3a20; /* Darker shade on hover */
}

/* Cancel Button */
dialog button[type="button"] {
    background-color: #ccc; /* Light gray for cancel button */
    color: black; /* Black text for cancel button */
}

dialog button[type="button"]:hover {
    background-color: #bbb; /* Darker gray on hover */
}


.table-container {
    display: flex;
    flex-direction: column; /* Stack children vertically */
    align-items: center; /* Center align children */
}

.export-container {
  position:absolute;
    margin-top: 10px; /* Space between the table and the button */
    top: 230px; /* Adjust the top position */
    left: 134px; /* Adjust the right position */
}

#exportButton {
    padding: 10px 20px; /* Button padding */
    font-size: 14px; /* Button font size */
    background-color: white; /* Button background color */
    color: black; /* Button text color */
    border: none; /* Remove border */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    font-weight: bold;
}



</style>

  <div class="search-container">
  <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
  </div>
  <div class="pagination-container">
    <button id="prevPage" onclick="prevPage()">Previous</button>
    <span id="pagination"></span>
    <button id="nextPage" onclick="nextPage()">Next</button>
</div>
  <div class="button-container">
  </div>

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
      const nstpFilter = document.getElementById('nstpFilter').value;
      const collegeFilter = document.getElementById('collegeFilter').value;
      const programFilter = document.getElementById('programFilter').value;
      const instructorFilter = document.getElementById('instructorFilter').value;
      const statusFilter = document.getElementById('statusFilter').value;

      const table = document.getElementById("editableTable");
      const tr = table.getElementsByTagName("tr");

      for (let i = 1; i < tr.length; i++) {
        const row = tr[i];
        const schoolId = row.getAttribute('data-school-id');
        const semester = row.getAttribute('data-semester');
        const gender = row.getAttribute('data-gender');
        const nstp = row.getAttribute('data-nstp');
        const college = row.getAttribute('data-college');
        const program = row.getAttribute('data-program');
        const instructor = row.getAttribute('data-instructor');
        const status = row.getAttribute('data-status');

        const schoolIdMatch = schoolIdFilter ? schoolId.startsWith(schoolIdFilter) : true;
        const semesterMatch = semesterFilter ? semester === semesterFilter : true;
        const genderMatch = genderFilter ? gender === genderFilter : true;
        const nstpMatch = nstpFilter ? nstp === nstpFilter : true;
        const collegeMatch = collegeFilter ? college === collegeFilter : true;
        const programMatch = programFilter ? program === programFilter : true;
        const instructorMatch = instructorFilter ? instructor === instructorFilter : true;
        const statusMatch = statusFilter ? status === statusFilter : true;

        if (schoolIdMatch && semesterMatch && genderMatch && nstpMatch && collegeMatch && programMatch && instructorMatch && statusMatch) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      }

      closeFilterModal(); // Close the modal after applying filters
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
    closeExportModal(); // Close the modal after selection
}

function exportToCSV() {
    const table = document.getElementById("editableTable");
    const rows = table.getElementsByTagName("tr");
    let csvContent = "data:text/csv;charset=utf-8,";

    // Add header row
    csvContent += "School ID,Last Name,First Name,Program\n";

    // Loop through table rows
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
        const row = rows[i];
        if (row.style.display !== "none") { // Check if the row is visible
            const cells = row.getElementsByTagName("td");
            if (cells.length > 0) {
                const schoolId = cells[0].textContent; // School ID
                const lastName = cells[2].textContent; // Last Name
                const firstName = cells[1].textContent; // First Name
                const program = cells[7].textContent; // Program

                // Create a CSV row
                const csvRow = `${schoolId},${lastName},${firstName},${program}`;
                csvContent += csvRow + "\n";
            }
        }
    }

    // Create a link to download the CSV file
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "students_data.csv");
    document.body.appendChild(link); // Required for Firefox
    link.click(); // This will download the data file
    document.body.removeChild(link); // Clean up
}



function exportToWord() {
    const table = document.getElementById("editableTable");
    const rows = table.getElementsByTagName("tr");

    // Create the header content
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

    // Start the table HTML
    let html = header;

    // Loop through table rows
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
        const row = rows[i];
        if (row.style.display !== "none") { // Check if the row is visible
            const cells = row.getElementsByTagName("td");
            if (cells.length > 0) {
                const schoolId = cells[0].textContent; // School ID
                const lastName = cells[2].textContent; // Last Name
                const firstName = cells[1].textContent; // First Name
                const program = cells[7].textContent; // Program

                // Create a row for the Word document
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

    // Create a new Blob with the HTML content
    const blob = new Blob([html], {
        type: 'application/msword'
    });

    // Create a link to download the Blob
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "students_data.doc"; // Name of the downloaded file
    document.body.appendChild(link);
    link.click(); // Trigger the download
    document.body.removeChild(link); // Clean up
}

  </script>
  <script src="./crud_input_grades.js"></script>
</body>

</html>