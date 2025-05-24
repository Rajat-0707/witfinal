<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Check authentication
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = new mysqli("localhost", "root", "", "wit");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("A database error occurred. Please try again later.");
}

// Check if task table exists, create if not
$check_table = $conn->query("SHOW TABLES LIKE 'task'");
if ($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE task (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task VARCHAR(255) NOT NULL,
        status ENUM('pending', 'completed') DEFAULT 'pending',
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($create_table)) {
        die("Failed to create task table: " . $conn->error);
    }
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security validation failed.";
    } else {
        // Add task
        if (isset($_POST['add'])) {
            $task = trim($_POST['task'] ?? '');
            if (empty($task)) {
                $error = "Task cannot be empty.";
            } elseif (strlen($task) > 255) {
                $error = "Task must be 255 characters or less.";
            } else {
                $stmt = $conn->prepare("INSERT INTO task (task, status, user_id) VALUES (?, 'pending', ?)");
                $stmt->bind_param("si", $task, $user_id);
                if ($stmt->execute()) {
                    $success = "Task added successfully!";
                } else {
                    $error = "Failed to add task.";
                }
            }
        }
        // Edit task
        elseif (isset($_POST['edit'])) {
            $id = intval($_POST['id'] ?? 0);
            $task = trim($_POST['task'] ?? '');
            
            // Verify task ownership first
            $check = $conn->prepare("SELECT id FROM task WHERE id=? AND user_id=?");
            $check->bind_param("ii", $id, $user_id);
            $check->execute();
            
            if ($check->get_result()->num_rows === 0) {
                $error = "Task not found or access denied.";
            } elseif (empty($task)) {
                $error = "Task cannot be empty.";
            } else {
                $stmt = $conn->prepare("UPDATE task SET task=? WHERE id=? AND user_id=?");
                $stmt->bind_param("sii", $task, $id, $user_id);
                if ($stmt->execute()) {
                    $success = "Task updated successfully!";
                } else {
                    $error = "Failed to update task.";
                }
            }
        }
    }
}

// Delete task (GET request with confirmation)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Verify task ownership
    $check = $conn->prepare("SELECT id FROM task WHERE id=? AND user_id=?");
    $check->bind_param("ii", $id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 1) {
        $stmt = $conn->prepare("DELETE FROM task WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            $success = "Task deleted successfully!";
        } else {
            $error = "Failed to delete task.";
        }
    } else {
        $error = "Task not found or access denied.";
    }
    
    // Redirect to prevent refresh issues
    header("Location: home1.php?success=" . urlencode($success));
    exit();
}

// Toggle task status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    
    // Verify task ownership
    $check = $conn->prepare("SELECT id FROM task WHERE id=? AND user_id=?");
    $check->bind_param("ii", $id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 1) {
        $stmt = $conn->prepare("UPDATE task SET status = CASE WHEN status = 'pending' THEN 'completed' ELSE 'pending' END WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            $success = "Task status updated!";
        }
    }
    
    header("Location: home1.php?success=" . urlencode($success));
    exit();
}

// Get success message from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Home</title>
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
        }
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .p1 {
            font-size: 28px;
            font-weight: bold;
            color: #2d6cdf;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .welcome {
            color: #333;
            font-weight: 500;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .box4 {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
        }
        .p4 {
            flex: 1;
            padding: 12px;
            border: 1px solid #b6c6e0;
            border-radius: 6px;
            font-size: 16px;
        }
        .add {
            background: linear-gradient(90deg, #2d6cdf 60%, #6fb1fc 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .add:hover {
            background: linear-gradient(90deg, #1b4fa0 60%, #4e8edb 100%);
        }
        .tasklistcontainer {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .p3 {
            font-size: 20px;
            font-weight: bold;
            color: #2d6cdf;
            margin-bottom: 20px;
        }
        .task-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .task-item.completed {
            background: #d4edda;
            opacity: 0.8;
        }
        .task-item.completed .task-text {
            text-decoration: line-through;
        }
        .task-text {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        .task-actions {
            display: flex;
            gap: 5px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-toggle {
            background: #28a745;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .no-tasks {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        /* ... rest of your CSS ... */
    </style>
</head>
<body>
    <div class="header">
        <div class="box1">
            <p class="p1">TASK MANAGER</p>
        </div>
        <div class="user-info">
            <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form class="box4" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="task" class="p4" placeholder="Write your task here..." required maxlength="255">
            <button class="add" type="submit" name="add">Add Task</button>
        </form>
        
        <div class="tasklistcontainer">
            <p class="p3">Your Tasks:</p>
            
            <?php
            $stmt = $conn->prepare("SELECT * FROM task WHERE user_id=? ORDER BY id DESC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $tasks = $stmt->get_result();
            
            if ($tasks->num_rows > 0) {
                while ($row = $tasks->fetch_assoc()) {
                    $status = $row['status'] ?? 'pending';
                    $completed_class = ($status === 'completed') ? 'completed' : '';
                    echo "<div class='task-item $completed_class'>
                            <form method='post' style='flex: 1; display: flex; gap: 10px;'>
                                <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='task' value='" . htmlspecialchars($row['task']) . "' class='task-text'>
                                <div class='task-actions'>
                                    <button type='submit' name='edit' class='btn btn-edit'>Edit</button>
                                    <a href='?toggle={$row['id']}' class='btn btn-toggle'>" . 
                                        ($status === 'completed' ? 'Undo' : 'Done') . 
                                    "</a>
                                    <a href='?delete={$row['id']}' class='btn btn-delete' 
                                       onclick=\"return confirm('Are you sure you want to delete this task?')\">Delete</a>
                                </div>
                            </form>
                          </div>";
                }
            } else {
                echo "<div class='no-tasks'>No tasks yet. Add your first task above!</div>";
            }
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>