<?php
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "wit");
    if ($conn->connect_error) {
        $error = "Database connection failed. Please try again later.";
    } else {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $age = intval($_POST['age']);

        // Basic validation
        if (empty($name) || empty($username) || empty($password) || $age <= 0) {
            $error = "Please fill in all fields with valid information.";
        } else {
            // Check if username already exists
            $check = $conn->query("SELECT id FROM users WHERE username='$username'");
            if ($check && $check->num_rows > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Insert new user
                $sql = "INSERT INTO users (name, username, password, age) VALUES ('$name', '$username', '$password', $age)";
                if ($conn->query($sql)) {
                    $success = "Registration successful! You can now <a href='index.php' style='color: #2d6cdf;'>login here</a>.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
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
    <title>Registration Form</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        form {
            background: #fff;
            max-width: 400px;
            margin: 60px auto;
            padding: 32px 28px 24px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
            border: none;
        }
        h2 {
            text-align: center;
            color: #2d6cdf;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 16px;
            margin-bottom: 6px;
            color: #333;
            font-weight: 500;
        }

        input{
            display: block;
            margin-top: 16px;
            margin-bottom: 32px;
            border: none;
            height: 40px;
        }

        ::placeholder {
            color: #aaa;
        }
        
        input[type="text"], input[type="password"], input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            /* border: 1px solid #b6c6e0; */
            border-radius: 6px;
            font-size: 1em;
            background: #f7faff;
            transition: border 0.2s;
            margin-bottom: 2px;
            background-color: white;
        }
        input[type="text"]:focus, input[type="password"]:focus, input[type="number"]:focus {
            border: 1.5px solid #2d6cdf;
            outline: none;
            background: #eaf1fb;

        }
        .error {
            color: #e74c3c;
            font-size: 0.92em;
            margin-bottom: 15px;
            padding: 10px;
            background: #fdf2f2;
            border-radius: 6px;
            text-align: center;
        }
        .success {
            color: #27ae60;
            font-size: 0.92em;
            margin-bottom: 15px;
            padding: 10px;
            background: #f2f8f2;
            border-radius: 6px;
            text-align: center;
        }
        button[type="submit"] {
            width: 100%;
            background: linear-gradient(90deg, #2d6cdf 60%, #6fb1fc 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 1.08em;
            font-weight: 600;
            margin-top: 22px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(45, 108, 223, 0.08);
            transition: background 0.2s, transform 0.1s;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg, #1b4fa0 60%, #4e8edb 100%);
            transform: translateY(-2px) scale(1.01);
        }
        .login-link {
            text-align: center;
            margin-top: 16px;
            color: #333;
        }
        .login-link a {
            color: #2d6cdf;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form action="" method="post" id="registerForm">
    <h2>Register for Task Manager</h2>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?> 
    
    <!-- <label for="name">Full Name:</label> -->
    <input placeholder="enter your full name"  type="text" name="name" id="name" required maxlength="100" 
           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"> 
    <hr>
    
    <!-- <label for="username">Username:</label> -->
    <input  placeholder="enter your username" type="text" name="username" id="username" required maxlength="50"
            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"> 
            <hr>
<!--     
    <label for="password">Password:</label> -->
    <input placeholder="create your password" type="password" name="password" id="password" required>
<hr>
    <!-- <label for="password">confirm Password:</label> -->
    <input placeholder="reenter your password"  type="password" name="password" id="Cpassword" required>
    <hr>
    <!-- <label for="age">Age:</label> -->
    <input placeholder="enter your age"  type="number" name="age" id="age" min="13" max="120" required 
            value="<?php echo isset($_POST['age']) ? intval($_POST['age']) : ''; ?>"> 
    <hr>
    <button type="submit">Register</button>
</form>

<div class="login-link">
    Already have an account? <a href="index.php">Sign in here</a>
</div>

</body>
</html>