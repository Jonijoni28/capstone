<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="dashboard.css">

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
</head>

<body>
<div class="dashboard">
        <div class="box">
            <h3>Total Students</h3>
            <p id="total-students"><?php echo $totalStudents; ?></p>
        </div>
        <div class="box">
            <h3>ROTC Students</h3>
            <p id="rotc-students"><?php echo $rotcStudents; ?></p>
        </div>
        <div class="box">
            <h3>CWTS Students</h3>
            <p id="cwts-students"><?php echo $cwtsStudents; ?></p>
        </div>
    </div>

    <body>
       
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

</header>
</body>
</html> 