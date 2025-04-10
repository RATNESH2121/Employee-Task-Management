<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

// Fetch employees with their task counts
$stmt = $pdo->prepare("
    SELECT users.*, COUNT(tasks.id) as task_count 
    FROM users 
    LEFT JOIN tasks ON users.id = tasks.assigned_to 
    WHERE users.role = 'employee' 
    GROUP BY users.id
");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TaskFlow Pro</title>
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
        .btn-gradient {
            background: linear-gradient(135deg, #6C63FF, #B57DFF);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -10px rgba(108, 99, 255, 0.5);
        }
        .btn-danger {
            background: linear-gradient(135deg, #E57373, #EF5350);
        }
        .table-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .table-row-hover:hover {
            background-color: #f3f0ff;
        }
        .action-icon {
            transition: all 0.2s ease;
        }
        .action-icon:hover {
            transform: scale(1.1);
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
            <a href="admin.php" class="flex items-center space-x-3 p-3 rounded-xl bg-white/10 backdrop-blur">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
            <a href="admin_tasks.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-white/5 transition">
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
            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Manage Employees</h1>
                <p class="text-gray-600 mt-2">Overview of all registered employees and their tasks</p>
            </header>

            <!-- Action Button -->
            <div class="mb-6">
                <button onclick="showAddEmployeeModal()" 
                        class="btn-gradient px-6 py-3 text-white rounded-xl font-medium flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Add New Employee</span>
                </button>
            </div>

            <!-- Table -->
            <div class="table-container p-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Employee</th>
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Email</th>
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Tasks</th>
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr class="table-row-hover border-b border-gray-100">
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white font-medium">
                                        <?= strtoupper(substr($employee['name'], 0, 2)) ?>
                                    </div>
                                    <span class="font-medium text-gray-900"><?= htmlspecialchars($employee['name']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600"><?= htmlspecialchars($employee['email']) ?></td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    <?= $employee['task_count'] ?> Tasks
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex space-x-3">
                                    <button onclick="showEditEmployeeModal(<?= $employee['id'] ?>, '<?= htmlspecialchars($employee['name']) ?>', '<?= htmlspecialchars($employee['email']) ?>')" 
                                            class="action-icon text-blue-600 hover:text-blue-700" 
                                            title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="showDeleteEmployeeModal(<?= $employee['id'] ?>, '<?= htmlspecialchars($employee['name']) ?>')" 
                                            class="action-icon text-red-600 hover:text-red-700" 
                                            title="Delete Employee">
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

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="modal-content bg-white p-8 rounded-2xl max-w-md w-full">
            <h3 class="text-2xl font-bold mb-6">Add New Employee</h3>
            <form action="employee_actions.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideAddEmployeeModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn-gradient px-6 py-2 text-white rounded-xl">
                        Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add necessary JavaScript -->
    <script>
        function showAddEmployeeModal() {
            document.getElementById('addEmployeeModal').classList.remove('hidden');
        }

        function hideAddEmployeeModal() {
            document.getElementById('addEmployeeModal').classList.add('hidden');
        }

        function showEditEmployeeModal(id, name, email) {
            // Implementation for edit modal
        }

        function showDeleteEmployeeModal(id, name) {
            if (confirm(`Are you sure you want to delete ${name}?`)) {
                window.location.href = `employee_actions.php?action=delete&id=${id}`;
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
