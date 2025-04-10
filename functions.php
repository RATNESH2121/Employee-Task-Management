<?php
include 'db.php'; // Include the database connection file

// Function to check if a user exists
function checkUserExists($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
}

// Function to register a new user (admin or employee)
function registerUser($username, $email, $password, $name, $role = 'employee') {
    global $pdo;
    
    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, role) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword, $name, $role]);
}

// Function to authenticate the user during login
function authenticateUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Function to create a new session for a logged-in user
function createUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user'] = $user;
}

// Function to fetch all tasks
function getAllTasks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch a single task by ID
function getTaskById($taskId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to add a new task
function addTask($title, $description, $deadline) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, deadline, status) VALUES (?, ?, ?, 'Pending')");
    return $stmt->execute([$title, $description, $deadline]);
}

// Function to update task status (Mark as Completed or Pending)
function updateTaskStatus($taskId, $status) {
    global $pdo;
    
    $validStatuses = ['Pending', 'Completed'];
    if (!in_array($status, $validStatuses)) {
        return false; // Invalid status
    }
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $taskId]);
}

// Function to delete a task
function deleteTask($taskId) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    return $stmt->execute([$taskId]);
}

// Function to check if the user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user']);
}

// Function to get the current logged-in user
function getLoggedInUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Function to destroy a session (logout)
function logoutUser() {
    session_unset();
    session_destroy();
}

// Function to get tasks assigned to a specific user (optional, depends on your task structure)
function getTasksByUser($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isValidUsername($username) {
    // Username must be 5-20 characters long and contain both letters and numbers
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,20}$/', $username);
}
?>
