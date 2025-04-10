<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$name = $_SESSION['name'];
$role = ucfirst($_SESSION['role']);

include('db.php');
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM tasks" . ($_SESSION['role'] === 'employee' ? " WHERE assigned_to = ?" : ""));

if ($_SESSION['role'] === 'employee') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= $role ?> Dashboard | Task Manager</title>
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
                    <h2 class="text-2xl font-bold"><?= $role ?> Panel</h2>
                    <p class="text-sm text-white/70">Task Management System</p>
                </div>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="flex items-center px-4 py-3 bg-white/10 rounded-lg group transition-all">
                    <i class="fas fa-home mr-3"></i> Dashboard
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
                        <i class="fas fa-users mr-3"></i> Manage Employees
                    </a>
                    <a href="task_manage.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
                        <i class="fas fa-tasks mr-3"></i> Manage Tasks
                    </a>
                <?php else: ?>
                    <a href="employee.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
                        <i class="fas fa-tasks mr-3"></i> My Tasks
                    </a>
                <?php endif; ?>
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
                        <h1 class="text-2xl font-bold">Welcome Back, <?= htmlspecialchars($name) ?>!</h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-white/70"><?= date('l, F j, Y') ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Tasks -->
                    <div class="glass p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Total Tasks</h3>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
                        <p class="text-sm text-gray-600 mt-2">All assigned tasks</p>
                    </div>

                    <!-- Completed Tasks -->
                    <div class="glass p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Completed</h3>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900"><?= $stats['completed'] ?? 0 ?></p>
                        <p class="text-sm text-gray-600 mt-2">Tasks completed</p>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="glass p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900"><?= $stats['pending'] ?? 0 ?></p>
                        <p class="text-sm text-gray-600 mt-2">Tasks in progress</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="add_employee.php" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                <i class="fas fa-user-plus text-indigo-600 mr-3"></i>
                                <span class="text-gray-800">Add Employee</span>
                            </a>
                            <a href="task_manage.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                <i class="fas fa-plus-circle text-green-600 mr-3"></i>
                                <span class="text-gray-800">Create Task</span>
                            </a>
                        <?php else: ?>
                            <a href="employee.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-tasks text-blue-600 mr-3"></i>
                                <span class="text-gray-800">View My Tasks</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
