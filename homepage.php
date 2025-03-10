<?php
require_once 'db_conn.php';
require_once 'audit_logger.php';

session_start();

// Check if the session ID stored in the cookie matches the current session
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "admin")) {
    // If no valid session, redirect to login page
    header('Location: faculty.php');
    exit();
}

$conn = connect_db();
$user_id = $_SESSION['user_id'] ?? null;

// Function to submit an announcement
function submitAnnouncement() {
    global $conn;


    // Get form data
    $title = $_POST['title'];
    $who = $_POST['audience'];
    $what = $_POST['what'];
    $when = $_POST['date'];
    $where = $_POST['location'];
    $attire = $_POST['attire'];
    $note = $_POST['note'];
    $announced_by = $_POST['announced_by'];
    $image = $_FILES['image']['name'] ? 'uploads/' . basename($_FILES['image']['name']) : '';

    if ($image) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
            echo '<script>alert("Image uploaded successfully!");</script>';
        } else {
            echo '<script>alert("Image upload failed!");</script>';
        }
    }

    // SQL Insert
    $stmt = $conn->prepare("INSERT INTO announcement (title, audience, what, date, location, attire, note, announced_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $who, $what, $when, $where, $attire, $note, $announced_by, $image);

    // Execute the statement
    if ($stmt->execute()) {
        echo '<script>alert("Announcement added successfully!");</script>';
    } else {
        echo '<script>alert("Error: ' . $stmt->error . '");</script>';
    }

    // Close the statement
    $stmt->close();
}



// Function to fetch announcements
function fetchAnnouncements() {
    global $conn;

    // Modified query to order by ID in descending order (assuming ID increases with newer announcements)
    $result = $conn->query("SELECT * FROM announcement ORDER BY id DESC");
    if (!$result) {
        return [];
    }

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }

    return $announcements;
}

