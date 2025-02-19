<?php
    require_once("dashboardPHP.php");
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
    <link rel="icon" type="image/png" href="slsulogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> 
    <link rel="stylesheet" type="text/css" href="instructor.css">
</head>

<body>
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
        <h6>Administrator</h6>
    </header>
    <ul>
        <li><a href="homepage.php"><i class="fa-solid fa-house"></i>Homepage</a></li>
        <li><a href="dashboard.php"><i class="fas fa-qrcode"></i>Dashboard</a></li>
        <li><a href="viewgrades.php"><i class="fas fa-link"></i>View Grades</a></li>
        <li><a href="cwtsStud.php"><i class="fa-solid fa-user"></i>CWTS Students</a></li>
        <li><a href="rotcStud.php"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
        <li><a href="instructor.php"><i class="fa-regular fa-user"></i>Instructor</a></li>
        <li><a href="audit_log.php"><i class="fa-solid fa-folder-open"></i>Audit Log</a></li>
        <li><a href="logout.php" onclick="return confirmLogout();" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
    </ul>
</div>

<div class="content-wrapper">
    <div class="header">
        <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>
</div>

<div class="faculty-section">

<!-- Admin Instructors Section -->
<div class="admin-instructors">
        <h2>Administrators</h2>
        <div class="admin-grid">
            <?php
           // Query for Admin instructors
// Query for Admin instructors
$admin_query = "SELECT u.id, u.photo, u.title, u.first_name, u.last_name, u.department, r.user_type, u.designation 
FROM user_info u 
LEFT JOIN registration r ON u.email = r.username 
WHERE u.designation = 'Admin'
ORDER BY u.last_name, u.first_name";
$admin_result = mysqli_query($conn, $admin_query);  
if (!$admin_result) {
    error_log('Admin query failed: ' . mysqli_error($conn));
}

while ($admin = mysqli_fetch_assoc($admin_result)) {
    echo '<div class="instructor" data-id="' . $admin['id'] . '">';
    
    // Display admin photo
    if (empty($admin['photo'])) {
        echo '<img src="default/avatar.png" alt="Default Avatar">';
    } else {
        echo '<img src="' . $admin['photo'] . '" alt="' . $admin['first_name'] . '">';
    }

    // Display admin details with bold name
    echo '<p class="name"><strong>' . $admin['title'] . ' ' . $admin['first_name'] . ' ' . $admin['last_name'] . '</strong></p>';
    echo '<p class="designation">' . $admin['department'] . '</p>';
    echo '<p class="user-type">' . ucfirst($admin['designation']) . '</p>';
    echo '</div>';
}
?>
        </div>
    </div>

    <!-- CWTS Instructors Section -->
    <div class="cwts-instructors">
        <h2>CWTS Instructors</h2>
        <div class="cwts-grid">
            <?php
            // Query for CWTS instructors
// Query for CWTS instructors
$cwts_query = "SELECT u.id, u.photo, u.title, u.first_name, u.last_name, u.department, u.area_assignment, r.user_type, u.designation 
FROM user_info u 
LEFT JOIN registration r ON u.email = r.username 
WHERE u.area_assignment = 'CWTS' AND u.designation != 'Admin'
ORDER BY u.last_name, u.first_name";
$cwts_result = mysqli_query($conn, $cwts_query);

while ($instructor = mysqli_fetch_assoc($cwts_result)) {
    echo '<div class="instructor" data-id="' . $instructor['id'] . '">';
    
    // Display instructor photo
    if (empty($instructor['photo'])) {
        echo '<img src="default/avatar.png" alt="Default Avatar">';
    } else {
        echo '<img src="' . $instructor['photo'] . '" alt="' . $instructor['first_name'] . '">';
    }

    // Display instructor details with bold name
    echo '<p class="name"><strong>' . $instructor['title'] . ' ' . $instructor['first_name'] . ' ' . $instructor['last_name'] . '</strong></p>';
    echo '<p class="designation">' . $instructor['department'] . '</p>';
    echo '<p class="user-type">' . ucfirst($instructor['designation']) . '</p>';
    echo '</div>';
}
            ?>
        </div>
    </div>

    <!-- ROTC Instructors Section -->
    <div class="rotc-instructors">
        <h2>ROTC Instructors</h2>
        <div class="rotc-grid">
            <?php
            // Query for ROTC instructors
