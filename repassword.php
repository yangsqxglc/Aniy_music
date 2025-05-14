<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

// 检查用户是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 处理密码修改请求
$error = $success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证输入完整性
    if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        $error = "请完整填写新密码和确认密码";
    } 
    // 验证密码长度
    elseif (strlen($_POST['new_password']) < 6 || strlen($_POST['new_password']) > 20) {
        $error = "密码长度需为6-20位";
    } 
    // 验证两次密码是否一致
    elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = "两次输入的密码不一致";
    } else {
        // 这里替换为你的数据库连接逻辑（示例）
        $pdo = new PDO('mysql:host=localhost;dbname=your_db', 'username', 'password');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            // 哈希密码（推荐使用 password_hash）
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            
            // 更新数据库中的密码（假设管理员ID存储在 session 中）
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['admin_id']]);
            
            $success = "密码修改成功";
        } catch (PDOException $e) {
            // 实际生产环境应记录日志而非直接显示错误
            $error = "密码修改失败：" . $e->getMessage();
        }
    }

    // 重定向防止重复提交
    $redirectUrl = 'repasswordor.php';
    if ($error) $redirectUrl .= '?error=' . urlencode($error);
    if ($success) $redirectUrl .= '?success=' . urlencode($success);
    header("Location: $redirectUrl");
    exit;
}
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
        .password-card {
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
    <title>Aniy音乐-修改密码</title>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-soft via-white to-primary-soft flex items-center justify-center">
    <div class="password-card w-full max-w-md p-8 bg-white/95 backdrop-blur-lg rounded-3xl shadow-xl ring-1 ring-primary/5">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 rounded-full mx-auto flex items-center justify-center mb-4">
                <i class="fa fa-key text-primary text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">修改登录密码</h1>
            <p class="text-sm text-gray-500 mt-2">请使用新密码完成安全验证</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
        <div class="bg-error-light text-error px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
            <i class="fa fa-exclamation-circle mr-3 text-lg"></i>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
            <i class="fa fa-check-circle mr-3 text-lg"></i>
            <span><?= htmlspecialchars($_GET['success']) ?></span>
        </div>
        <?php endif; ?>

        <form action="repasswordor.php" method="post" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-user mr-2 text-gray-400"></i> 管理员用户名
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>"
                    disabled 
                    class="appearance-none block w-full px-4 py-3 rounded-2xl border border-primary/20 placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all bg-gray-50"
                />
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-lock mr-2 text-gray-400"></i> 新登录密码
                </label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    minlength="6"
                    class="appearance-none block w-full px-4 py-3 rounded-2xl border border-primary/20 placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    placeholder="请输入6-20位新密码"
                >
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-lock mr-2 text-gray-400"></i> 确认新密码
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    minlength="6"
                    class="appearance-none block w-full px-4 py-3 rounded-2xl border border-primary/20 placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    placeholder="请再次输入新密码"
                >
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-2xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary transition-transform hover:scale-105 active:scale-95">
                    <span class="flex items-center">
                    保存新密码
                    </span>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
    