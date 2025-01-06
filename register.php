<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/form.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Body background styling */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(45deg, #2e8b57, #042d86);
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 18px;
        }

        .modal-content h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        .modal-content p {
            line-height: 1.5;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: red;
            text-decoration: none;
        }

        /* Styling for terms checkbox */
        .terms-container {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }

        .terms-container label {
            margin-left: 5px;
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
            font-size: 18px;
        }

        .terms-container label:hover {
            text-decoration: none;
        }

        button[disabled] {
            background-color: gray;
            cursor: not-allowed;
        }

        /* Form styling */
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .form-control:focus {
            border-color: #007bff;
            outline: none;
        }

        small {
            font-size: 14px;
            color: red;
        }

        /* Button Styling */
        button.btn {
            display: block; /* Ensures the button behaves as a block-level element */
            width: 40%;
            padding: 10px;
            background-color: green;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 10px auto; /* Center the button and add spacing between buttons */
            text-align: center; /* Centers text inside the button */
        }

        button.btn:hover {
            background-color: darkgreen;
        }

        /* Register link */
        .register {
            text-align: center;
            margin-top: 10px;
        }

        .register a {
            font-size: 16px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .register a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="register-form" action="assets/register-form.php" method="POST">
            <h1>Register</h1>

            <!-- User Name -->
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" class="form-control" required id="username" name="username">
                <small id="usernameHelp" class="form-text text-muted"></small>
            </div>

            <!-- First Name -->
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" required id="first_name" name="first_name">
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" required id="last_name" name="last_name">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" required id="email" name="email">
            </div>

            <!-- Create Password -->
            <div class="form-group">
                <label for="password">Create Password</label>
                <input type="password" class="form-control" required id="password" name="password" minlength="7">
                <small id="passwordHelp" class="form-text text-muted"></small>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" required id="confirm_password" name="confirm_password">
                <small id="confirmPasswordHelp" class="form-text text-muted"></small>
            </div>

            <!-- Student Number -->
            <div class="form-group">
                <label for="student_number">Student Number</label>
                <input type="text" class="form-control" required id="student_number" name="student_number">
            </div>
             <!-- Contact Number -->
             <div class="form-group">
                <label for="contact_no">Contact Number</label>
                <input type="contact_no" class="form-control" required id="contact_no" name="contact_no">
            </div>

            <!-- Course -->
            <div class="form-group">
                <label for="course">Course</label>
                <input type="text" class="form-control" required id="course" name="course">
            </div>

            <!-- Section -->
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" class="form-control" required id="section" name="section">
            </div>

            <!-- Register Link -->
            <div class="register">
                <a href="login.php">Login</a>
            </div>

            <!-- Terms and Conditions -->
            <div class="terms-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" id="termsLabel">I agree to the <span id="termsText">Terms and Conditions</span></label>
            </div>

            <!-- Buttons Container -->
            <div class="buttons-container" style="text-align: center;">
                <!-- Submit Button -->
                <button type="submit" class="btn">Create</button>
                <!-- Go to Home button -->
                <button type="button" onclick="window.location.href='login.php'" class="btn">Go Back</button>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Terms and Conditions</h2>
            <p>
                By using our service, you agree to the following terms and conditions. We value your privacy and will protect your personal information in accordance with the Privacy Act. Your data will not be shared with third parties without your consent. By agreeing, you acknowledge and accept these conditions.
            </p>
        </div>
    </div>

    <script>
        // Prevent navigation through the back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Validate password field
        document.getElementById('password').addEventListener('input', function () {
            const passwordField = this;
            const feedback = document.getElementById('passwordHelp');

            // Remove spaces while typing
            passwordField.value = passwordField.value.replace(/\s/g, '');

            // Check length
            if (passwordField.value.length < 7) {
                feedback.textContent = "Password must be at least 7 characters long.";
                feedback.style.color = "red";
            } else {
                feedback.textContent = "";
            }
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function () {
            const confirmPasswordField = this;
            const passwordField = document.getElementById('password');
            const feedback = document.getElementById('confirmPasswordHelp');

            // Check if passwords match
            if (confirmPasswordField.value !== passwordField.value) {
                feedback.textContent = "Passwords do not match.";
                feedback.style.color = "red";
            } else {
                feedback.textContent = "";
            }
        });

        // Modal functionality
        const modal = document.getElementById("termsModal");
        const termsText = document.getElementById("termsText");
        const closeModal = document.getElementsByClassName("close")[0];

        termsText.onclick = function () {
            modal.style.display = "block";
        };

        closeModal.onclick = function () {
            modal.style.display = "none";
        };

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
</html>