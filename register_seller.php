<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/Form.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include custom JavaScript file -->
    <script src="js/ajax.js"></script>
</head>
<body>
    <div class="container">
        <form id="register-form" action="assets/register-form.php" method="post">
            <h1>Register</h1>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" required id="first_name" name="first_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" required id="last_name" name="last_name">
            </div>  
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" required id="username" name="username">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" required id="email" name="email">
            </div>
            <div class="form-group">
                <label for="password">Create Password</label>
                <input type="password" class="form-control" required id="password_hash" name="password">
            </div>
            <div class="form-group">
                <label for="contact_no">Contact</label>
                <input type="text" class="form-control" required id="contact_no" name="contact_no">
            </div>
            <div class="register">
                <a href="register_seller.php">Register as Student</a>
                <a href="login.php">Login</a>
            </div>
            <button type="submit" class="btn">Create</button>
        </form>
    </div>

    <script>
        // Prevent navigation through the back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>
