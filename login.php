<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error_msg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary': '#2563eb',
                    'primary-light': '#e0f2fe',
                    'primary-soft': '#f0f9ff',
                    'error': '#ef4444',
                    'error-light': '#fee2e2'
                },
                boxShadow: {
                    'xl': '0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 8px 10px -6px rgba(37, 99, 235, 0.1)'
                }
            }
        }
    }</script>
    <style>
        .login-card {
            animation: fadeInUp 0.5s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <title>Aniy音乐-后台登录</title>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-soft via-white to-primary-soft flex items-center justify-center">
    <div class="login-card w-full max-w-md p-8 bg-white/95 backdrop-blur-lg rounded-3xl shadow-xl ring-1 ring-primary/5">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 rounded-full mx-auto flex items-center justify-center mb-4">
                <i class="fa fa-headphones-simple text-primary text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Aniy音乐后台</h1>
            <p class="text-sm text-gray-500 mt-2">请使用授权账号登录管理后台</p>
        </div>
        
        <?php if ($error_msg): ?>
        <div class="bg-error-light text-error px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
            <i class="fa fa-exclamation-circle mr-3 text-lg"></i>
            <span><?= $error_msg ?></span>
        </div>
        <?php endif; ?>

        <form action="login_process.php" method="post" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-user mr-2 text-gray-400"></i> 管理员用户名
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    class="appearance-none block w-full px-4 py-3 rounded-2xl border border-primary/20 placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    placeholder="请输入管理员账号"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-lock mr-2 text-gray-400"></i> 登录密码
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="appearance-none block w-full px-4 py-3 rounded-2xl border border-primary/20 placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    placeholder="请输入管理员密码"
                >
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-2xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary transition-transform hover:scale-105 active:scale-95">
                    <span class="flex items-center">
                        <i class="fa fa-sign-in-alt mr-2"></i> 立即登录
                    </span>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
    