<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <div class="header">
        <a href="login.html"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
            <p>National Service Training Program</p>
        </div>
</head>
    
<body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="card w-25">
                <div class="card-header">
                    <h2>Login Form</h2>
                </div>
                <div class="card-body">
                    <form action="faculty_login.php" method="post">
                        <div class="form-group">
                            <input type="name" id="name" class="form-control" name="username" placeholder="Username" required/>
                            <i class='bx bxs-user'></i>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" class="form-control" name="password" placeholder="Password" required/>
                            <i class='bx bxs-lock-alt' ></i>
                        </div>
                        <div class="remember-forgot">
                            <label><input type="checkbox">Remember me</label>
                        </div>
                        <input type="submit" class="btn btn-primary" value="Login" name="">
                    </form>
                </div>
                <div class="card-footer">
                    <small>&copy; BSIT Students</small>
                </div>
            </div>
        </div>
        
</body>
</html>