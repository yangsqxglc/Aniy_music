<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

// 检查登录状态并获取用户名（直接从Session获取，避免依赖表单提交）
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php?error=请先登录");
    exit;
}
$current_username = $_SESSION['admin_username']; // 直接使用Session中的用户名

// 数据库配置（与原代码保持一致）
$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";

// 获取并过滤输入（仅验证密码相关字段）
$new_password = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

// 验证输入（移除用户名相关验证，因为用户名来自Session）
$errors = [];
if (empty($new_password)) $errors[] = "新密码不能为空";
if ($new_password !== $confirm_password) $errors[] = "两次输入的密码不一致";
if (strlen($new_password) < 6) $errors[] = "密码长度至少6位";

if (!empty($errors)) {
    header("Location: repassword.php?error=" . urlencode(implode('；', $errors)));
    exit;
}

// 连接数据库
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 更新密码（直接使用Session中的用户名，无需检查是否存在，登录时已验证）
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_sql = "UPDATE admin SET password = ? WHERE username = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ss", $hashed_password, $current_username); // 使用Session用户名

if ($update_stmt->execute()) {
    // 密码修改成功后销毁Session
    session_unset();    // 释放所有Session变量
    session_destroy();  // 销毁Session
    // 跳转到登录页并携带成功提示
    header("Location: login.php?success=密码修改成功，请重新登录");
} else {
    header("Location: repassword.php?error=密码修改失败：" . urlencode($update_stmt->error));
}

$update_stmt->close();
$conn->close();
exit;
?>