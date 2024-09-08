<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            background: url('nstp.jpg') no-repeat center center/cover;
             height: 100vh;
            display: flex;
            flex-direction: column;
        }

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
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 8px;
            font-size: 16px;
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <a href="login.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>

    <!-- Login Form Section -->
    <div class="container">
        <div class="login-box">
            <h2>Login Form</h2>
            <form action="faculty_login.php" method="post">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required />
                    <i class='bx bxs-user'></i>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required />
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox"> Remember me</label>
                </div>
                <input type="submit" class="btn" value="Login" />
            </form>
            <small>&copy; BSIT Students</small>
        </div>
    </div>
</body>
</html>
