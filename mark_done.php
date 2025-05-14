<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

// 验证管理员登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php?msg=请先登录&type=error");
    exit;
}

// 验证是否为超级管理员（关键权限控制）
if ($_SESSION['admin_role'] !== '超级管理员') {
    header("Location: admin.php?msg=仅超级管理员可操作&type=error");
    exit;
}

// 验证留言ID有效性（防止非法请求）
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php?msg=无效的留言ID&type=error");
    exit;
}

$messageId = (int)$_GET['id'];  // 强制转换为整数防SQL注入
$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";

// 连接数据库并更新状态
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$updateStmt = $conn->prepare("UPDATE music_info SET status = 1 WHERE id = ?");
$updateStmt->bind_param("i", $messageId);  // 预处理语句防SQL注入
$updateResult = $updateStmt->execute();

$conn->close();

// 根据操作结果跳转提示
if ($updateResult && $updateStmt->affected_rows > 0) {
    header("Location: admin.php?msg=留言标记完成成功！&type=success");
} else {
    header("Location: admin.php?msg=标记失败，留言不存在或已处理&type=error");
}
exit;