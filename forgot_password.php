<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SLSU NSTP</title>
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

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
        }

        .reset-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            width: 350px;
        }

        .reset-box h2 {
            color: white;
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: normal;
        }

        .form-group {
        margin-bottom: 20px;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        padding-left: 35px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-group i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #505050;  /* This makes the icons black */
        font-size: 18px;
    }

        .form-group input::placeholder {
            color: #666;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #218838;
        }

        .back-to-login {
            display: block;
            text-align: center;
            color: white;
            text-decoration: none;
            margin-top: 15px;
        }

        .message {
            color: white;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .success { background: rgba(40, 167, 69, 0.2); }
        .error { background: rgba(220, 53, 69, 0.2); }

        #passwordFields {
            display: none;
        }

        .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #000;
    }

        #password-message {
            display: block;
            font-size: 14px;
            margin-top: 5px;
            color: white;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group i.bx-show-alt,
        .form-group i.bx-hide {
            font-size: 18px;
            left: -20px;
        }


        .input-container {
        position: relative;
        width: 100%;
    }

    .input-container input {
        width: 100%;
        padding: 12px;
        padding-left: 35px;
        padding-right: 35px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.9);
    }

    .input-container i.bxs-lock {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #505050;
        font-size: 18px;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #000;
    }

    .validation-message {
        display: block;
        font-size: 14px;
        margin-top: 5px;
        color: #ff6b6b;
    }

    .message.success {
        background: rgba(40, 167, 69, 0.9);
        color: white;
        font-weight: bold;
        padding: 15px;
        margin: 20px 0;
        border-radius: 5px;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    <div class="container">
        <div class="reset-box">
            <h2>Forgot Password</h2>
            <form id="resetForm" action="process_reset_password.php" method="POST">
    <div id="verificationFields">
        <div class="form-group">
            <input type="text" name="first_name" placeholder="First Name" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="form-group">
            <input type="text" name="middle_name" placeholder="Middle Name" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="form-group">
            <input type="text" name="last_name" placeholder="Last Name" required>
            <i class='bx bxs-user'></i>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
            <i class='bx bxs-envelope'></i>
        </div>
        <div class="form-group">
            <input type="text" name="mobile" placeholder="Mobile Number" required>
            <i class='bx bxs-phone'></i>
        </div>
        <button type="button" onclick="verifyDetails()">Verify Details</button>
    </div>

    <div id="passwordFields">
    <div class="form-group">
        <div class="input-container">
            <i class='bx bxs-lock'></i>
            <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
            <span class="password-toggle">
                <i class='bx bx-show-alt' id="toggleNewPassword"></i>
            </span>
        </div>
        <span id="password-message" class="validation-message"></span>
    </div>
    <div class="form-group">
        <div class="input-container">
            <i class='bx bxs-lock'></i>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <span class="password-toggle">
                <i class='bx bx-show-alt' id="toggleConfirmPassword"></i>
            </span>
        </div>
    </div>
    <button type="submit">Reset Password</button>
</div>
</form>
            <a href="faculty" class="back-to-login">Back to Login</a>
        </div>
    </div>

    <script>
    function verifyDetails() {
        const formData = new FormData(document.getElementById('resetForm'));
        formData.append('action', 'verify');

        // Show loading state
        const verifyButton = document.querySelector('button[type="button"]');
        verifyButton.textContent = 'Verifying...';
        verifyButton.disabled = true;

        fetch('process_reset_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('verificationFields').style.display = 'none';
                document.getElementById('passwordFields').style.display = 'block';
                const successDiv = document.createElement('div');
                successDiv.className = 'message success';
                successDiv.textContent = 'Details verified successfully. Please enter your new password.';
                document.querySelector('.message')?.remove();
                document.querySelector('h2').insertAdjacentElement('afterend', successDiv);
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error';
                errorDiv.textContent = data.message || 'Invalid details. Please try again.';
                document.querySelector('.message')?.remove();
                document.querySelector('h2').insertAdjacentElement('afterend', errorDiv);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message to user
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error';
            errorDiv.textContent = 'An error occurred. Please try again.';
            document.querySelector('.message')?.remove();
            document.querySelector('h2').insertAdjacentElement('afterend', errorDiv);
        })
        .finally(() => {
            // Reset button state
            verifyButton.textContent = 'Verify Details';
            verifyButton.disabled = false;
        });
    }

    // Function to toggle password visibility
    function setupPasswordToggle(toggleElement, passwordInput) {
        const toggleIcon = toggleElement.querySelector('i');
        
        toggleElement.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            toggleIcon.classList.toggle('bx-show-alt');
            toggleIcon.classList.toggle('bx-hide');
        });
    }

    // Setup password toggles
    const newPasswordToggle = document.querySelector('#toggleNewPassword').parentElement;
    const newPasswordInput = document.querySelector('#new_password');
    setupPasswordToggle(newPasswordToggle, newPasswordInput);

    const confirmPasswordToggle = document.querySelector('#toggleConfirmPassword').parentElement;
    const confirmPasswordInput = document.querySelector('#confirm_password');
    setupPasswordToggle(confirmPasswordToggle, confirmPasswordInput);

    // Password validation
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value.trim();
        const passwordMessage = document.getElementById('password-message');
        
        // Regular expressions for validation
        const lengthPattern = /.{8,}/;
        const specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;

        if (!lengthPattern.test(password)) {
            this.setCustomValidity('Password must be at least 8 characters long.');
            passwordMessage.textContent = 'Password must be at least 8 characters long.';
            passwordMessage.style.color = '#white';
            this.style.borderColor = '#ff6b6b';
        } else if (!specialCharPattern.test(password)) {
            this.setCustomValidity('Password must contain at least one special character.');
            passwordMessage.textContent = 'Password must contain at least one special character (@, #, $, etc.).';
            passwordMessage.style.color = '#white';
            this.style.borderColor = '#white';
        } else {
            this.setCustomValidity('');
            passwordMessage.textContent = 'Password meets requirements!';
            passwordMessage.style.color = '#white';
            this.style.borderColor = '#white';
        }
    });

    // Confirm password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match!');
            this.style.borderColor = '#white';
        } else {
            this.setCustomValidity('');
            this.style.borderColor = '#white';
        }
    });

    // Form submission validation
    document.getElementById('resetForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const password = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.validity.customError || confirmPassword.validity.customError) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error';
            errorDiv.textContent = 'Please ensure your password meets all requirements and matches the confirmation.';
            document.querySelector('.message')?.remove();
            document.querySelector('h2').insertAdjacentElement('afterend', errorDiv);
            return false;
        }

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Resetting Password...';
        
        fetch('process_reset_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove any existing messages
                document.querySelector('.message')?.remove();
                
                // Create and show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'message success';
                successDiv.textContent = 'Password reset successful! Redirecting to login...';
                document.querySelector('h2').insertAdjacentElement('afterend', successDiv);
                
                // Disable form inputs and buttons
                const form = document.getElementById('resetForm');
                const inputs = form.querySelectorAll('input, button');
                inputs.forEach(input => input.disabled = true);
                
                // Add CSS to make success message more visible
                successDiv.style.cssText = `
                    background: rgba(40, 167, 69, 0.9);
                    color: white;
                    font-weight: bold;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 5px;
                    animation: fadeIn 0.5s;
                `;
                
                // Add animation keyframes
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                `;
                document.head.appendChild(style);
                
                // Ensure the message is visible for a moment before redirect
                setTimeout(() => {
                    window.location.href = 'faculty.php';
                }, 3000); // Increased delay to 3 seconds
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error';
                errorDiv.textContent = data.message || 'Failed to reset password. Please try again.';
                document.querySelector('.message')?.remove();
                document.querySelector('h2').insertAdjacentElement('afterend', errorDiv);
                
                submitButton.disabled = false;
                submitButton.textContent = 'Reset Password';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error';
            errorDiv.textContent = 'An error occurred. Please try again.';
            document.querySelector('.message')?.remove();
            document.querySelector('h2').insertAdjacentElement('afterend', errorDiv);
            
            submitButton.disabled = false;
            submitButton.textContent = 'Reset Password';
        });
    });
    </script>
</body>
</html>