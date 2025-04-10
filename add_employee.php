<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'employee')");
        $stmt->execute([$name, $email, $password]);
        header("Location: admin.php?success=Employee added successfully");
    } catch (PDOException $e) {
        header("Location: admin.php?error=Failed to add employee");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Employee | Task Manager</title>
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
                <a href="admin.php" class="flex items-center px-4 py-3 bg-white/10 rounded-lg group transition-all">
                    <i class="fas fa-users mr-3"></i> Manage Employees
                </a>
                <a href="task_manage.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg group transition-all">
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
                        <h1 class="text-2xl font-bold">Add New Employee</h1>
                        <a href="admin.php" class="text-white/70 hover:text-white transition">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Employees
                        </a>
                    </div>
                </div>
            </header>

            <!-- Add Employee Form -->
            <main class="p-6">
                <div class="glass p-8 max-w-2xl mx-auto">
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="name" required
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter employee's full name">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-tag text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       name="username" 
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,20}$" 
                                       title="Username must be 5-20 characters long and contain both letters and numbers"
                                       required
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Choose a username (letters and numbers)">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" required
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter email address">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" name="password" required
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Create a password">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 pt-4">
                            <a href="admin.php" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-user-plus mr-2"></i>Add Employee
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>