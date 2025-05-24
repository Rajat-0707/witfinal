<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
    header("Location: home1.php");
    exit();
}

$error = "";
$success = "";

// Check for success message from registration
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = "Registration successful! Please login with your credentials.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "wit");
    if ($conn->connect_error) {
        $error = "Database connection failed. Please try again later.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            // Retrieve user data (plain text password comparison)
            $sql = "SELECT id, username, name, password FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Compare plain text passwords (INSECURE, but works if passwords are not hashed)
                if ($password === $user['password']) {
                    // Login successful
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    header("Location: home1.php");
                    exit();
                } else {
                    echo "<script>alert('Invalid username or password.');</script>";
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Login</title>
    <style>
        /* Your existing CSS styles remain unchanged */
        * { 
        box-sizing: border-box;
         margin: 0; 
         padding: 0; }
        body {
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        /* background-image: url('th.jpg');
        background-size: cover; */
         min-height: 100vh; }
        .container {
         display: flex;
          justify-content: center;
           align-items: center;
           min-height: 100vh;
        border: none;
     }
        .loginBox {
         background: rgba(255, 255, 255, 0.95);
         height: 500px;
         padding: 40px; 
        /* border-radius: 16px; */
         box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
         width: 100%; 
        max-width: 400px; }
        .tm {
         text-align: center;
         font-size: 20px;
          margin-bottom: 30px;
         font-weight: 600; }

        .login{
            margin-top: 20px;
        }
        .un, .pw {
         width: 100%; 
         padding: 12px;
         border: none;
         /* border: 1px solid #b6c6e0; */
         border-radius: 6px; 
        font-size: 16px;
         background: #f7faff;
         margin-bottom: 0px;
        height: 60px; }

        .loginbutton { 
            height: 60px;
            margin-top: 0px;
        width: 100%;
         background: linear-gradient(90deg, #2d6cdf 60%, #6fb1fc 100%); 
        color: white;
         border: none; border-radius: 6px; 
        padding: 12px; 
        font-size: 16px;
         font-weight: 600;
         cursor: pointer; 
        margin-top: 10px; }
        
        .loginbutton:hover {
         background: linear-gradient(90deg, #1b4fa0 60%, #4e8edb 100%); }
        
         .signup { text-align: center; 
        margin-top: 20px; 
        color: #333; }
        
        .signup a { 
        color: #2d6cdf;
         text-decoration: none; }
        
         .error { 
        color: #e74c3c;
         text-align: center; 
        margin-bottom: 15px; 
        padding: 10px; 
        background: #fdf2f2;
         border-radius: 6px; }
        
         .success {
             color: #27ae60;
          text-align: center; 
         margin-bottom: 15px;
           padding: 10px; 
           background: #f2f8f2; 
            border-radius: 6px; }

        .lb2{
                width: 350px;
                height: 500px;
                background-image: url('th2.jpg');
                background-size: cover;
                background-position: center;
                padding: 0px;
            }
        
            .lb2 img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        hr{
            margin-bottom: 15px;
            padding: 0px;
        }
        /* Removed invalid ::placeholder[ selector */
        
        .un::placeholder, .pw::placeholder {
           margin: 0px;
           padding: 0px;
            opacity: 1; /* Firefox */
        }
   </style>
</head>
<body>

<div class="container">
    <div class="loginBox">
        <div class="loginto">
            <p style="margin-left: 17px; font-size: 35px;  color: #2d6cdf;font-weight: 750;">WELCOME BACK!</p>
            <p class="tm">Sign in to Task Manager</p>
        </div>
        
        <form class="login-form" action="" method="post">
            <div class="username">
                <input class="un" type="text" name="username" placeholder="Username" required maxlength="50">
            </div>
            <hr>
            <div class="password">
                <input class="pw" type="password" name="password" placeholder="Password" required>
            </div>
            <hr>
            <div class="login">
                <button class="loginbutton" type="submit">Login</button>
            </div>
        </form>
        
        <div class="signup">
            <p>New user? <a href="register.php">Sign Up</a></p>
        </div>
    </div>

    <div class="lb2">
        <img src="th2.jpg" alt="">
    </div>
</div>
</body>
</html>