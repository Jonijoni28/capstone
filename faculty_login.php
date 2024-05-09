<?php
    $username = $_POST['username'];
    $password = $_POST['password'];

    //DATABASE CONNECTION HERE
    $con = new mysqli("localhost","root","","login");
    if($con->connect_error) {
        die("Failed to connect : ".$con->connect_error);
    } else {
        $stmt = $con->prepare("select * from registration where username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt_results = $stmt->get_result();
        if($stmt_results->num_rows > 0) {
            $data = $stmt_results->fetch_assoc();
            if($data['password'] === $password) {
                echo "<h2>Login Successfully</h2>";
                echo "<script>window.location = 'homepage.html' </script>";
            } else {
               echo "<h2>Invalid Email or password</h2>";
            }
        } else {
            echo "<script>window.location = 'faculty.php' </script>";
        }
    }
?>