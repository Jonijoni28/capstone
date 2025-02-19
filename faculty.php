<?php
    session_start();

    // Check if the session ID stored in the cookie matches the current session
    if (isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type'])) {
        // Redirect based on user type
        if ($_SESSION['user_type'] === 'admin') {
            header("Location: homepage");
            exit();
        } elseif ($_SESSION['user_type'] === 'instructor') {
            header("Location: professor");
            exit();
        }
    }

    
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="slsulogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        
        }

body {
    background: url('backgroundss.jpg') no-repeat center center fixed;
    background-size: cover;
    height: 100vh;
    display: flex;
    flex-direction: column;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
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

        /* PRELOADER CSS */

        /* Header styling */
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

        /* Container for the login form */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 180px); /* Adjusted for header height */
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 30px;
            width: 350px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: white;
        }

        .form-group {
    position: relative;
    margin-bottom: 20px;
}

.form-group input {
    width: 100%;
    padding: 10px;
    padding-left: 40px;
    padding-right: 40px; /* Added padding-right for the show/hide icon */
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 8px;
    font-size: 16px;
}

.form-group #lockIcon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 20px;
}

.togglePassword {
    position: absolute;
    right: -230px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 20px;
    cursor: pointer;
    z-index: 1;
}



#togglePassword:hover {
    color: #555;
}

        .form-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            color: white;
        }

        .remember-forgot label {
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #218838;
        }

        .login-box small {
            display: block;
            margin-top: 10px;
            color: white;
        }
        
.captcha-group {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    position: relative;
    align-items: center; /* Align items vertically */
}

.captcha-container {
    display: flex;
    align-items: center;
    gap: 5px; /* Space between captcha image and refresh icon */
}

.captcha-group img {
    height: 40px;
    border-radius: 4px;
    width: 120px;
}

.captcha-group input {
    flex: 1;
    padding: 10px;
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 8px;
    font-size: 16px;
}

#refresh-captcha {
    cursor: pointer;
    font-size: 18px; /* Smaller font size */
    color: white;
    padding: 4px; /* Add some padding */
    display: flex;
    right: 120px;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
}

#refresh-captcha:hover {
    color: #28a745;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    color: white;
}

.remember-forgot label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.forgot-password {
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #white;
    text-decoration: underline;
}
    </style>
</head>
<body>

<div id="preloader">
        <img src="slsulogo.png" alt="Logo" class="preloader-logo">
        <div class="progress-container">
            <div id="progress-bar"></div>
        </div>
    </div>
    <!-- Header Section -->
    <div class="header">
        <a href="index.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>

    <!-- Login Form Section -->
 <div class="container">
        <div class="login-box">
            <h2>Login Form</h2>
            <form action="faculty_login" method="post">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required 
                           value="<?php echo isset($_COOKIE['remembered_username']) ? htmlspecialchars($_COOKIE['remembered_username']) : ''; ?>"/>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required
                           value="<?php echo isset($_COOKIE['remembered_password']) ? htmlspecialchars($_COOKIE['remembered_password']) : ''; ?>"/>
                    <i class='bx bx-show-alt' id="togglePassword"></i>
                </div>
                <!-- Add CAPTCHA -->
                <div class="form-group captcha-group">
                    <img src="captcha" id="captcha-image" alt="CAPTCHA">
                    <i class='bx bx-refresh' id="refresh-captcha"></i>
                    <input type="text" name="captcha" placeholder="Enter CAPTCHA" required />
                </div>
                <?php
                if (isset($_SESSION['login_error'])) {
                    echo '<div style="color: #ff3333; background-color: rgba(255, 255, 255, 0.9); 
                              padding: 10px; border-radius: 5px; margin-bottom: 15px; 
                              text-align: center; font-weight: bold;">' 
                         . $_SESSION['login_error'] . 
                         '</div>';
                    unset($_SESSION['login_error']);
                }
?>
                    <div class="remember-forgot">
        <label>
            <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remembered_username']) ? 'checked' : ''; ?>> 
            Remember me
        </label>
        <a href="forgot_password" class="forgot-password">Forgot Password?</a>
    </div>
    <input type="submit" class="btn" value="Login" />
</form>
            <small>&copy; BSIT Students</small>
        </div>
    </div>

    <script>
         // Preloader script
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.getElementById('preloader');
        const progressBar = document.getElementById('progress-bar');
        let progress = 0;

        const interval = setInterval(() => {
            progress += 1;
            progressBar.style.width = `${progress}%`;

            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);
                }, 500);
            }
        }, 20);
    });

    // CAPTCHA refresh functionality
 document.getElementById('refresh-captcha').addEventListener('click', function(e) {
        e.stopPropagation(); // Stop event from bubbling
        document.getElementById('captcha-image').src = 'captcha?' + new Date().getTime();
        document.querySelector('input[name="captcha"]').value = ''; // Clear the input
    });

    // Show error messages if any
    <?php if (isset($_SESSION['login_error'])): ?>
        alert('<?php echo $_SESSION['login_error']; ?>');
    <?php endif; ?>

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event from bubbling
        // toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // toggle the icon
        this.classList.toggle('bx-show-alt');
        this.classList.toggle('bx-hide');
    });

    </script>
</body>
</html>
