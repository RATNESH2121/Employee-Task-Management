<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $assigned_to = $_POST['assigned_to'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, deadline, assigned_to, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$title, $description, $deadline, $assigned_to]);
        header("Location: admin.php?success=Task added successfully");
    } catch (PDOException $e) {
        header("Location: admin.php?error=Failed to add task");
    }
    exit();
}
?>