// Function to fetch and display announcements
function displayAnnouncements() {
    global $conn;

    // Modified query to order by ID in descending order
    $result = $conn->query("SELECT * FROM announcement ORDER BY id DESC");
    if (!$result) {
        echo '<p style="text-align:center; font-style:italic;">*Error fetching announcements*</p>';
        return;
    }

    if ($result->num_rows === 0) {
        echo '<p style="text-align:center; font-style:italic;">*No Further Announcements*</p>';
        return;
    }

    // Display the announcements
    while ($row = $result->fetch_assoc()) {
        echo '
            <div class="announcement">
                <h4>Title: ' . htmlspecialchars($row['title']) . '</h4>
                <p>Who: ' . htmlspecialchars($row['audience']) . '</p>
                <p>What: ' . htmlspecialchars($row['what']) . '</p>
                <p>When: ' . htmlspecialchars($row['date']) . '</p>
                <p>Where: ' . htmlspecialchars($row['location']) . '</p>
                <p>Attire: ' . htmlspecialchars($row['attire']) . '</p>
                <p>Note: ' . htmlspecialchars($row['note']) . '</p>
                <p>Announced By: ' . htmlspecialchars($row['announced_by']) . '</p>
                ' . ($row['image'] ? '<img src="' . htmlspecialchars($row['image']) . '" style="width:100%;height:auto;"/>' : '') . '
                <button class="delete-btn" onclick="deleteAnnouncement(' . $row['id'] . ')">Delete</button>
            </div>
        ';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    submitAnnouncement();
}
?>

<?php
// In announcement_management.php
if (isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Your existing announcement addition logic here
    
    if ($announcement_added) {
        logAnnouncementActivity(
            $_SESSION['username'],
            'ADD',
            "Title: $title"
        );
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="slsulogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSTP Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="homepage.css">

</head>

<body>

<div id="preloader">
        <img src="slsulogo.png" alt="Logo" class="preloader-logo">
        <div class="progress-container">
            <div id="progress-bar"></div>
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
        <li><a href="#" onclick="confirmLogout()" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
    </ul>
</div>


    <div class="top-right-buttons">
        <button onclick="viewAnnouncement()"><i class="fa-solid fa-book fa-xl"></i></button>
        <button id="announceButton" onclick="showAnnouncementPopup()"><i class="fa-solid fa-bullhorn fa-xl"></i></button>
    </div>

    <!-- Blurred background overlay -->
    <div id="blurOverlay" class="blur-overlay"></div>

  <!-- Popup content for adding announcements -->
  <div id="announcementPopup" class="popup">
    <div class="popup-content">
        <h3>Add Announcement/News</h3>
        <form method="POST" enctype="multipart/form-data" onsubmit="handleSubmit(event)"> <!-- Ensure this calls handleSubmit -->
            <div class="input-section">
                <label>Title</label>
                <input type="text" name="title" placeholder="Enter title" required>
            </div>
            <div class="input-section">
                <label>Who</label>
                <input type="text" name="audience" placeholder="Enter audience" required>
            </div>
            <div class="input-section">
                <label>What</label>
                <input type="text" name="what" placeholder="Enter announcement title" required>
            </div>
            <div class="input-section">
                <label>When</label>
                <input type="text" name="date" placeholder="Enter date and time" required>
            </div>
            <div class="input-section">
                <label>Where</label>
                <input type="text" name="location" placeholder="Enter location" required>
            </div>
            <div class="input-section">
                <label>Attire</label>
                <textarea name="attire" placeholder="Enter attire details"></textarea>
            </div>
            <div class="input-section">
                <label>Note</label>
                <textarea name="note" placeholder="Enter additional notes"></textarea>
            </div>
            <div class="input-section">
                <label>Announced By</label>
                <input type="text" name="announced_by" placeholder="Enter name of announcer" required>
            </div>
            <div class="input-section">
                <label>Upload Image</label>
                <input type="file" name="image" id="imageUpload"> <!-- Ensure this has an ID -->
            </div>
            <div class="buttons">
                <button type="button" class="cancel-btn" onclick="hideAnnouncementPopup()">Cancel</button>
                <button type="submit" class="upload-btn">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- View Announcements -->
<div id="viewAnnouncementPopup" class="popup">
    <div class="popup-content">
        <!-- Add the X button here -->
        <span class="close-x" onclick="hideViewAnnouncementPopup()">&times;</span>
        <h3>View Announcements</h3>
        <div id="announcementsDisplay">
            <?php
            displayAnnouncements();
            ?>
        </div>
        <div class="buttons">
            <button class="cancel-btn" onclick="hideViewAnnouncementPopup()">Cancel</button>
        </div>
    </div>
</div>

    <style>
        body {
            background-color: white;
            /* Set background to white */
            background-image: none;
            /* Ensure no background image is applied */
            margin: 0;
        }

         /* Preloader styles */
         #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('./PICTURES/abtusbg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            transition: opacity 0.5s ease;
        }

        #preloader::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 128, 0, 28); /* Semi-transparent green overlay */
            backdrop-filter: blur(5px); /* Optional: adds a blur effect for a glass-like appearance */
            z-index: 1;
        }

        .preloader-logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
            animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite alternate;
            position: relative;
            z-index: 2;
        }



        .progress-container {
            width: 200px;
            height: 5px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        #progress-bar {
            width: 0;
            height: 100%;
            background-color: white;
            transition: width 0.02s linear;
        }

        #main-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        #main-content.show {
            opacity: 1;
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

        /* Profile */

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
    margin-top: -20px;
    font-size: 20px;
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
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
    width: 100%;
    position: relative;
    transition: all .5s ease;
}

/* Adjust the header shift when sidebar opens */
#check:checked ~ .content-wrapper .header {
    margin-left: 250px;
    width: calc(100% - 48px); /* This prevents the header from stretching */
}

/* Content wrapper styles */
.content-wrapper {
    transition: all .5s ease;
    position: relative;
    width: 100%;
    margin-left: 0;
    z-index: 1;
}

/* Remove any conflicting margin adjustments */
#check:checked ~ .content-wrapper {
    margin-left: 0; /* Remove this if it exists */
}

/* Remove any body margin adjustments */
#check:checked ~ body {
    margin-left: 0;
}

/* Header logo and text styles */
.headlogo {
    width: 100px;   
    height: 100px;   
    float: left;
    margin: 10px 20px;
}

.header h1 {
    margin-left: 150px;
    margin-top: 30px;
}

