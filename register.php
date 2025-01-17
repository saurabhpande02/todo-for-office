<?php
session_start();

// Database configuration
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "pro_1todojs";

// Enable mysqli exceptions for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create a new mysqli connection
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    $conn->set_charset("utf8mb4"); // Set charset to handle special characters
} catch (mysqli_sql_exception $e) {
    // Handle connection errors
    $_SESSION['error_message'] = "Database connection failed: " . $e->getMessage();
    header("Location: register.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_btn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input
    if ($username === '' || $password === '') {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: register.php");
        exit();
    }

    try {
        // Prepare an SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Bind parameters and execute the statement
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();

        // Registration successful
        $_SESSION['success_message'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        // Duplicate entry error code is 1062
        if ($e->getCode() == 1062) {
            $_SESSION['error_message'] = "Username already exists. Please choose a different username.";
        } else {
            $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
        }
        header("Location: register.php");
        exit();
    } catch (Exception $e) {
        // Handle other exceptions
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        /* Basic styling for better appearance */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f2f2f2; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
        }
        .container { 
            width: 350px; 
            padding: 20px; 
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        }
        h2 { text-align: center; color: #333; }
        form { 
            display: flex; 
            flex-direction: column; 
        }
        input[type="text"], input[type="password"] { 
            padding: 10px; 
            margin-bottom: 15px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            font-size: 16px;
        }
        input[type="submit"] { 
            padding: 10px; 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
        }
        input[type="submit"]:hover { 
            background-color: #45a049; 
        }
        .message { 
            text-align: center; 
            margin-bottom: 15px; 
            font-size: 14px;
        }
        .error { color: red; }
        .success { color: green; }
        .link { text-align: center; margin-top: 10px; }
        .link a { color: #4CAF50; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        
        <!-- Display Error Message -->
        <?php
        if(isset($_SESSION['error_message'])) {
            echo "<div class='message error'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Display Success Message -->
        <?php
        if(isset($_SESSION['success_message'])) {
            echo "<div class='message success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="submit" name="register_btn" value="Register" />
        </form>
        <div class="link">
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </div>
    </div>
</body>
</html>
