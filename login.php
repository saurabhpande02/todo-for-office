<?php
session_start();

// Database configuration
require('db.php');



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input
    if ($username === '' || $password === '') {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: login.php");
        exit();
    }

    // Sanitize input
    $username = $conn->real_escape_string($username);

    // Prepare and execute SELECT statement
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No user found with that username.";
        header("Location: login.php");
        exit();
    }
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* Basic styling for better appearance */
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; }
        .container { width: 300px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; }
        input[type="text"], input[type="password"] { padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 3px; }
        input[type="submit"] { padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
        .message { text-align: center; margin-bottom: 15px; }
        .error { color: red; }
        .link { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        
        <!-- Display Error Message -->
        <?php
        if(isset($_SESSION['error_message'])) {
            echo "<div class='message error'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="submit" name="login_btn" value="Login" />
        </form>
        <div class="link">
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>
