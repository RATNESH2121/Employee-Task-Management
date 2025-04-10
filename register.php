<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? "admin.php" : "employee.php"));
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $name = $_POST["name"];
    $admin_code = $_POST["admin_code"] ?? '';

    // Validate username format
    if (!isValidUsername($username)) {
        $error = "Username must be 5-20 characters long and contain both letters and numbers.";
    } else if ($stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?")) {
        // Determine role based on admin code
        $role = ($admin_code === '8914') ? 'admin' : 'employee';
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn() > 0;
        
        if ($emailExists) {
            $error = "❌ Email already registered!";
        } else {
            try {
                // Register the new user
                if (registerUser($username, $email, $password, $name, $role)) {
                    $success = "✅ Registration successful! You can now login.";
                } else {
                    $error = "❌ Registration failed. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "❌ Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register | Task Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #1e1b4b;
            background-image: 
                url('https://raw.githubusercontent.com/tailwindlabs/tailwindcss.com/master/public/img/beams.jpg'),
                linear-gradient(135deg, #312e81, #4338ca);
            background-blend-mode: overlay;
            background-size: cover;
            position: relative;
        }
        .glass {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.35),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }
        .pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="pattern">
    <!-- Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-10 -right-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float"></div>
        <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: -2s"></div>
    </div>

    <!-- Header -->
    <header class="bg-white/15 backdrop-blur-md relative z-10 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-24">
                <div class="flex items-center space-x-3">
                    <div class="text-4xl animate-float">📋</div>
                    <div>
                        <span class="text-3xl font-bold text-white block">Task Manager</span>
                        <span class="text-white/70 text-sm">Manage your tasks efficiently</span>
                    </div>
                </div>
                <nav>
                    <a href="index.php" 
                       class="group relative inline-flex items-center px-8 py-3 text-white overflow-hidden rounded-lg bg-white/10 hover:bg-white/20 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2 relative z-10"></i>
                        <span class="relative z-10">Sign In</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-8 relative z-10">
        <div class="w-full max-w-md transform hover:scale-[1.01] transition-transform duration-300">
            <div class="glass p-8 shadow-xl">
                <div class="text-center mb-8">
                    <div class="inline-block p-3 rounded-full bg-indigo-100 mb-4">
                        <i class="fas fa-user-plus text-3xl text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
                    <p class="text-gray-600 mt-2">Join us to start managing tasks</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="font-medium"><?= $error ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="name" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter your full name">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter your email">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Create a password">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Admin Code (Optional)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-gray-400"></i>
                            </div>
                            <input type="password" name="admin_code"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter admin code if applicable">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Leave empty for employee account</p>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    Already have an account? 
                    <a href="index.php" class="text-indigo-600 hover:text-indigo-700 font-medium">
                        Sign in instead
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white/15 backdrop-blur-md text-white relative z-10 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="text-center md:text-left">
                        <div class="text-2xl font-bold mb-2">📋 Task Manager</div>
                        <p class="text-white/70 text-sm">Streamline your workflow</p>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-white/70">
                            &copy; <?= date('Y') ?> Task Manager.<br>
                            All rights reserved.
                        </div>
                    </div>
                    <div class="flex justify-center md:justify-end space-x-6">
                        <a href="#" class="group p-2 rounded-full hover:bg-white/10 transition-colors duration-300">
                            <i class="fab fa-github text-2xl text-white/70 group-hover:text-white"></i>
                        </a>
                        <a href="#" class="group p-2 rounded-full hover:bg-white/10 transition-colors duration-300">
                            <i class="fab fa-linkedin text-2xl text-white/70 group-hover:text-white"></i>
                        </a>
                        <a href="#" class="group p-2 rounded-full hover:bg-white/10 transition-colors duration-300">
                            <i class="fab fa-twitter text-2xl text-white/70 group-hover:text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>