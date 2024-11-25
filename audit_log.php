<?php
require_once 'db_conn.php';
require_once 'audit_functions.php';

// Check authentication
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "admin")) {
    header('Location: faculty.php');
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Use a prepared statement for the main query
$sql = "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$results = $stmt->get_result();

// Get total records for pagination
$total_records_sql = "SELECT COUNT(*) as count FROM audit_log";
$total_records_result = $conn->query($total_records_sql);
$total_records = $total_records_result->fetch_assoc()['count'];

$conn = connect_db();
$user_id = $_SESSION['user_id'] ?? null;

// Fetch audit logs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$results = getAuditLogs($page, $records_per_page);
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


    <table id="editableTable" class="table">
      <thead>
        <tr>
            <th>Audit ID</th>
            <th>User ID</th>
            <th>Action</th>
            <th>Table Name</th>
            <th>Record ID</th>
            <th>Description</th>
            <th>IP Address</th>
            <th>User Agent</th>
            <th>Timestamp</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php
        if ($results->num_rows > 0) {
            while ($row = $results->fetch_assoc()) {
                echo "<tr>";
                if (isset($row['id'])) {
                    $id = $row['id'];
                } else {
                    $id = 'N/A'; // or handle the case as needed
                }

                if (isset($row['user_id'])) {
                    $user_id = $row['user_id'];
                } else {
                    $user_id = 'NULL'; // or handle the case as needed
                }

                if (isset($row['action'])) {
                    $action = $row['action'];
                } else {
                    $action = 'No Action'; // or handle the case as needed
                }

                if (isset($row['table_name'])) {
                    $table_name = $row['table_name'];
                } else {
                    $table_name = 'NULL'; // or handle the case as needed
                }

                if (isset($row['record_id'])) {
                    $record_id = $row['record_id'];
                } else {
                    $record_id = 'NULL'; // or handle the case as needed
                }

                if (isset($row['description'])) {
                    $description = $row['description'];
                } else {
                    $description = 'No Description'; // or handle the case as needed
                }

                if (isset($row['ip_address'])) {
                    $ip_address = $row['ip_address'];
                } else {
                    $ip_address = 'No IP'; // or handle the case as needed
                }

                if (isset($row['user_agent'])) {
                    $user_agent = $row['user_agent'];
                } else {
                    $user_agent = 'No User Agent'; // or handle the case as needed
                }

                if (isset($row['created_at'])) {
                    $created_at = $row['created_at'];
                } else {
                    $created_at = 'No Date'; // or handle the case as needed
                }

                echo "<td>" . $id . "</td>";
                echo "<td>" . $user_id . "</td>";
                echo "<td>" . $action . "</td>";
                echo "<td>" . $table_name . "</td>";
                echo "<td>" . $record_id . "</td>";
                echo "<td>" . $description . "</td>";
                echo "<td>" . $ip_address . "</td>";
                echo "<td>" . $user_agent . "</td>";
                echo "<td>" . $created_at . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No audit log records found</td></tr>";
        }
        ?>

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
        /* Align to the left */
        align-items: center;
        margin-bottom: 20px;
        /* Space between pagination and table */
        margin-top: -30px;
        /* Adjust to align with the search bar and add button */
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

    /* Common dialog styling for both edit and add modals */
dialog {
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
    margin-bottom: 10px;
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

    </style>
    <div class="search-container">
      <input type="text" id="searchInput" onkeyup="searchRecords()" placeholder="Search by any column...">
    </div>

    


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
          pageButton.onclick = function() {
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
    .then(response => response.json())
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
            document.getElementById('selectionActions').style.display = 'none';
            
            alert('Students successfully transferred to instructor.');
        } else {
            alert('Failed to transfer students: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while transferring students.');
    });
}

      // Cancel button functionality
      function cancelSelection() {
        document.querySelectorAll('.selectStudentCheckbox').forEach(checkbox => checkbox.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        toggleSelectionActions();
      }

    
    </script>

    <script src="./crud_function.js"></script>
  </body>

  </html>