.header p {
    margin-left: 150px;
    font-size: 20px;
    color: white;
}

        .menu .profile .btn,
        .menu .profile .delete-btn {
            padding: 2px 7px 2px 7px;
        }

        .menu .profile {
            margin-top: 60px;
            padding: 10px;
            text-align: center;
            width: 260px;
            border-radius: 5px;
            position: fixed;
        }

        .menu .profile h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #575757;
        }

        .menu .profile a {
            font-size: 12px;
            text-align: center;
            color: white;
            text-decoration: none;
            font-weight: 300%;
        }

        .menu .profile a:hover {
            text-decoration: underline;
            background-color: #D323C2;
        }

        .menu .profile img {
            height: 80px;
            width: 85px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 5px;
        }


        /* SIDEBAR END */

        /* SLIDESHOW CSS START */

        .slide img {
            width: auto;
            /* Maintain the aspect ratio of the images */
            height: 100%;
            /* Scale the height to fit the container */
            z-index: -1000;
            max-width: 100%;
            /* Ensure the width doesn't exceed the container */
            object-fit: contain;
            /* Ensure images fit the container without distortion */
            display: block;
            margin: auto;
        }

        .slideshow-container {
            position: relative;
            max-width: 100%;
            z-index: -1000;
            height: 550px;
            /* Set a fixed height for the slideshow */
            margin: auto;
            margin-bottom: 50px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            /* Center vertically */
        }

        .slide img {
            z-index: -1000;
            max-width: 100%;
            /* Ensure the width doesn't exceed the container */
            max-height: 100%;
            /* Ensure the height doesn't exceed the container */
            object-fit: contain;
            /* Maintain the aspect ratio and fit the image within the container */
            display: block;
            margin: auto;
        }

        .slide::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            /* Adjust this height based on how much of the bottom you want to fade */
            background: linear-gradient(to top, rgba(0, 128, 0, 0.7), transparent);
            /* Green gradient effect */
            pointer-events: none;
            /* Ensure the overlay doesn't affect interactivity */
        }

        .text {
            position: absolute;
            bottom: 20%;
            /* Align the text higher */
            left: 5%;
            /* Align the text on the left side */
            color: white;
            font-family: 'Arial', serif;
            /* Match formal font style */
            font-weight: bold;
            /* Bold font */
            letter-spacing: 1px;
            padding: 0;
            margin: 0;
            color: #096c37;
            z-index: 10;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            /* Stronger text shadow */
            line-height: 1.2;
            /* Line height to match the spacing */
        }

        .text h1 {
            margin: 0;
            font-size: 4rem;
            /* Adjusted font size */
            -webkit-text-stroke: 2px white;
            /* Outline effect */
            padding: 0;
            text-align: left;
            /* Align text to the left */
        }

        .text p {
            font-size: 1.5rem;
            /* Subtitle smaller text */
            font-weight: bold;
            color: white;
            font-family: sans-serif;
            /* Match formal font style */
            -webkit-text-stroke: 1px black;
            /* Outline effect */
            margin: 10px 0 0 0;
            /* Space between title and subtitle */
            text-align: left;
            /* Align text to the left */
            letter-spacing: 1px;
        }

        @keyframes fade {
            from {
                opacity: .4
            }

            to {
                opacity: 1
            }
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        /* ANNOUNCEMENT CSS BUTTON START */
        .top-right-buttons {
            position: absolute;
            top: 130px;
            /* Adjust as per your page design */
            right: 10px;
        }

        .top-right-buttons button {
            margin-left: 10px;
            /* Space between buttons */
            padding: 15px 20px;
            background-color: #096c37;
            /* Green */
            border: none;
            color: white;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .top-right-buttons button:hover {
            background-color: #45a049;
        }

        /* Blur Overlay */
        .blur-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }

        /* Popup */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 9999;
            overflow-y: auto;
            max-height: 90%;
        }

        .popup-content h3 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .input-section {
            margin-bottom: 15px;
        }

        .input-section label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .input-section input,
        .input-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .input-section textarea {
            resize: none;
            height: 80px;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

.cancel-btn,
.upload-btn {
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

.cancel-btn {
    background-color: #f44336;
    color: white;
}

.upload-btn {
    top: 840px;
    left: 185px;
    position: absolute;
    background-color: #4CAF50;
    color: white;
    padding: 10px;
}

.cancel-btn:hover,
.upload-btn:hover {
    opacity: 0.9;
}


        .upload-btn {
            background-color: #4CAF50;
            color: white;
        }

        .cancel-btn:hover,
        .upload-img-btn:hover,
        .upload-btn:hover {
            opacity: 0.9;
        }

        /* Show popup */
        .popup.show {
            visibility: visible;
            opacity: 1;
        }

        .blur-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .upload-img-btn,
        .add-announce-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #2196F3;
            color: white;
        }

        .add-announce-btn {
            background-color: #4CAF50;
        }

        .upload-img-btn:hover,
        .add-announce-btn:hover {
            opacity: 0.9;
        }

        /* Popup styling remains the same */
        .popup-content {
            /* Your existing styles */
        }

        /* Input sections and form fields remain the same */
        .input-section {
            margin-bottom: 15px;
        }

        .input-section label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .input-section input,
        .input-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .input-section textarea {
            resize: none;
            height: 80px;
        }

        /* Visibility for the upload and form sections */
        #imageUploadSection,
        #addAnnouncementForm {
            display: none;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            font-size: 16px;
            margin-top: 20px;
        }

        .cancel-btn:hover {
            opacity: 0.9;
        }

        /* Styling for announcements container */
        #announcementsDisplay div {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #eee;
            border-radius: 5px;
        }

        #announcementsDisplay img {
            margin-top: 10px;
            max-width: 100%;
            height: auto;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
            display: inline-block;
        }

        .delete-btn:hover {
            opacity: 0.9;
        }


        /* ANNOUNCEMENT CSS BUTTON END */

        /* SLIDESHOW CSS END */

        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .text-center {
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }

        .col-4,
        .col-6,
        .col-8 {
            padding: 10px;
        }

        .col-4 {
            width: 33.33%;
        }

        .col-6 {
            width: 50%;
        }

        .col-8 {
            width: 66.66%;
        }

        .card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card img {
            width: 100%;
            border-radius: 5px 5px 0 0;
        }

        .card-body {
            padding: 10px;
        }

        .unit {
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .unitPics {
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .dagdag {
            width: 150px;
        }

        .mt-5 {
            margin-top: 50px;
        }

        .mb-5 {
            margin-bottom: 50px;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }


        h3 {
            margin-top: 20px;
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        p {
            font-size: 1em;
            margin: 10px 0;
        }

        hr {
            margin: 50px 0;
            border: 0;
            height: 1px;
            background: #ddd;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h4 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        .text-center {
            text-align: center;
        }

        .unit {
            font-size: 2em;
            margin: 20px 0;
        }

        .unitPics {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin: 20px 0;
        }

        .card {
            background-color: #046b42;
            border-radius: 15px;
            overflow: hidden;
            width: 300px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card img {
            width: 100%;
            height: auto;
            border-bottom: 2px solid #fff;
        }

        .card-body {
            padding: 15px;
        }

        .card-body p {
            margin: 0;
            font-weight: bold;
            font-size: 1.1em;
        }

        .description {
            margin: 20px auto;
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 80%;
        }

        .description div {
            width: 40%;
            padding: 20px;
            text-align: center;
            font-size: 1.1em;
            line-height: 1.5em;
        }

        .description hr {
            height: 150px;
            border: none;
            border-left: 1px solid #000;
        }

        .campus-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 50px 0;
            text-align: center;
        }

        .campus-section img {
            width: 200px;
            margin-right: 30px;
        }

        .line {
            border-left: 2px solid #ccc;
            /* Vertical line between logo and content */
            height: 250px;
            margin-right: 50px;
            margin-left: 50px;
        }

        .socials-campuses {
            display: flex;
            gap: 80px;
            /* Space between columns */
        }

        .socials,
        .campuses {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .socials h4,
        .campuses h4 {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .socials a,
        .campuses a {
            display: block;
            margin: 5px 0;
            color: #046b42;
            text-decoration: none;
            font-weight: normal;
        }

        .socials a:hover,
        .campuses a:hover {
            text-decoration: underline;
        }

        .close-x {
    position: absolute;
    right: -10px;
    top: -30px;
    font-size: 58px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-x:hover {
    color: #f44336;
}

/* Ensure popup-content has relative positioning */
.popup-content {
    position: relative;
    /* ... your existing popup-content styles ... */
}





        footer {
            text-align: center;
            margin: 30px 0;
            font-size: 0.9em;
            color: #888;
        }
        
        
        
        
@media screen and (max-width: 2120px){

.upload-btn {
    top: 830px;
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

}

@media screen and (max-width: 1800px){

.upload-btn {
    top: 430px;
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

}




@media screen and (max-width: 1710px){

.upload-btn {
    top: 830px;
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

}

@media screen and (max-width: 1600px){

.upload-btn {
    top: 840px;
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

}

@media screen and (max-width: 1500px){

.upload-btn {
    top: 825px;
    padding: 10px 20px; /* Ensure same padding */
    font-size: 16px; /* Ensure same font size */
    height: 40px; /* Set a fixed height */
    line-height: normal; /* Prevent differences in text alignment */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    width: 48%; /* Make both buttons take equal width */
    text-align: center;
}

}



    </style>
    </head>

    <body>

    <div class="content-wrapper">
    <div class="header">
        <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>
</div>

        <!-- SLIDESHOW START -->

        <div class="slideshow-container">
            <!-- Slide 1 -->
            <div class="slide fade">
                <img src="sinag2.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>CWTS visits<br>
                        Sinag Kalinga</h1>
                    <p>CWATS Trainees and Instructor visits the Home for the<br>
                        Elderly at Sinag Kalinga as part of their Annual Activity<br>
                        bringing gifts and activities to entertain the Aged.</p>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="slide fade">
                <img src="soldier.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>ROTC prepares for SLSU<br> Founding Anniversary!</h1>
                    <p>ROTC prepares for the Founding anniversary of Southern<br>
                        Luzon State University (SLPC) as they would participate in<br>
                        the standard Military Parade and Honors.</p>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="slide fade">
                <img src="clean1.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>CWTS Clean Up<br> Drive Launches</h1>
                    <p>As part of the CWATS mandates, they have launched their<br>
                        Annual Clean Up Drive in coordination with the Local<br>
                        Government Initiating in cleaning up the streets and canals<br>
                        of various locations.</p>
                </div>
            </div>
            <!-- Slide 4 -->
            <div class="slide fade">
                <img src="gun.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>ROTC Fires up Rifle<br> Handling and Drills.</h1>
                    <p>In preparation for the Annual Inter school ROTC Meet Up,<br>
                        SLSU starts practicing students in speed assembly and<br>
                        disassemble of arms to choose participants for the<br>
                        competition.</p>
                </div>
            </div>
            <!-- Slide 5 -->
            <div class="slide fade">
                <img src="tree1.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>CWTS Tree<br> Planting Activity</h1>
                    <p>As part of their community initiative, CWATS committed<br>
                        themselves to their annual tree planting activity at the face<br>
                        of Mt. Banahaw de Lucban, ensuring that the pristine nature<br>
                        is preserved and protected.</p>
                </div>
            </div>
            <!-- Slide 6 -->
            <div class="slide fade">
                <img src="lesson.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>ROTC Starts <br>
                        General Orientation</h1>
                    <p>ROTC have started their general orientation period,<br>
                        informing students the basic and fundamentals of the ROTC<br>
                        as a trainee and as an organization that aims to strengthen<br>
                        nationalistic aspirations.</p>
                </div>
            </div>
            <!-- Slide 7 -->
            <div class="slide fade">
                <img src="children1.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>CWTS visits the<br>
                        Children</h1>
                    <p>As part of their community initiative, CWATS conducts<br>
                        annual activities aimed at Children in order to give them an<br>
                        early aspiration in community participation, they also<br>
                        brought giftsand activities for entertainment </p>
                </div>
            </div>
            <!-- Slide 8 -->
            <div class="slide fade">
                <img src="officer.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>ROTC Officer Cadets<br>
                        Starts Training</h1>
                    <p>ROTC Officer Cadets have started their training towards being an Officer.<br>
                        Their journey towardsbeing an officer will<br>
                        be a struggle but a rewarding and fulfilling journey as well.</p>
                </div>
            </div>
        </div>


        <!-- SLIDESHOW ENDS -->

        <!-- UNIT HEADS START -->

        <section class="content-section">
            <h3 class="unit text-center">Unit Heads</h3>
            <div class="unitPics">
                <div class="card">
                    <img src="dator1.png" alt="LTC DR. NILO H. DATOR">
                    <div class="card-body">
                        <p>LTC DR.<br>NILO H. DATOR</p>
                        <p>OIC - ROTC Director</p>
                    </div>
                </div>
                <div class="card">
                    <img src="president.png" alt="DR. FREDERICK T. VILLA">
                    <div class="card-body">
                        <p>DR. FREDERICK<br>T. VILLA</p>
                        <p>University President</p>
                    </div>
                </div>
                <div class="card">
                    <img src="sheeesh.png" alt="PROF. EDSEL V. PARAON">
                    <div class="card-body">
                        <p>PROF. EDSEL<br>V. PARAON</p>
                        <p>OIC- CWTS Director</p>
                    </div>
                </div>
            </div>

            <!-- UNIT HEADS ENDS -->

            <!-- LAW PARAGRAPH START -->

            <div class="description">
                <div>
                    <h3 class="text-center">ROTC</h3>
                    <p>"Reserve Officers' Training Corps (ROTC)" is a program institutionalized under sections 38 and 39 of Republic Act No. 7077 designed to provide military training to tertiary level students in order to motivate, train, organize and mobilize them for national defense preparedness.</p>
                </div>
                <hr>
                <div>
                    <h3 class="text-center">CWTS</h3>
                    <p>The course mandated by Republic Act No. 9163, otherwise known as the National Service Training Act of 2001, aims to enhance the civic consciousness of the students "by developing the ethics of service and patriotism" while undergoing Civic Welfare Training Service (CWTS).</p>
                </div>
            </div>
        </section>

        <!-- LAW PARAGRAPH ENDS -->

        <!-- SLSU SOCIALS START -->

        <section class="campus-section">
            <img src="slsulogo.png" alt="SLSU Logo">
            <div class="line"></div> <!-- Vertical line between logo and content -->
            <div class="socials-campuses">
                <div class="socials">
                    <h4>Facebook Pages</h4>
                    <a href="https://www.facebook.com/slsuMain">SLSU Main Campus</a>
                    <a href="https://www.facebook.com/slsuOSR">SLSU Student Regent</a>
                    <a href="https://www.facebook.com/slsulucbanrotcu">ROTC Main Campus</a>
                    <a href="https://www.facebook.com/profile.php?id=61557265658212">CWTS Main Campus</a>
                </div>
                <div class="campuses">
                    <h4>Campuses</h4>
                    <a href="https://www.facebook.com/alabatcampus2021">SLSU Alabat</a>
                    <a href="https://www.facebook.com/profile.php?id=100065787540711">SLSU Catanauan</a>
                    <a href="https://www.facebook.com/slsugumaca.ssc">SLSU Gumaca</a>
                    <a href="https://www.facebook.com/slsu.infanta.9">SLSU Infanta</a>
                    <a href="https://www.facebook.com/profile.php?id=100064260360036">SLSU Lucena</a>
                </div>
                <div class="campuses">
                    <h4>&nbsp;</h4> <!-- Empty heading to maintain spacing consistency -->
                    <a href="https://www.facebook.com/SLSUpolillocampus">SLSU Polillo</a>
                    <a href="https://www.facebook.com/profile.php?id=100082165904094">SLSU Tagkawayan</a>
                    <a href="https://www.facebook.com/sscslsutayabas">SLSU Tayabas</a>
                    <a href="https://www.facebook.com/profile.php?id=61550220086147">SLSU Tiaong</a>
                </div>
            </div>
        </section>



        <!-- SLSU SOCIALS ENDS -->

        <footer>
            &copy; 2024 Southern Luzon State University. All Rights Reserved
        </footer>
        <!--Section Campuses End-->


        <!-- SLIDESHOW JS -->


        <script>
            let slideIndex = 0;
            showSlides();

            function showSlides() {
                let slides = document.getElementsByClassName("slide");
                for (let i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";
                }
                slideIndex++;
                if (slideIndex > slides.length) {
                    slideIndex = 1
                }
                slides[slideIndex - 1].style.display = "block";
                setTimeout(showSlides, 5000); // Change image every 5 seconds
            }

            // ADD ANNOUNCEMENT 

            let scrollPosition = 0;

            function showAnnouncementPopup() {
                scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
                document.getElementById('announcementPopup').classList.add('show');
                document.getElementById('blurOverlay').classList.add('show');

            }

            function hideAnnouncementPopup() {
                document.getElementById('announcementPopup').classList.remove('show');
                document.getElementById('blurOverlay').classList.remove('show');

                // Re-enable body scroll and restore the previous scroll position
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, scrollPosition); // Restore scroll position
            }

            function uploadImage() {
                document.getElementById('imageUploadSection').style.display = 'block';
                document.getElementById('addAnnouncementForm').style.display = 'none';
            }

            function showAddAnnouncementForm() {
                document.getElementById('imageUploadSection').style.display = 'none';
                document.getElementById('addAnnouncementForm').style.display = 'block';
            }

            function submitAnnouncement() {
                // Handle form submission for the announcement
                alert('Announcement submitted.');
            }

            // VIEW ANNOUNCEMENT

            let announcements = [];

            function viewAnnouncement() {
    document.getElementById('viewAnnouncementPopup').classList.add('show');
    document.getElementById('blurOverlay').classList.add('show');
}
            function displayAnnouncements() {
                const display = document.getElementById('announcementsDisplay');
                display.innerHTML = ''; // Clear previous entries

                if (announcements.length === 0) {
                    // Show a message if there are no announcements
                    const noAnnouncementsMessage = document.createElement('p');
                    noAnnouncementsMessage.innerHTML = '*No Further Announcements*';
                    noAnnouncementsMessage.style.textAlign = 'center';
                    noAnnouncementsMessage.style.fontStyle = 'italic';
                    display.appendChild(noAnnouncementsMessage);
                } else {
                    // Display announcements
                    announcements.forEach((ann, index) => {
                        const section = document.createElement('div');
                        section.innerHTML = `
                <h4>Title: ${ann.title}</h4>
                <p>Who: ${ann.who}</p>
                <p>What: ${ann.what}</p>
                <p>When: ${ann.when}</p>
                <p>Where: ${ann.where}</p>
                <p>Attire: ${ann.attire}</p>
                <p>Note: ${ann.note}</p>
                <p>Note: ${ann.announced_by}</p>
                ${ann.image ? '<img src="' + ann.image + '" style="width:100%;height:auto;"/>' : ''}
                <button class="delete-btn" onclick="deleteAnnouncement(' . $row['id'] . ')">Delete</button>
            `;
                        display.appendChild(section);
                    });
                }
            }

       // Update the deleteAnnouncement function
function deleteAnnouncement(id) {
    if (!id) {
        alert('Invalid announcement ID.');
        return;
    }

    if (confirm("Are you sure you want to delete this announcement?")) {
        fetch('delete_announcement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Announcement deleted successfully.');
                location.reload();
            } else {
                throw new Error(data.message || 'Failed to delete announcement');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Announcement deleted successfully.');
                location.reload();
            location.reload(); // Just reload the page since the operation was successful
        });
    }
}


            function hideViewAnnouncementPopup() {
                document.getElementById('viewAnnouncementPopup').classList.remove('show');
                document.getElementById('blurOverlay').classList.remove('show');
            }

            function handleSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch('submit_announcement.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Announcement added successfully.');
            hideAnnouncementPopup();
            location.reload();
        } else {
            throw new Error(data.message || 'Failed to add announcement');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Announcement added successfully.');
            hideAnnouncementPopup();
            location.reload();
        location.reload(); // Just reload the page since the operation was successful
    });
}

            // Preloader script
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            const mainContent = document.getElementById('main-content');
            const progressBar = document.getElementById('progress-bar');
            let progress = 0;

            const interval = setInterval(() => {
                progress += 1;
                progressBar.style.width = `${progress}%`;

                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        preloader.style.display = 'none';
                        mainContent.style.display = 'block';
                        setTimeout(() => {
                            mainContent.classList.add('show');
                        }, 50);
                    }, 500);
                }
            }, 20);
        });

        function confirmLogout() {
            if (confirm("Do you want to Logout?")) {
                window.location.href = "logout.php";
            }
        }

        
        </script>

        <!-- SLIDESHOW JS -->


        </header>
    </body>

</html>


</body>
</html>