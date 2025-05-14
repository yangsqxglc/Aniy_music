<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 获取表单数据并过滤
$username = htmlspecialchars(trim($_POST['username'] ?? ''));
$password = trim($_POST['password'] ?? '');

// 假设管理员表名为 `admin`，包含字段：username, password（哈希值）, role
$sql = "SELECT username, password, role FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    // 登录成功，设置Session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $user['username']; // 用户名
    $_SESSION['admin_role'] = $user['role']; // 用户角色（如：超级管理员/普通管理员）
    header("Location: admin.php");
    exit;
} else {
    // 登录失败，跳转回登录页并传递错误信息
    header("Location: login.php?error=用户名或密码错误");
    exit;
}

$stmt->close();
$conn->close();
?>