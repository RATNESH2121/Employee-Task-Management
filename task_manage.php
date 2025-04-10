<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

// Fetch all tasks with employee names
$stmt = $pdo->query("
    SELECT tasks.*, users.name as employee_name 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id 
    ORDER BY tasks.deadline ASC
");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employees for the add task form
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'employee'");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Tasks | Task Manager</title>
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
        .sidebar {
            backdrop-filter: blur(16px);
            background-color: rgba(30, 41, 59, 0.85);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="pattern">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 min-h-screen text-white px-6 py-8 hidden md:block">
            <div class="flex items-center space-x-3 mb-8">
                <div class="text-3xl">📋</div>
                <div>
                    <h2 class="text-2xl font-bold">Admin Panel</h2>
                    <p class="text-sm text-white/70">Task Management System</p>
                </div>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
                    <i class="fas fa-home mr-3"></i> Dashboard
                </a>
                <a href="admin.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
                    <i class="fas fa-users mr-3"></i> Manage Employees
                </a>
                <a href="task_manage.php" class="flex items-center px-4 py-3 bg-white/10 rounded-lg group transition-all">
                    <i class="fas fa-tasks mr-3"></i> Manage Tasks
                </a>
                <a href="logout.php" class="flex items-center px-4 py-3 bg-red-500/50 hover:bg-red-500/70 rounded-lg group transition-all mt-8">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white/15 backdrop-blur-md text-white relative z-10 border-b border-white/10">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex justify-between items-center h-24">
                        <h1 class="text-2xl font-bold">Manage Tasks</h1>
                        <div class="flex items-center space-x-4">
                            <button onclick="document.getElementById('addTaskModal').classList.remove('hidden')"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-plus mr-2"></i>Add New Task
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Task List -->
            <main class="p-6">
                <div class="glass p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-gray-500 text-sm">
                                    <th class="px-6 py-3 text-left">Title</th>
                                    <th class="px-6 py-3 text-left">Assigned To</th>
                                    <th class="px-6 py-3 text-left">Deadline</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($tasks as $task): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($task['title']) ?></td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= $task['employee_name'] ? htmlspecialchars($task['employee_name']) : 'Unassigned' ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= date('M d, Y', strtotime($task['deadline'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-sm <?= $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= ucfirst($task['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="edit_task.php?id=<?= $task['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800 mx-2">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="showDeleteModal(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>', '<?= htmlspecialchars($task['employee_name']) ?>', '<?= date('M d, Y', strtotime($task['deadline'])) ?>', '<?= $task['status'] ?>')"
                                           class="text-red-600 hover:text-red-800 mx-2">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div id="addTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="glass p-8 rounded-lg w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Add New Task</h3>
                <button onclick="document.getElementById('addTaskModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="add_task.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                    <input type="text" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select name="assigned_to" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" name="deadline" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                            onclick="document.getElementById('addTaskModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Delete Task Modal -->
    <div id="deleteTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="glass p-8 max-w-md w-full">
            <div class="text-center mb-8">
                <div class="inline-block p-3 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Delete Task</h2>
                <p class="text-gray-600 mt-2">Are you sure you want to delete this task?</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-medium text-gray-900" id="deleteTaskTitle"></h3>
                <p class="text-gray-600 text-sm mt-1" id="deleteTaskAssignee"></p>
                <p class="text-gray-600 text-sm mt-1" id="deleteTaskDeadline"></p>
                <p class="text-gray-600 text-sm mt-1" id="deleteTaskStatus"></p>
            </div>

            <div class="text-gray-600 text-sm mb-6">
                <i class="fas fa-info-circle mr-2"></i>
                This action cannot be undone. The task will be permanently deleted.
            </div>

            <form id="deleteTaskForm" method="POST" class="flex justify-end space-x-4">
                <button type="button" onclick="hideDeleteModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash-alt mr-2"></i>Delete Task
                </button>
            </form>
        </div>
    </div>

    <script>
        function showDeleteModal(taskId, title, employee, deadline, status) {
            document.getElementById('deleteTaskTitle').textContent = title;
            document.getElementById('deleteTaskAssignee').textContent = 'Assigned to: ' + employee;
            document.getElementById('deleteTaskDeadline').textContent = 'Deadline: ' + deadline;
            document.getElementById('deleteTaskStatus').textContent = 'Status: ' + status.charAt(0).toUpperCase() + status.slice(1);
            document.getElementById('deleteTaskForm').action = 'delete_task.php?id=' + taskId;
            document.getElementById('deleteTaskModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteTaskModal').classList.add('hidden');
        }
    </script>
</body>
</html>