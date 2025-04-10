<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$error = '';

try {
    switch ($action) {
        case 'add':
            if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['assigned_to']) || empty($_POST['deadline'])) {
                throw new Exception('All fields are required');
            }
            
            $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, deadline, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['assigned_to'],
                $_POST['deadline']
            ]);
            $_SESSION['success'] = 'Task added successfully';
            break;

        case 'edit':
            if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['assigned_to']) || empty($_POST['deadline'])) {
                throw new Exception('All fields are required');
            }
            
            $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, assigned_to = ?, deadline = ? WHERE id = ?");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['assigned_to'],
                $_POST['deadline'],
                $_POST['task_id']
            ]);
            $_SESSION['success'] = 'Task updated successfully';
            break;

        case 'delete':
            if (empty($_GET['id'])) {
                throw new Exception('Task ID is required');
            }
            
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['success'] = 'Task deleted successfully';
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: admin_tasks.php");
exit();