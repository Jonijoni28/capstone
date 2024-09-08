<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="instructor.css">

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

<style>
    body {
  
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

</style>

<div class="faculty-section">
    <!-- CWTS Instructors Section -->
    <div class="cwts-instructors">
        <h2>CWTS Instructors</h2>
        <div class="cwts-grid">
            <div class="instructor">
                <img src="aimee.png" alt="Joyce Andrade">
                <p>JOYCE ANDRADE</p>
                <p>CTE</p>
            </div>
            <div class="instructor">
                <img src="hans.png" alt="Jerick Besalo">
                <p>JERICK A. BESALO</p>
                <p>CAS, CEN, CAG</p>
            </div>
            <div class="instructor">
                <img src="aimee.png" alt="Airra Cadid">
                <p>AIRRA JHANE D. CADID</p>
                <p>CAM & CEN</p>
            </div>
            <div class="instructor">
                <img src="aimee.png" alt="Joyce Malacad">
                <p>JOYCE MALACAD</p>
                <p>CIT, CAS & CABHA</p>
            </div>
            <div class="instructor">
                <img src="aimee.png" alt="Monica Orobia">
                <p>MONICA ELAINE R. OROBIA</p>
                <p>CABHA</p>
            </div>
        </div>
    </div>

    <!-- ROTC Instructors Section -->
    <div class="rotc-instructors">
        <h2>ROTC Instructors</h2>
        <div class="rotc-grid">
            <div class="instructor">
                <img src="hans.png" alt="LTC DR. NILO H. DATOR">
                <p>LTC DR. NILO H. DATOR</p>
                <p>ROTC President</p>
            </div>
            <div class="instructor">
                <img src="hans.png" alt="P2LT JESRAEL G. LUCES">
                <p>P2LT JESRAEL G. LUCES P A</p>
                <p>Battalion Ex-O/S3</p>
            </div>
            <div class="instructor">
                <img src="aimee.png" alt="P2LT ANEJANE R. PERJES">
                <p>P2LT ANEJANE R. PERJES P A</p>
                <p>Battalion S7</p>
            </div>
            <div class="instructor">
                <img src="hans.png" alt="P2LT JOSHUA D. FELIPE">
                <p>P2LT JOSHUA D. FELIPE P A</p>
                <p>Battalion Commander</p>
            </div>
            <div class="instructor">
                <img src="aimee.png" alt="P2LT AIRA CHEEZCA A. DIVINAGRACIA">
                <p>P2LT AIRA CHEEZCA A. DIVINAGRACIA P A</p>
                <p>Battalion Adjutant/S1</p>
            </div>
            <div class="instructor">
                <img src="rubie_rose_mojica.png" alt="P2LT RUBIE ROSE M. MOJICA">
                <p>P2LT RUBIE ROSE M. MOJICA P A</p>
                <p>Battalion S3</p>
            </div>
        </div>
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