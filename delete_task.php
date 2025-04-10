<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

$task_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT tasks.*, users.name as employee_name FROM tasks LEFT JOIN users ON tasks.assigned_to = users.id WHERE tasks.id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header("Location: task_manage.php");
    exit();
}

if (isset($_POST['confirm'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    if ($stmt->execute([$task_id])) {
        header("Location: task_manage.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Delete Task | Task Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-color: #1e1b4b;
            background-image: 
                url('https://raw.githubusercontent.com/tailwindlabs/tailwindcss.com/master/public/img/beams.jpg'),
                linear-gradient(135deg, #312e81, #4338ca);
            background-blend-mode: overlay;
            background-size: cover;
        }
        .glass {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }
        .pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="pattern flex items-center justify-center min-h-screen p-6">
    <div class="glass p-8 max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-block p-3 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Delete Task</h2>
            <p class="text-gray-600 mt-2">Are you sure you want to delete this task?</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($task['title']) ?></h3>
            <p class="text-gray-600 text-sm mt-1">Assigned to: <?= htmlspecialchars($task['employee_name']) ?></p>
            <p class="text-gray-600 text-sm mt-1">Deadline: <?= date('M d, Y', strtotime($task['deadline'])) ?></p>
            <p class="text-gray-600 text-sm mt-1">Status: <?= ucfirst($task['status']) ?></p>
        </div>

        <div class="text-gray-600 text-sm mb-6">
            <i class="fas fa-info-circle mr-2"></i>
            This action cannot be undone. The task will be permanently deleted.
        </div>

        <form method="POST" class="flex justify-end space-x-4">
            <a href="task_manage.php" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" name="confirm" value="1"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-trash-alt mr-2"></i>Delete Task
            </button>
        </form>
    </div>
</body>
</html>