// Query for ROTC instructors
$rotc_query = "SELECT u.id, u.photo, u.title, u.first_name, u.last_name, u.department, u.area_assignment, r.user_type, u.designation 
FROM user_info u 
LEFT JOIN registration r ON u.email = r.username 
WHERE u.area_assignment = 'ROTC' AND u.designation != 'Admin'
ORDER BY u.last_name, u.first_name";
$rotc_result = mysqli_query($conn, $rotc_query);

while ($instructor = mysqli_fetch_assoc($rotc_result)) {
    echo '<div class="instructor" data-id="' . $instructor['id'] . '">';
    
    // Display instructor photo
    if (empty($instructor['photo'])) {
        echo '<img src="default/avatar.png" alt="Default Avatar">';
    } else {
        echo '<img src="' . $instructor['photo'] . '" alt="' . $instructor['first_name'] . '">';
    }

    // Display instructor details with bold name
    echo '<p class="name"><strong>' . $instructor['title'] . ' ' . $instructor['first_name'] . ' ' . $instructor['last_name'] . '</strong></p>';
    echo '<p class="designation">' . $instructor['department'] . '</p>';
    echo '<p class="user-type">' . ucfirst($instructor['designation']) . '</p>';
    echo '</div>';
}
        ?>

        
    </div>
</div>
</div>



<!-- Add this modal structure at the end of your body -->
<div id="userTypeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Change User Type</h2>
        <form id="userTypeForm">
            <input type="hidden" id="instructorId" name="instructorId">
            <label for="userType">User Type:</label>
            <select id="userType" name="userType">
                <option value="instructor">Instructor</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </div>
</div>
</div>




  <style>

/* Main container styles */
.faculty-section {
    top: 10px;
    bottom: 10px;
    margin-top: 120px;
    padding: 20px;
    width: 75%;
    max-width: 1200px;
    position: relative;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-left: auto;
    margin-right: auto;
    overflow: visible; /* Remove inner scroll */
    height: fit-content; /* Allow content to determine height */
}

/* Remove any sidebar-related adjustments to the container */
#check:checked ~ .faculty-section {
    margin-left: auto; /* Keep it centered */
    transform: none; /* Remove transform */
}

<?php
    require_once("dashboardPHP.php");
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




  <style>

/* Main container styles */
.faculty-section {
    margin-top: 120px;
    padding: 20px;
    width: 75%;
    max-width: 1200px;
    position: relative;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-left: auto;
    margin-right: auto;
    overflow: visible; /* Remove inner scroll */
    height: auto; /* Allow content to determine height */
    min-height: calc(100vh - 140px); /* Minimum height */
}

/* Remove any sidebar-related adjustments to the container */
#check:checked ~ .faculty-section {
    margin-left: auto; /* Keep it centered */
    transform: none; /* Remove transform */
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
            position: fixed;
            cursor: pointer;
            background: #0a3a20;
            border-radius: 3px;
            z-index: 1001;
        }
        
        label #btn {
    position: sticky;
}

        /* Button to open the sidebar */
        label #btn {
            position: sticky;
            left: 20px;
            top: 130px;
            font-size: 35px;
            color: white;
            padding: 6px 12px;
            transition: all .5s;
        }

        /* Button to close the sidebar */
        label #cancel {
            position: fixed;
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
    margin-left: 250px;
}
        .user-avatar {
            width: 80px; /* Adjust the size as needed */
            height: 80px; /* Keep it the same as width for a circle */
            border-radius: 50%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            margin-top: 11px; /* Center the image in the sidebar */
        }

/* Grid layouts */
.admin-grid {
    display: grid;
    grid-template-columns: repeat(2, 280px); /* Two columns of fixed width */
    gap: 40px;
    justify-content: center;
    padding: 20px;
}

.cwts-grid, .rotc-grid {
    display: grid;
    grid-template-columns: repeat(3, 280px); /* Three columns of fixed width */
    gap: 40px;
    justify-content: center;
    padding: 20px;
}

/* Remove any duplicate styles and transitions that might affect the container */
* {
    transition: none;
}

