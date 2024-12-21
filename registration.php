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
            background-image: url(backgroundss.jpg);
            height: 100vh;
            display: flex;
            flex-direction: column;
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

        .form-group i.bxs-lock-alt {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 20px;
    z-index: 1;
    pointer-events: none;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 40%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    cursor: pointer;
}

.password-toggle i {
    color: #888;
    font-size: 20px;
}

.password-toggle:hover i {
    color: #555;
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

        .terms-text {
        text-align: left;
        margin: 20px 0;
        color: #333;
        padding: 0 20px;
    }

    .terms-text h3 {
        color: #0a3a20;
        margin-bottom: 15px;
    }

    .terms-text p {
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .terms-text strong {
        color: #0a3a20;
    }

    .modal-content {
        width: 70%; /* Increased width for better readability */
        max-width: 800px;
    }

    .modal-buttons {
        position: sticky;
        bottom: 0;
        background-color: white;
        padding: 15px 0;
        border-top: 1px solid #ddd;
    }

    .agree-btn, .disagree-btn {
    padding: 10px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 10px;
}

.agree-btn {
    background-color: #0a3a20; /* SLSU green color */
    color: white;
}

.agree-btn:hover {
    background-color: #0c4526;
}

.disagree-btn {
    background-color: #dc3545; /* Red color */
    color: white;
}

.disagree-btn:hover {
    background-color: #c82333;
}


#username-message {
    display: block;
    font-size: 14px;
    margin-top: 5px;
}

input:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
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
                        <label for="photo">2x2 Formal ID Picture:</label>
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
                <input type="text" name="designation" value="instructor" readonly style="background-color: #f0f0f0; cursor: not-allowed;" />
                </div>

                <div class="form-group">
                    <select name="employment_status" required>
                        <option value="" disabled selected>Employment Status</option>
                        <option value="Full-time">Permanent</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="form-group">
                <select name="area_assignment" required>
                        <option value="" disabled selected>Select Area of Assignment</option>
                        <option value="CWTS">CWTS</option>
                        <option value="ROTC">ROTC</option>
                    </select>
                </div>
            </div>

            <!-- User Account Section -->
            <div class="form-section">
                <h3>User Account</h3>
                <div class="form-group">
                    <input type="text" id="username" name="username" required>
                    <span id="username-message"></span>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="password-toggle">
                        <i class='bx bx-show-alt' id="togglePassword"></i>
                    </span>
                </div>

                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <span class="password-toggle">
                        <i class='bx bx-show-alt' id="toggleConfirmPassword"></i>
                    </span>
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

    <div id="termsModal" class="modal">
    <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
        <h2>Terms and Conditions</h2>
        <div class="terms-text">
            <h3>Please read these terms and conditions carefully before registering:</h3>
            <br>
            <p>1. <strong>Registration Agreement:</strong> By registering for the National Service Training Program (NSTP), you agree to provide accurate, current, and complete information. Any falsification of information may result in immediate termination of your account and possible legal consequences.</p>
            <br>
            <p>2. <strong>Account Security:</strong> You are solely responsible for maintaining the confidentiality of your account credentials. Any activities that occur under your account are your responsibility. You must immediately notify the administration of any unauthorized use of your account.</p>
            <br>
            <p>3. <strong>Program Commitment:</strong> By registering, you commit to actively participate in all required NSTP activities, training sessions, and community service projects. Regular attendance and participation are mandatory for program completion.</p>
            <br>
            <p>4. <strong>Code of Conduct:</strong> You agree to adhere to the NSTP Code of Conduct, which includes but is not limited to: maintaining proper behavior, showing respect to instructors and fellow participants, following safety protocols, and upholding the university's values and regulations.</p>
            <br>
            <p>5. <strong>Privacy Policy:</strong> Your personal information will be collected, stored, and processed in accordance with our privacy policy. This information may be used for program-related communications, emergency contacts, and administrative purposes.</p>
            <br>
            <p>6. <strong>Media Release:</strong> You grant permission for the use of photographs, videos, or other media content featuring your participation in NSTP activities for promotional, educational, or documentation purposes without compensation.</p>
            <br>
            <p>7. <strong>Program Modifications:</strong> The NSTP administration reserves the right to modify, suspend, or cancel any activities, schedules, or requirements as deemed necessary. Participants will be notified of any significant changes through their registered contact information.</p>
            <br>
            <p>8. <strong>Health and Safety:</strong> You certify that you are physically and mentally fit to participate in NSTP activities. You must disclose any medical conditions that may affect your participation and follow all safety guidelines provided by instructors.</p>
            <br>
            <p>9. <strong>Academic Requirements:</strong> You understand that NSTP is a mandatory component of your academic curriculum, and failure to complete the program requirements may affect your academic standing or graduation eligibility.</p>
            <br>
            <p>10. <strong>Termination Clause:</strong> The NSTP administration reserves the right to terminate your participation in the program for violations of these terms, university policies, or any behavior deemed detrimental to the program or its participants. No refunds will be provided in cases of termination.</p>
        </div>
        <div class="modal-buttons">
            <button class="agree-btn">I Agree</button>
            <button class="disagree-btn">I Disagree</button>
        </div>
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
                        showModal('Account Registered. Go to Login Page.');
                    });
            } else {
                console.error('Form with ID "registerForm" not found');
            }
        }

        // Attach the submit_register function to the form submission
        document.getElementById('registerForm').addEventListener('submit', submit_register);

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


     // Terms and conditions handling
     const termsCheckbox = document.querySelector('input[name="terms"]');
    const termsModal = document.getElementById('termsModal');
    const agreeBtn = termsModal.querySelector('.agree-btn');
    const disagreeBtn = termsModal.querySelector('.disagree-btn');

    termsCheckbox.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent checkbox from being checked immediately
        termsModal.style.display = 'block';
    });

    agreeBtn.addEventListener('click', function() {
        termsCheckbox.checked = true;
        termsModal.style.display = 'none';
    });

    disagreeBtn.addEventListener('click', function() {
        termsCheckbox.checked = false;
        termsModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === termsModal) {
            termsModal.style.display = 'none';
            termsCheckbox.checked = false;
        }
    });

       // Function to toggle password visibility
       function setupPasswordToggle(toggleElement, passwordInput) {
        const toggleIcon = toggleElement.querySelector('i');
        
        toggleElement.addEventListener('click', function() {
            // toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // toggle the icon
            toggleIcon.classList.toggle('bx-show-alt');
            toggleIcon.classList.toggle('bx-hide');
        });
    }

    // Setup toggle for password field
    const passwordToggle = document.querySelector('#togglePassword').parentElement;
    const passwordInput = document.querySelector('#password');
    setupPasswordToggle(passwordToggle, passwordInput);

    // Setup toggle for confirm password field
    const confirmPasswordToggle = document.querySelector('#toggleConfirmPassword').parentElement;
    const confirmPasswordInput = document.querySelector('#confirm_password');
    setupPasswordToggle(confirmPasswordToggle, confirmPasswordInput);

    // Add this to your existing script section
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            event.preventDefault(); // Prevent form submission
            alert('Passwords do not match!');
            return false;
        }
    });

    // Real-time password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match!');
        } else {
            this.setCustomValidity('');
        }
    });

    // Update validation when primary password changes
    document.getElementById('password').addEventListener('input', function() {
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value) {
            if (this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match!');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    });

    // Add this to your existing script section
    document.getElementById('username').addEventListener('input', function() {
        const username = this.value.trim();
        if (username.length > 0) {
            // Create form data
            const formData = new FormData();
            formData.append('username', username);
            
            // Send request to check username
            fetch('check_username.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.taken) {
                    this.setCustomValidity('Username already taken');
                    // Add visual feedback
                    this.style.borderColor = '#ff6b6b';
                    // Show message to user
                    document.getElementById('username-message').textContent = 'Username already taken';
                    document.getElementById('username-message').style.color = '#ff6b6b';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#51cf66';
                }
            });
        }
    });


// Add this to your existing script section
document.getElementById('username').addEventListener('input', function() {
    const username = this.value.trim();
    if (username.length > 0) {
        // Create form data
        const formData = new FormData();
        formData.append('username', username);
        
        // Send request to check username
        fetch('check_username.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.taken) {
                this.setCustomValidity('Username already taken');
                // Add visual feedback
                this.style.borderColor = '#ff6b6b';
                // Show message to user
                document.getElementById('username-message').textContent = 'Username already taken';
                document.getElementById('username-message').style.color = '#ff6b6b';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#51cf66';
                document.getElementById('username-message').textContent = 'Username available';
                document.getElementById('username-message').style.color = '#51cf66';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});

    // Add this to prevent form submission if username is taken
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        const username = document.getElementById('username');
        if (username.value.trim() === '' || username.validity.customError) {
            event.preventDefault();
            alert('Please choose a different username.');
            return false;
        }
    });

    </script>
</body>

</html>