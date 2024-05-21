<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="homepage.css">

    <div class="header">
        <a href="professor.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
        </div>
        <div class="navbar">    
        <a href="#" class="action_btn">Instructor</a>
        <div class="toggle_btn">
            <i class="fa-solid fa-bars"></i>
            </div>
        </div>  
</head>

    <body>
       
            <input type="checkbox" id="check">
            <label for="check">
                <i class="fas fa-bars" id="btn"></i>
                <i class="fas fa-times" id="cancel"></i>
            </label>
            <div class="sidebar">
            <header>Instructor</header>
            <ul> 
                <li><a href="professor.php"><i class="fa-solid fa-house"></i></i>Homepage</a></li>
                <li><a href="inputgrades.php"><i class="fas fa-link"></i>Input Grades</a></li>
            </ul>
            </div>
            

            <div class="dropdown_menu">
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#" class="action_btn">Login Here</a></li>
            </div>
        </header>
    </body>
</html> 