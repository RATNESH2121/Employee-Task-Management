<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: index.php");
    exit();
}

include('db.php');

// Fetch employee's tasks
$stmt = $pdo->prepare("
    SELECT tasks.*, users.name as employee_name 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id 
    WHERE tasks.assigned_to = ?
    ORDER BY tasks.deadline ASC
");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Tasks | TaskFlow Pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
        }
        .content-area {
            background-image: 
                radial-gradient(at 50% 0%, rgba(129, 140, 248, 0.1) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(192, 132, 252, 0.1) 0, transparent 50%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .table-row-hover:hover {
            background-color: #f3f0ff;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6C63FF, #B57DFF);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -10px rgba(108, 99, 255, 0.5);
        }
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="sidebar w-64 h-screen fixed left-0 top-0 text-white p-6">
        <div class="flex items-center space-x-2 mb-8">
            <span class="text-3xl">📋</span>
            <div>
                <span class="text-xl font-bold">TaskFlow Pro</span>
                <p class="text-sm text-white/70">Employee Dashboard</p>
            </div>
        </div>
        
        <nav class="space-y-4">
            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl bg-white/10 backdrop-blur">
                <i class="fas fa-tasks"></i>
                <span>My Tasks</span>
            </a>
            <a href="logout.php" class="flex items-center space-x-3 p-3 mt-auto rounded-xl bg-gradient-to-r from-red-400 to-red-500 hover:opacity-90 transition">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <!-- Add this button right after the main content starts -->
    <main class="content-area ml-64 flex-1 p-8 transition-all duration-300" id="mainContent">
        <!-- Add this button -->
        <button id="sidebarToggle" class="fixed top-6 left-6 z-50 p-2 rounded-lg bg-white/80 backdrop-blur shadow-lg hover:bg-white/90 lg:hidden">
            <i class="fas fa-bars text-gray-600"></i>
        </button>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Tasks</h1>
            <p class="text-gray-600 mt-2">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?></p>
        </header>
    
        <!-- Task List -->
        <div class="glass-card p-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Title</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Description</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Deadline</th>
                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Status</th>
                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr class="table-row-hover border-b border-gray-100">
                        <td class="py-4 px-6 font-medium text-gray-900"><?= htmlspecialchars($task['title']) ?></td>
                        <td class="py-4 px-6 text-gray-600">
                            <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>...
                        </td>
                        <td class="py-4 px-6 text-gray-600">
                            <?= date('M d, Y', strtotime($task['deadline'])) ?>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-indigo-100 text-indigo-800' ?>">
                                <?= ucfirst($task['status']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex justify-center space-x-3">
                                <?php if ($task['status'] !== 'completed'): ?>
                                    <button onclick="showCompleteModal(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>')"
                                            class="text-green-600 hover:text-green-700 transition-colors">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                                <button onclick="showViewModal(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>', '<?= htmlspecialchars($task['description']) ?>', '<?= date('M d, Y', strtotime($task['deadline'])) ?>', '<?= $task['status'] ?>')"
                                        class="text-indigo-600 hover:text-indigo-700 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </main>
    
    <!-- Add this script before closing body tag -->
    <script>
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
    
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            mainContent.classList.toggle('ml-0');
            mainContent.classList.toggle('ml-64');
        });
    </script>

    <!-- Modals with updated styling -->
    <div id="viewTaskModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="modal-content p-8 max-w-md w-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900" id="viewTaskTitle"></h3>
                <button onclick="hideViewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <p id="viewTaskDescription" class="mt-1 text-gray-600"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deadline</label>
                        <p id="viewTaskDeadline" class="mt-1 text-gray-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p id="viewTaskStatus" class="mt-1"></p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="hideViewModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="completeTaskModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="modal-content p-8 max-w-md w-full">
            <div class="text-center mb-8">
                <div class="inline-block p-3 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Complete Task</h2>
                <p class="text-gray-600 mt-2">Are you sure you want to mark this task as completed?</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-medium text-gray-900" id="completeTaskTitle"></h3>
            </div>

            <form id="completeTaskForm" method="POST" action="mark_complete.php" class="flex justify-end space-x-4">
                <input type="hidden" name="task_id" id="completeTaskId">
                <button type="button" onclick="hideCompleteModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Complete Task
                </button>
            </form>
        </div>
    </div>

    <script>
        function showViewModal(taskId, title, description, deadline, status) {
            document.getElementById('viewTaskTitle').textContent = title;
            document.getElementById('viewTaskDescription').textContent = description;
            document.getElementById('viewTaskDeadline').textContent = deadline;
            
            const statusElement = document.getElementById('viewTaskStatus');
            if (status === 'completed') {
                statusElement.innerHTML = `<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Completed</span>`;
            } else {
                statusElement.innerHTML = `<span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">Pending</span>`;
            }
            
            document.getElementById('viewTaskModal').classList.remove('hidden');
        }

        function hideViewModal() {
            document.getElementById('viewTaskModal').classList.add('hidden');
        }

        function showCompleteModal(taskId, title) {
            document.getElementById('completeTaskId').value = taskId;
            document.getElementById('completeTaskTitle').textContent = title;
            document.getElementById('completeTaskModal').classList.remove('hidden');
        }

        function hideCompleteModal() {
            document.getElementById('completeTaskModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewTaskModal');
            const completeModal = document.getElementById('completeTaskModal');
            
            if (event.target === viewModal) {
                hideViewModal();
            }
            if (event.target === completeModal) {
                hideCompleteModal();
            }
        }
    </script>
</body>
</html>
