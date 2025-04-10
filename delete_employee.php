<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // First, delete or reassign any tasks assigned to this employee
        $stmt = $pdo->prepare("UPDATE tasks SET assigned_to = NULL WHERE assigned_to = ?");
        $stmt->execute([$id]);
        
        // Then delete the employee
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
        $stmt->execute([$id]);
        
        header("Location: admin.php?success=1");
        exit();
    } catch (PDOException $e) {
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: admin.php");
exit();