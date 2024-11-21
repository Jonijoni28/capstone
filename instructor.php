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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    <link rel="stylesheet" type="text/css" href="instructor.css">

    <div class="header">
        <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
        </div>
        
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

<div class="faculty-section">
    <!-- CWTS Instructors Section -->


    <!-- CWTS Instructors Section -->
    <div class="cwts-instructors">
        <h2>CWTS Instructors</h2>
        <div class="cwts-grid">
            <?php
            // Query for CWTS instructors
            $cwts_query = "SELECT u.id, u.photo, u.title, u.first_name, u.last_name, u.department, u.area_assignment, r.user_type 
            FROM user_info u 
            LEFT JOIN registration r ON u.email = r.username 
            WHERE u.area_assignment = 'CWTS'";
$cwts_result = mysqli_query($conn, $cwts_query);

while ($instructor = mysqli_fetch_assoc($cwts_result)) {
 echo '<div class="instructor">';
 
 // Display instructor photo
 if (empty($instructor['photo'])) {
     echo '<img src="default/avatar.png" alt="Default Avatar">';
 } else {
     echo '<img src="' . $instructor['photo'] . '" alt="' . $instructor['first_name'] . '">';
 }

 // Display instructor details with bold name
 echo '<p class="name"><strong>' . $instructor['title'] . ' ' . $instructor['first_name'] . ' ' . $instructor['last_name'] . '</strong></p>';
 echo '<p class="designation">' . $instructor['department'] . '</p>';
 // Add null check for user_type
 echo '<p class="user-type">' . (isset($instructor['user_type']) ? ucfirst($instructor['user_type']) : 'Instructor') . '</p>';
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
            $rotc_query = "SELECT u.id, u.photo, u.title, u.first_name, u.last_name, u.department, u.area_assignment, r.user_type 
               FROM user_info u 
               LEFT JOIN registration r ON u.email = r.username 
               WHERE u.area_assignment = 'ROTC'";
$rotc_result = mysqli_query($conn, $rotc_query);

while ($instructor = mysqli_fetch_assoc($rotc_result)) {
    echo '<div class="instructor">';
    
    // Display instructor photo
    if (empty($instructor['photo'])) {
        echo '<img src="default/avatar.png" alt="Default Avatar">';
    } else {
        echo '<img src="' . $instructor['photo'] . '" alt="' . $instructor['first_name'] . '">';
    }

    // Display instructor details with bold name
    echo '<p class="name"><strong>' . $instructor['title'] . ' ' . $instructor['first_name'] . ' ' . $instructor['last_name'] . '</strong></p>';
    echo '<p class="designation">' . $instructor['department'] . '</p>';
    // Add null check for user_type
    echo '<p class="user-type">' . (isset($instructor['user_type']) ? ucfirst($instructor['user_type']) : 'Instructor') . '</p>';
    echo '</div>';
}
        ?>

        
    </div>
</div>
</div>


  <style>

* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header {
            overflow: hidden;
            background-color: #0a3a20;
            color: white;
            padding: 10px;
        }

        .header h1 {
            margin-left: 140px;
            margin-top: 20px;
        }

        .header p {
            margin-left: 140px;
            font-size: 20px;
        }

        .headlogo {
            width: 100px;
            height: 100px;
            float: left;
            margin-left: 10px;
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
    transition: .4s;
    text-decoration: none; /* Remove underlines */
    border-bottom: 1px solid black; 
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


.faculty-section {
            padding: 20px;
            background-color: #fff; /* White background for the section */
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faculty-section h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .cwts-grid, .rotc-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px; /* Spacing between items */
        }

        .instructor {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            width: 200px; /* Fixed width for instructor containers */
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease; /* Animation for hover */
        }

        .instructor img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .instructor p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .instructor:hover {
            transform: translateY(-5px); /* Slight lift on hover */
        }

        @media (max-width: 768px) {
            .instructor {
                width: calc(50% - 20px); /* Adjust for smaller screens */
            }
        }

        @media (max-width: 480px) {
            .instructor {
                width: 100%; /* Full width for very small screens */
            }
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
    font-size: 2    0px;
}

.instructor {
    background: #fff;
    padding: 15px; /* Reduced from 20px to 15px */
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
    margin: 5px; /* Added small margin */
}

.instructor .title .first_name .last_name{
    font-weight: bold;
}

.instructor img {
    width: 120px; /* Reduced from 150px to 120px */
    height: 120px; /* Reduced from 150px to 120px */
    border-radius: 50%;
    margin-bottom: 10px; /* Reduced from 15px to 10px */
    object-fit: cover;
}

.instructor p {
    margin: 3px 0; /* Reduced margin between text elements */
    font-size: 0.9em; /* Slightly smaller text */
}

.instructor .name {
    font-weight: bold;
    font-size: 1em;
    margin: 8px 0 3px 0;
}

.instructor .designation {
    color: #666;
    margin: 3px 0;
    font-size: 0.9em;
}

.cwts-grid, .rotc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Reduced from 250px to 200px */
    gap: 15px; /* Reduced from 30px to 15px */
    padding: 10px; /* Reduced from 20px to 10px */
    max-width: 1000px; /* Add max-width to keep cards from spreading too far */
    margin: 0 auto; /* Center the grid */
    margin-bottom: 50px;
    margin-top: 20px;
}

    </style>

  

</body>
    </head>
</html>