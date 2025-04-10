<?php
session_start();
include('db.php');

$action = $_GET['action'] ?? 'login';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($action == 'login') {
        $email = $_POST["email"];
        $password = $_POST["password"];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            header("Location: " . ($user['role'] == 'admin' ? "admin.php" : "employee.php"));
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } elseif ($action == 'register') {
        $email = $_POST["email"];
        $name = $_POST["name"];
        $username = $_POST["username"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $admin_code = $_POST["admin_code"] ?? '';
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $role = ($admin_code === '8614') ? 'admin' : 'employee';
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role, name, username) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$email, $password, $role, $name, $username])) {
                $_SESSION['success'] = 'Account created successfully!';
                header("Location: auth.php?action=login");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
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
    <title><?= $action == 'login' ? 'Login' : 'Register' ?> | TaskFlow Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 50% 0%, #818cf8 0, transparent 50%),
                radial-gradient(at 100% 0%, #c084fc 0, transparent 50%);
            background-size: cover;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }
        .input-field {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        .input-field:focus {
            background: white;
            box-shadow: 
                0 0 0 3px rgba(99, 102, 241, 0.1),
                0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -10px rgba(139, 92, 246, 0.5);
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center p-6 relative overflow-hidden">
    <div class="blob bg-indigo-500/30 w-[500px] h-[500px] top-[-200px] left-[-200px]"></div>
    <div class="blob bg-purple-500/30 w-[500px] h-[500px] bottom-[-200px] right-[-200px]"></div>

    <div class="w-full max-w-[440px] relative z-10">
        <div class="mb-8 text-center">
            <a href="index.php" class="inline-flex items-center text-white/90 hover:text-white transition-colors font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Homepage
            </a>
        </div>
        
        <div class="glass-card p-10">
            <div class="text-center mb-10">
                <div class="inline-block p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white mb-6 shadow-lg">
                    <i class="fas <?= $action == 'login' ? 'fa-lock' : 'fa-user-plus' ?> text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    <?= $action == 'login' ? 'Welcome Back!' : 'Join TaskFlow Pro' ?>
                </h2>
                <p class="text-gray-600 font-normal">
                    <?= $action == 'login' ? 'Sign in to continue to your workspace' : 'Create your account today' ?>
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 p-4 mb-6 rounded-xl" role="alert">
                    <p class="font-medium flex items-center text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= $error ?>
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-indigo-500"></i>
                        </div>
                        <input type="email" name="email" required
                               class="input-field w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-indigo-500"></i>
                        </div>
                        <input type="password" name="password" required
                               class="input-field w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                               placeholder="Enter your password">
                    </div>
                </div>

                <?php if ($action == 'register'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-indigo-500"></i>
                            </div>
                            <input type="text" name="name" required
                                   class="input-field w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                                   placeholder="Enter your full name">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user-tag text-indigo-500"></i>
                            </div>
                            <input type="text" name="username" required
                                   class="input-field w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                                   placeholder="Choose a unique username">
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 mt-4">
                        <input type="checkbox" id="isAdmin" class="hidden">
                        <button type="button" onclick="toggleAdminCode()" 
                                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            Register as Administrator
                        </button>
                    </div>

                    <div id="adminCodeSection" class="hidden mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Admin Code</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-indigo-500"></i>
                            </div>
                            <input type="text" name="admin_code" id="adminCode"
                                   class="input-field w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500"
                                   placeholder="Enter admin code">
                        </div>
                    </div>
                <?php endif; ?>

                <script>
                    function toggleAdminCode() {
                        const adminCodeSection = document.getElementById('adminCodeSection');
                        const isAdmin = document.getElementById('isAdmin');
                        isAdmin.checked = !isAdmin.checked;
                        adminCodeSection.classList.toggle('hidden');
                    }
                </script>

                <button type="submit"
                        class="btn-gradient w-full py-4 px-6 text-white rounded-xl font-medium text-base mt-8">
                    <?= $action == 'login' ? 'Sign In' : 'Create Account' ?>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    <?php if ($action == 'login'): ?>
                        New to TaskFlow Pro? 
                        <a href="?action=register" class="text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                            Create an account
                        </a>
                    <?php else: ?>
                        Already have an account? 
                        <a href="?action=login" class="text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                            Sign in
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i>' + 
                             (<?= json_encode($action == 'login') ?> ? 'Signing in...' : 'Creating account...');
            button.disabled = true;
            button.classList.add('opacity-75');
        });
    </script>
</body>
</html>