/* Keep transitions only for sidebar and its buttons */
.sidebar, label #btn, label #cancel {
    transition: all .3s ease;
}

/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Times New Roman', Times, serif;
}

/* Header styles */
.header {
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

h1 {
    margin-top: 30px;
    margin-left: 150px;
    font-weight: bold;
}

.header p {
    margin-left: 150px;
    font-size: 20px;
    color: white;
}

.headlogo {
    width: -100%;
    height: 100px;
    float: left;
    margin-top: 10px;
    margin-left: 20px;
    margin-bottom: 10px;
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
    text-decoration: none;
}

/* Hover effect for sidebar links */
ul li:hover a {
    padding-left: 50px;
}

/* Icon styles inside sidebar */
.sidebar ul a i {
    margin-right: 16px;
}

/* Logout link hover effect */
ul li:hover a.logout-link {
    padding-left: 50px;
    color: #ff5c5c;
}

/* User avatar styles */
.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 11px;
    margin-bottom: -30px;
}

h5 {
    margin-bottom: -30px;
    margin-top: -15px;
    font-size: 21px;
    font-weight: bold;
}

h6 {
    margin-top: -30px;
    margin-top: -15px;
    font-size: 24px;
    font-weight: normal;
}


/* Sidebar toggle button */
#check {
    display: none;
}

/* Main content adjustments */
.admin-instructors, .cwts-instructors, .rotc-instructors {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 20px;
}

.admin-grid, .cwts-grid, .rotc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    width: 100%;
    margin: 0 auto;
}

.cwts-instructors {
    margin-top: 50px;
    margin-bottom: 50px;
}

.instructor {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    width: 280px; /* Fixed width */
    height: 250px; /* Fixed height */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.instructor img {
    width: 120px; /* Fixed size for images */
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
}

.instructor .name {
    font-size: 18px;
    font-weight: bold;
    margin: 10px 0 5px 0;
    line-height: 1.2;
}

.instructor .designation {
    font-size: 16px;
    color: #666;
    margin: 5px 0;
}

.instructor .user-type {
    font-size: 16px;
    color: #096c37;
    margin-top: 5px;
}



.instructor {
    background-color: #fff;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.instructor img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
}

.instructor p {
    margin: 5px 0;
    font-size: 14px;
}

.header {
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
}

h1 {
    margin-top: 30px;
    margin-left: 150px;
}

.header p{
    margin-left: 150px;
    font-size: 20px;
}

.headlogo{
    width:-100%;
    height:100px;   
    float:left;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }

 .firstlogo{
    width:-150%;
    height:150px;   
    float:right;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }

 .secondlogo{
    width:-150%;
    height:150px;   
    float:left;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }
 
.header {
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
}

body {
    background: url('backgroundss.jpg') no-repeat center center fixed;
    background-size: cover;
    overflow-y: auto; /* Enable scrolling on body */
    min-height: 100vh;
}

.navbar li a:hover {
    color:#00f974;
    transition: all 0.4s ease 0s;
}

.navbar .toggle_btn {
    display: none;
    margin-top: -120px;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.action_btn {
    margin-top: -80px;
    margin-right: 50px;
    float: right;
    background-color: rgb(21, 134, 72);
    color: #fff;
    padding: 0.5rem 2rem;
    border: none;
    outline: none;
    border-radius: 20px;
    font-size: 25px;
    font-weight: bold;
    cursor: pointer;
}

.action_btn:hover{
    scale: 1.05;
    color: #fff;
}

.action_btn:active {
    scale: 0.95
}

/* SIDE BAR MENU */

.sidebar {
    position: fixed;
    left: -250px;
    width: 250px;
    height: 100%;
    background: #096c37;
    transition: all .5s ease;
}

.sidebar header{
    font-size: 22px;
    color: white;
    text-align: center;
    line-height: 70px;
    background: 063146;
    user-select: none;
}

.sidebar ul a{
    display: block;
    height: 100%;
    width: 100%;
    line-height: 65px;
    font-size: 20px;
    color: white;
    padding-left: 40px;
    box-sizing: border-box;
    border-top: 1px solid rgba(255, 255, 255, .1);
    border-bottom: 1px solid black;
    transition: .4s;
}

ul li:hover a{
    padding-left: 50px;
}

.sidebar ul a i{
    margin-right: 16px;
}

#check{
    display: none;
}
label #btn,label #cancel{
    position: fixed;
    cursor: pointer;
    background: #0a3a20;
    border-radius: 3px;
}
label #btn{
    left: 20px;
    top: 130px;
    font-size: 35px;
    color: white;
    padding: 6px 12px;
    transition: all .3s ease;
}

