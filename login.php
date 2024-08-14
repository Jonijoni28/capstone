<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Southern Luzon State University - NSTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="styles.css">
    <style>


body {
    background-color: white; /* Set background to white */
    background-image: none; /* Ensure no background image is applied */
    margin: 0;
}

.slide img {
    width: auto; /* Maintain the aspect ratio of the images */
    height: 100%; /* Scale the height to fit the container */
    max-width: 100%; /* Ensure the width doesn't exceed the container */
    object-fit: contain; /* Ensure images fit the container without distortion */
    display: block;
    margin: auto;
}
    
.slideshow-container {
    position: relative;
    max-width: 100%;
    height: 500px; /* Set a fixed height for the slideshow */
    margin: auto;
    overflow: hidden;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
}

.slide img {
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
        bottom: 5%; /* Position text near the bottom */
        left: 28%;
    transform: translateX(-50%);
    color: white;
    font-size: 3rem; /* Larger font size */
    text-align: center;
    font-weight: bold; /* Bold text */
    font-family: Arial, sans-serif; /* Clean font */
    background: none; /* No background color */
    padding: 0; /* No padding */
    margin: 0; /* No margin */
    z-index: 10;
    box-shadow: none; /* No shadow */
    line-height: 1.2; /* Adjust line height */
}

.text h1, .text p {
    margin: 0; /* Remove margins */
    padding: 0; /* Remove padding */
    text-align: left;
    background: none; /* Ensure no background color */
    box-shadow: none; /* Ensure no shadow */
}

.text p {
    font-size: 1.5rem; /* Smaller text size */
    font-weight: normal; /* Normal weight for the smaller text */
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
            font-weight: bold ;
            font-family: sans-serif;
        }

        @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @media (max-width: 700px) {
            h1 {
                font-size: 20px;
                padding-left: 100px;
            }

            .header p {
                font-size: 14px;
                padding-left: 100px;
            }

            .headlogo {
                width: 60px;
                height: 60px;   
                margin-right: 10px;
            }

            .action_btn {
                font-size: 14px;
                padding: 5px 10px;
            }

            .text {
                font-size: 20px;
            
            }

            .logo {
                width: 80px;
            }
        }

        @media (max-width: 424px) {
            h1 {
                font-size: 18px;
                padding-left: 80px;
            }

            .header p {
                font-size: 12px;
                padding-left: 80px;
            }

            .headlogo {
                width: 50px;
                height: 50px;   
                margin-right: 10px;
            }

            .action_btn {
                font-size: 12px;
                padding: 5px 10px;
            }

            .text {
                font-size: 18px;
                padding: 5px;
            }

            .logo {
                width: 70px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <img src="slsulogo.png" class="headlogo">
    <h1>Southern Luzon State University</h1>
    <p>National Service Training Program</p>
    <div class="navbar">    
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

</body>
</html>