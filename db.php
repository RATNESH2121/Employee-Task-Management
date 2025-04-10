<?php
// Database credentials
$host = 'localhost';
$dbname = 'management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
    
    // Make PDO connection available globally
    $conn = $pdo;
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


function registerUser($username, $email, $password, $name, $role) {
    global $pdo;
    
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, role) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$username, $email, $hashedPassword, $name, $role]);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}
?>
