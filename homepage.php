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
    
    </head>

        <body>
        <div class="header">
            <a href="prof.php"><img src="slsulogo.png" class="headlogo"></a>
            <h1>Southern Luzon State University</h1>
            <p>National Service Training Program</p>
            </div>
            <div class="navbar">    
            <a href="#" class="action_btn">Administrator</a>
            <div class="toggle_btn">
                <i class="fa-solid fa-bars"></i>
                </div>
            </div>  
        
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
                <style>

                body {
        background-color: white; /* Set background to white */
        background-image: none; /* Ensure no background image is applied */
        margin: 0;
    }

    /* Sidebar */
.sidebar {
    position: fixed;  /* Keeps sidebar fixed when scrolling */
    left: -250px;     /* Initially off-screen */
    top: 30;           /* Fix the sidebar from the top */
    width: 250px;
    height: 100%;
    background: #096c37;
    transition: all .5s ease;
    z-index: 1000;    /* Ensure the sidebar stays above the main content */
    overflow-y: auto; /* Allow scrolling inside the sidebar if content exceeds height */
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

ul li:hover a {
    padding-left: 50px;
}

/* Icon styles inside sidebar */
.sidebar ul a i {
    margin-right: 16px;
}

/* Sidebar toggle button */
#check {
    display: none;
}

/* Styling for the open button */
label #btn, label #cancel {
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
    top: 130px;
    font-size: 30px;
    color: #fff;
    padding: 4px 9px;
    transition: all .5s ease;
}

/* Toggle: When checked, open the sidebar */
#check:checked ~ .sidebar {
    left: 0;
}

/* Hide the open button and show the close button when the sidebar is open */
#check:checked ~ label #btn {
    left: 250px;
    opacity: 0;
    pointer-events: none;
}

/* Move the close button when the sidebar is open */
#check:checked ~ label #cancel {
    left: 195px;
}

/* Ensure the content shifts when the sidebar is open */
#check:checked ~ body {
    margin-left: 250px;
}


    /* SLIDESHOW CSS */

  /* SLIDESHOW CSS START */

.slide img {
    width: auto; /* Maintain the aspect ratio of the images */
    height: 100%; /* Scale the height to fit the container */
    z-index: -1000;
    max-width: 100%; /* Ensure the width doesn't exceed the container */
    object-fit: contain; /* Ensure images fit the container without distortion */
    display: block;
    margin: auto;
}

.slideshow-container {
    position: relative;
    max-width: 100%;
    z-index: -1000;
    height: 550px; /* Set a fixed height for the slideshow */
    margin: auto;
    margin-bottom: 50px;
    overflow: hidden;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
}

.slide img {
    z-index: -1000; 
    max-width: 100%; /* Ensure the width doesn't exceed the container */
    max-height: 100%; /* Ensure the height doesn't exceed the container */
    object-fit: contain; /* Maintain the aspect ratio and fit the image within the container */
    display: block;
    margin: auto;
}

.slide::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 60%; /* Adjust this height based on how much of the bottom you want to fade */
    background: linear-gradient(to top, rgba(0, 128, 0, 0.7), transparent); /* Green gradient effect */
    pointer-events: none; /* Ensure the overlay doesn't affect interactivity */
}

.text {
    position: absolute;
    bottom: 20%; /* Align the text higher */
    left: 5%; /* Align the text on the left side */
    color: white;
    font-family: 'Arial', serif; /* Match formal font style */
    font-weight: bold; /* Bold font */
    letter-spacing: 1px;
    padding: 0;
    margin: 0;
    color:#096c37;
    z-index: 10;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8); /* Stronger text shadow */
    line-height: 1.2; /* Line height to match the spacing */
}

.text h1 {
    margin: 0;
    font-size: 4rem; /* Adjusted font size */
    -webkit-text-stroke: 2px white; /* Outline effect */
    padding: 0;
    text-align: left; /* Align text to the left */
}

.text p {
    font-size: 1.5rem; /* Subtitle smaller text */
    font-weight: bold;
    color: white;
    font-family: sans-serif; /* Match formal font style */
    -webkit-text-stroke: 1px black; /* Outline effect */
    margin: 10px 0 0 0; /* Space between title and subtitle */
    text-align: left; /* Align text to the left */
    letter-spacing: 1px;
}

@keyframes fade {
    from {opacity: .4}
    to {opacity: 1}
}

.fade {
    animation-name: fade;
    animation-duration: 1.5s;
}

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

            .col-4, .col-6, .col-8 {
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
    border-left: 2px solid #ccc; /* Vertical line between logo and content */
    height: 250px;
    margin-right: 50px;
    margin-left: 50px;
}

.socials-campuses {
    display: flex;
    gap: 80px; /* Space between columns */
}

.socials, .campuses {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.socials h4, .campuses h4 {
    font-weight: bold;
    margin-bottom: 10px;
}

.socials a, .campuses a {
    display: block;
    margin: 5px 0;
    color: #046b42;
    text-decoration: none;
    font-weight: normal;
}

.socials a:hover, .campuses a:hover {
    text-decoration: underline;
}




            footer {
                text-align: center;
                margin: 30px 0;
                font-size: 0.9em;
                color: #888;
            }

        </style>
    </head>
    <body>

    <!-- SLIDESHOW START -->

    <div class="slideshow-container">
        <!-- Slide 1 -->
        <div class="slide fade">
            <img src="sinag1.jpg" alt="SLSU ROTC">
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
            <img src="clean.jpg" alt="SLSU ROTC">
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
            <img src="gun.png" alt="SLSU ROTC">
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
            <img src="tree.jpg" alt="SLSU ROTC">
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
            <img src="children.jpg" alt="SLSU ROTC">
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
                    <p>OIC - University President</p>
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
                <img src="nstppres.png" alt="PROF. ANTONIO V. ROMANA">
                <div class="card-body">
                    <p>PROF. ANTONIO<br>V. ROMANA</p>
                    <p>CWTS - President</p>
                </div>
            </div>
        </div>

        <!-- UNIT HEADS ENDS -->

        <!-- LAW PARAGRAPH START -->

        <div class="description">
            <div>
                <h3 class="text-center">ROTC</h3>
                <p>“Reserve Officers' Training Corps (ROTC)” is a program institutionalized under sections 38 and 39 of Republic Act No. 7077 designed to provide military training to tertiary level students in order to motivate, train, organize and mobilize them for national defense preparedness.</p>
            </div>
            <hr>
            <div>
                <h3 class="text-center">CWTS</h3>
                <p>The course mandated by Republic Act No. 9163, otherwise known as the National Service Training Act of 2001, aims to enhance the civic consciousness of the students “by developing the ethics of service and patriotism” while undergoing Civic Welfare Training Service (CWTS).</p>
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
            <h4>Socials</h4>
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
            if (slideIndex > slides.length) {slideIndex = 1}    
            slides[slideIndex-1].style.display = "block";  
            setTimeout(showSlides, 5000); // Change image every 5 seconds
        }
    </script>
                
    <!-- SLIDESHOW JS -->

            
            </header>
        </body>
    </html> 
