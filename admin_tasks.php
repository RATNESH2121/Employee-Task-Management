<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

// Fetch all tasks with employee names
$stmt = $pdo->prepare("
    SELECT tasks.*, users.name as employee_name 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id 
    ORDER BY tasks.deadline ASC
");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all employees for the assignment dropdown
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'employee'");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks | TaskFlow Pro</title>
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
        .btn-gradient {
            background: linear-gradient(135deg, #6C63FF, #B57DFF);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -10px rgba(108, 99, 255, 0.5);
        }
    </style>
</head>
<body class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="sidebar w-64 h-screen fixed left-0 top-0 text-white p-6">
        <div class="flex items-center space-x-2 mb-8">
            <span class="text-3xl">📋</span>
            <span class="text-2xl font-bold">TaskFlow Pro</span>
        </div>
        
        <nav class="space-y-4">
            <a href="admin.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-white/5 transition">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
            <a href="admin_tasks.php" class="flex items-center space-x-3 p-3 rounded-xl bg-white/10 backdrop-blur">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
            </a>
            <a href="logout.php" class="flex items-center space-x-3 p-3 mt-auto rounded-xl bg-gradient-to-r from-red-400 to-red-500 hover:opacity-90 transition">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="content-area ml-64 flex-1 p-8">
        <div class="max-w-7xl mx-auto">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Manage Tasks</h1>
                <p class="text-gray-600 mt-2">Assign and monitor tasks for all employees</p>
            </header>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <button onclick="showAddTaskModal()" 
                        class="btn-gradient px-6 py-3 text-white rounded-xl font-medium flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Add New Task</span>
                </button>
            </div>

            <div class="glass-card p-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Task</th>
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Assigned To</th>
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Deadline</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($task['title']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($task['description'], 0, 60)) ?>...</div>
                            </td>
                            <td class="py-4 px-4 text-gray-600"><?= htmlspecialchars($task['employee_name']) ?></td>
                            <td class="py-4 px-4 text-gray-600"><?= date('M d, Y', strtotime($task['deadline'])) ?></td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-medium 
                                    <?= $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-indigo-100 text-indigo-800' ?>">
                                    <?= ucfirst($task['status']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex justify-end space-x-3">
                                    <button onclick="editTask(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>', '<?= htmlspecialchars($task['description']) ?>', '<?= $task['deadline'] ?>', <?= $task['assigned_to'] ?>)"
                                            class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteTask(<?= $task['id'] ?>)"
                                            class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-trash-alt"></i>
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

    <!-- Task Modal -->
    <div id="taskModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-md w-full p-8">
            <h3 class="text-2xl font-bold mb-6" id="modalTitle">Add New Task</h3>
            <form action="task_actions.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="task_id" id="taskId">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="taskTitle" required
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="taskDescription" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                            rows="3"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select name="assigned_to" id="taskAssignedTo" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" name="deadline" id="taskDeadline" required
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideTaskModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn-gradient px-6 py-2 text-white rounded-xl">
                        Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddTaskModal() {
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.querySelector('form').reset();
            document.querySelector('[name="action"]').value = 'add';
            document.getElementById('taskId').value = '';
        }

        function hideTaskModal() {
            document.getElementById('taskModal').classList.add('hidden');
        }

        function editTask(id, title, description, deadline, assignedTo) {
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Task';
            document.querySelector('[name="action"]').value = 'edit';
            document.getElementById('taskId').value = id;
            document.getElementById('taskTitle').value = title;
            document.getElementById('taskDescription').value = description;
            document.getElementById('taskDeadline').value = deadline;
            document.getElementById('taskAssignedTo').value = assignedTo;
        }

        function deleteTask(id) {
            if (confirm('Are you sure you want to delete this task?')) {
                window.location.href = `task_actions.php?action=delete&id=${id}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        }
    </script>
</body>
</html>