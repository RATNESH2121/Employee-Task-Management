<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: index.php");
    exit();
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $employee_id = $_SESSION['user_id'];

    try {
        // Verify the task belongs to this employee
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
        $stmt->execute([$task_id, $employee_id]);
        $task = $stmt->fetch();

        if ($task) {
            // Update task status to completed
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
            $stmt->execute([$task_id]);
            
            header("Location: employee.php?success=1");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: employee.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: employee.php");
exit();