label #cancel{
    z-index: 1111;
    left: -195px;
    top: 130px;
    font-size: 30px;
    color: #fff ;
    padding: 4px 9px;
    transition: all .5s ease;
}
#check:checked ~ .sidebar{
    left: 0;
}
#check:checked ~ label #btn{
    left: 250px;
    opacity: 0;
    pointer-events: none;
}
#check:checked ~ label #cancel{
    top: 180px;
    
    left: 195px;
   
}
#check:checked ~ body{
    margin-left: 0;
}

body {
    font-family: 'Times New Roman', Times, serif;
    background-color: #f0f0f0;
}

.faculty-section {
    padding: 20px;
}

h2 {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
}

.cwts-grid, .rotc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    justify-items: center;
}

.instructor {
    background-color: #fff;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.instructor img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
}

.instructor p {
    margin: 5px 0;
    font-size: 14px;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fefefe;
    padding: 30px; /* Reduced padding */
    border: 1px solid #888;
    width: 400px; /* Reduced width */
    border-radius: 12px;
    position: relative;
    margin: 0 auto;
    top: 25%;
    transform: translateY(-50%);
}

/* Modal title */
.modal-content h2 {
    margin-bottom: 25px;
    color: #0a3a20;
    font-size: 28px; /* Reduced font size */
    text-align: center;
    font-weight: bold;
}

/* Form elements */
.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 90%;
    margin: 0 auto;
}

.modal-content label {
    font-size: 20px; /* Reduced font size */
    color: #333;
    text-align: left;
}

.modal-content select {
    padding: 12px; /* Reduced padding */
    border-radius: 8px;
    border: 2px solid #ddd;
    font-size: 18px; /* Reduced font size */
    font-family: 'Times New Roman', Times, serif;
    margin: 8px 0;
}

.modal-content button {
    background-color: #0a3a20;
    color: white;
    padding: 15px; /* Reduced padding */
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 20px; /* Reduced font size */
    font-family: 'Times New Roman', Times, serif;
    margin-top: 15px;
    width: 100%;
    transition: background-color 0.3s ease;
}

.modal-content button:hover {
    background-color: #096c37;
}

/* Close button */
.close {
    position: absolute;
    right: 15px;
    top: 10px;
    color: #aaa;
    font-size: 32px; /* Reduced font size */
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #0a3a20;
}

    </style>

    <script>
// Move this function to the top of your script section
function confirmLogout() {
    if (confirm("Do you want to Logout?")) {
        window.location.replace("logout.php");
        return true;
    }
    return false;
}

// Your other existing JavaScript code...
const modal = document.getElementById('userTypeModal');
const span = document.getElementsByClassName('close')[0];

// Add click event to all instructor cards
document.querySelectorAll('.instructor').forEach(item => {
    item.addEventListener('click', event => {
        const instructorId = item.getAttribute('data-id');
        const userType = item.querySelector('.user-type').textContent.trim().toLowerCase();
        
        // Set the form values
        document.getElementById('instructorId').value = instructorId;
        document.getElementById('userType').value = userType;
        
        // Show the modal
        modal.classList.add('show');
        modal.style.display = 'flex';
    });
});

// Close modal when clicking (x)
span.onclick = function() {
    modal.classList.remove('show');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('userTypeForm').onsubmit = function(e) {
    e.preventDefault();
    const instructorId = document.getElementById('instructorId').value;
    const designation = document.getElementById('userType').value;

    // AJAX request to update user type
    fetch('update_user_type.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ instructorId, designation }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User type updated successfully!');
            location.reload(); // Reload to see changes
        } else {
            alert('Error updating user type: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating user type.');
    });

    modal.classList.remove('show');
    modal.style.display = 'none';
};  
    </script>

  

</body>
</html>