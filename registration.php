<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #185519;
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

        /* Container for the registration form */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 200px);
            /* Adjusted for header height */
            margin-top: 40px;
            /* Adds space between the header and the form */
            padding: 20px;
        }

        .registration-box {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 30px;
            width: 700px;
            max-height: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            overflow-y: auto;
            position: relative;
            max-height: calc(90vh - 100px);
            /* Ensures it doesn't overflow out of view */
        }

        .registration-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            position: relative;
            margin-bottom: 15px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
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

        .form-section {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-section h3 {
            margin-bottom: 10px;
        }

        .form-group input[type="file"] {
            padding: 5px;
            border: none;
        }

        .checkbox-group {
            display: flex;
            justify-content: start;
            align-items: center;
            margin-top: 10px;
        }

        .checkbox-group input {
            margin-right: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            /* Remove page scrolling when modal is open */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black with opacity */
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align: center;
            border-radius: 8px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            /* Center horizontally and vertically */
        }

        .modal-content p {
            font-size: 18px;
        }

        .close-btn,
        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .close-btn:hover,
        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <a href="index.php"><img src="slsulogo.png" class="headlogo"></a>
        <h1>Southern Luzon State University</h1>
        <p>National Service Training Program</p>
    </div>

    <!-- Registration Form Section -->
    <div class="container">
        <div class="registration-box">
            <h2>Registration Form</h2>

            <!-- Basic Information Section -->
            <div class="form-section">
                <h3>Basic Information</h3>
                <form id="registerForm" action="register_process.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <select name="title" required>
                            <option value="" disabled selected>Title</option>
                            <option value="Archi">Architech</option>
                            <option value="Atty">Atty.</option>
                            <option value="Dean">Dean</option>
                            <option value="Director">Director</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Engr.">Engr.</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Pres.">President</option>
                            <option value="Prof.">Prof.</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="first_name" placeholder="First Name" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="middle_name" placeholder="Middle Name" />
                    </div>
                    <div class="form-group">
                        <input type="text" name="last_name" placeholder="Last Name" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="suffix" placeholder="Suffix" />
                    </div>
                    <div class="form-group">
                        <select name="sex" required>
                            <option value="" disabled selected>Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required />
                    </div>
                    <div class="form-group">
                        <input type="tel" name="mobile" placeholder="Mobile Number" required />
                    </div>
                    <div class="form-group">
                        <label for="photo">2x2 ID Picture:</label>
                        <input type="file" id="photo" name="photo" accept="image/*" required />
                    </div>
            </div>

            <!-- Affiliation Section -->
            <div class="form-section">
                <h3>Affiliation</h3>
                <div class="form-group">
                    <input type="text" name="university" placeholder="University" required />
                </div>
                <div class="form-group">
                    <input type="text" name="department" placeholder="Department" required />
                </div>
                <div class="form-group">
                    <input type="text" name="designation" placeholder="Designation/Position" required />
                </div>
                <div class="form-group">
                    <select name="employment_status" required>
                        <option value="" disabled selected>Employment Status</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="area_assignment" placeholder="Area of Assignment" required />
                </div>
            </div>

            <!-- User Account Section -->
            <div class="form-section">
                <h3>User Account</h3>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required />
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="terms" required />
                    <label for="terms">I agree to the terms and conditions</label>
                </div>
            </div>

            <input type="submit" class="btn" value="Register" />
            </form>
        </div>
    </div>

    <!-- Modal structure -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <p>Account Registered. Wait for your Approval Request to Login into the National Service Training Program Website.</p>
            <button id="backBtn" class="back-btn">Go to Login Page</button>
        </div>
    </div>

    <script>
        /**
         * Shows the registration confirmation modal.
         * @function
         * @name showModal
         * @param {string} message - The message to display in the modal.
         */
        function showModal(message) {
            const modal = document.getElementById('registerModal');
            const modalMessage = modal.querySelector('p');
            modalMessage.textContent = message;
            modal.style.display = 'block';
        }

        /**
         * Handles the click event on the "Go to Login Page" button.
         * Redirects the user to the login page.
         * @function
         * @name backBtnClickHandler
         */
        document.getElementById('backBtn').onclick = function backBtnClickHandler() {
            window.location.href = 'index.php'; // Redirect to login page
        }

        /**
         * Handles the form submission event.
         * Prevents default form submission, sends form data to the server, and shows the confirmation modal.
         * @function
         * @name submit_register
         * @param {Event} event - The form submission event object.
         */
        function submit_register(event) {
            // Prevent the default form submission
            event.preventDefault();

            // Get the form element
            const form = document.getElementById('registerForm');

            // Check if the form exists
            if (form) {
                // Create a FormData object
                const formData = new FormData(form);

                // Send the form data to the server using fetch
                fetch('/register_process.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showModal(data.message);
                        } else {
                            showModal('Registration failed: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal('An error occurred during registration. Please try again.');
                    });
            } else {
                console.error('Form with ID "registerForm" not found');
            }
        }

        // Attach the submit_register function to the form submission
        document.getElementById('registerForm').addEventListener('submit', submit_register);
    </script>
</body>

</html>