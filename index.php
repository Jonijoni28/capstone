<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Southern Luzon State University - NSTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div id="preloader">
        <img src="slsulogo.png" alt="Logo" class="preloader-logo">
        <div class="progress-container">
            <div id="progress-bar"></div>
        </div>
    </div>

    <div id="main-content" style="display: none;">
        <div class="header">
            <img src="slsulogo.png" class="headlogo">
            <h1>Southern Luzon State University</h1>
            <p>National Service Training Program</p>
            <div class="navbar">    
                <a href="registration.php" class="action_btn">Registration</a>
                <a href="faculty.php" class="action_btn">Sign In</a>
            </div> 
        </div>

        <div class="slideshow-container">
            <!-- Slide 1 -->
            <div class="slide fade">
                <img src="soldier.jpg" alt="SLSU ROTC">
                <div class="text">
                    <h1>JOIN US NOW!</h1>
                    <p>SOUTHERN LUZON STATE UNIVERSITY - ROTC UNIT</p>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="slide fade">
                <img src="boom.jpg" alt="SLSU ROTC">
                <div class="text">
                <h1>BE ONE OF US!</h1>
                    <p>SOUTHERN LUZON STATE UNIVERSITY - CWTS UNIT</p>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="slide fade">
                <img src="tara.jpg" alt="SLSU ROTC">
                <div class="text">
                <h1>WE SHAPE YOU!</h1>
                    <p>SOUTHERN LUZON STATE UNIVERSITY - NSTP UNIT</p>
                </div>
            </div>
        </div>
        
        <div class="caption-container">
            <img src="cwtslogo.png" alt="First Logo" class="logo">
            <div class="caption-text">
                <p>
                    The NSTP was established by virtue of RA 9163 also known as the National Service 
                    Training Program Act of 2001. As provided in the law, the National Service Training Program (NSTP)
                    is a program aimed at enhancing civic consciousness and defense preparedness in the youth by developing
                    the ethics of service and patriotism while undergoing training in any of its three (3) program components.
                    Its various components are specially designed to enhance the youth's active contribution to the general
                    welfare.
                </p>
            </div>
            <img src="rotclogo.png" alt="Second Logo" class="logo">
        </div>
    </div>

    <style>
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

        @keyframes popIn {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.1);
                opacity: 1;
            }
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

        body {
            background-color: white;
            background-image: none;
            margin: 0;
            list-style: none;
            text-decoration: none;
        }

        .header {
            overflow: hidden;
            background-color: #0a3a20;
            color: white;
            height: 80px;
        }

        h1 {
            margin-top: 10px;
            margin-left: 0px;
        }

        .header p {
            margin-left: 0px;
            font-size: 20px;
            margin-top: 0px;
        }

        .headlogo {
            width: 100px;
            height: 100px;   
            float: left;
            margin-right: 20px;
            margin-top: -10px;
        }

        .navbar {
            float: right;
            margin-top: -60px;
        }

        .action_btn {
            background: linear-gradient(135deg, rgb(21, 134, 72), rgb(17, 114, 62));
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            outline: none;
            margin: 10px;
        }

        .action_btn:hover {
            scale: 1.07;
            background: linear-gradient(135deg, rgb(17, 114, 62), rgb(21, 134, 72));
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15), 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .action_btn:active {
            scale: 0.97;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* SLIDESHOW CSS START */
        .slide img {
            width: auto;
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
            margin: auto;
        }
            
        .slideshow-container {
            position: relative;
            max-width: 100%;
            height: 550px;
            margin-bottom: 10px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .slide::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background: linear-gradient(to top, rgba(0, 128, 0, 0.7), transparent);
            pointer-events: none;
        }

        /* TEXT AND LOGO START */
        .text {
            position: absolute;
            bottom: 5%;
            left: 28%;
            transform: translateX(-50%);
            color: white;
            font-size: 3rem;
            text-align: center;
            font-weight: bold;
            font-family: Arial, sans-serif;
            background: none;
            padding: 0;
            margin: 0;
            z-index: 10;
            box-shadow: none;
            line-height: 1.2;
        }

        .text h1, .text p {
            margin: 0;
            padding: 0;
            text-align: left;
            background: none;
            box-shadow: none;
        }

        .text p {
            font-size: 1.5rem;
            font-weight: normal;
        }

        @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        .caption-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 150px;
            margin: 0 20px;
        }

        .caption-text {
            max-width: 750px;
            font-size: 20px;
            font-weight: bold;
            font-family: sans-serif;
        }
    </style>

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
    </script>
</body>
</html>