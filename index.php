<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? "admin.php" : "employee.php"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Task Manager - One App to Rule Them All</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            position: relative;
            overflow-x: hidden;
        }
        .gradient-text {
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Poppins', sans-serif;
        }
        .hero-pattern {
            background: 
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.1) 0, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(139, 92, 246, 0.1) 0, transparent 50%),
                radial-gradient(#e0e7ff 1px, transparent 1px);
            background-size: 50% 50%, 50% 50%, 24px 24px;
            background-position: 0 0, 100% 0, 0 0;
            position: relative;
        }
        .hero-pattern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
            pointer-events: none;
        }
        .feature-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 70%);
            pointer-events: none;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="fixed w-full bg-white/80 backdrop-blur-md z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-2">
                    <span class="text-3xl">📋</span>
                    <span class="text-2xl font-bold gradient-text">TaskFlow Pro</span>
                </div>
                <nav class="flex items-center space-x-4">
                    <a href="auth.php?action=login" class="text-gray-600 hover:text-gray-900">Login</a>
                    <a href="auth.php?action=register" 
                       class="bg-indigo-600 text-white px-6 py-2 rounded-full hover:bg-indigo-700 transition">
                        Sign Up
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 hero-pattern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Manage Tasks <span class="gradient-text">Effortlessly</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Streamline your team's workflow with our powerful task management system.
                    Assign, track, and complete tasks with ease.
                </p>
                <a href="auth.php?action=login" 
                   class="inline-flex items-center px-8 py-4 text-lg font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full hover:opacity-90 transition-all transform hover:scale-105">
                    Start Managing Tasks
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
                <p class="text-sm text-gray-500 mt-4">Simple, Efficient, and Team-Friendly</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Essential Task Management Features</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-gradient-to-br from-purple-500 to-indigo-600 p-6 rounded-2xl text-white">
                    <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-tasks text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Task Assignment</h3>
                    <p class="text-white/80">Easily assign tasks to team members and track progress in real-time.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-gradient-to-br from-blue-500 to-cyan-500 p-6 rounded-2xl text-white">
                    <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Progress Tracking</h3>
                    <p class="text-white/80">Monitor task completion and team performance with detailed insights.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-gradient-to-br from-pink-500 to-rose-500 p-6 rounded-2xl text-white">
                    <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Team Management</h3>
                    <p class="text-white/80">Manage your team members and their responsibilities efficiently.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-3xl">📋</span>
                        <span class="text-2xl font-bold gradient-text">TaskFlow Pro</span>
                    </div>
                    <p class="text-gray-500 max-w-md">
                        The ultimate solution for managing tasks, teams, and projects. Stay organized and boost productivity with our comprehensive task management system.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-semibold mb-4">Features</h3>
                        <ul class="space-y-2 text-gray-500">
                            <li><a href="#" class="hover:text-gray-900">Task Assignment</a></li>
                            <li><a href="#" class="hover:text-gray-900">Team Management</a></li>
                            <li><a href="#" class="hover:text-gray-900">Progress Tracking</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2 text-gray-500">
                            <li><a href="auth.php?action=login" class="hover:text-gray-900">Login</a></li>
                            <li><a href="auth.php?action=register" class="hover:text-gray-900">Register</a></li>
                            <li><a href="#" class="hover:text-gray-900">Help Center</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-100 flex items-center justify-between">
                <p class="text-gray-500">&copy; <?= date('Y') ?> Task Manager. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-github"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-500"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
