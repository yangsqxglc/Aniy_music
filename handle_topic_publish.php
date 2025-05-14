<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

// 会话验证：确保管理员已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 权限校验：仅限超级管理员发布话题
if ($_SESSION['admin_role'] !== '超级管理员') {
    header("Location: admin.php?topic_type=error&topic_msg=权限不足：您没有发布话题的权限");
    exit;
}

// 数据库配置（与你的管理后台保持一致）
$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";

// 连接数据库
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 初始化变量
$coverImagePath = null;
$errorMessage = '';

// 处理文件上传（封面图片）
if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    // 配置上传目录（与当前文件同级的covers文件夹）
    $uploadDir = __DIR__ . '/covers/';
    
    // 自动创建目录（如果不存在）
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 生成唯一文件名（避免覆盖）
    $filename = uniqid() . '_' . basename($_FILES['cover_image']['name']);
    $targetPath = $uploadDir . $filename;
    
    // 移动临时文件到目标目录
    if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
        $errorMessage = '封面图片上传失败（文件移动失败）';
    } else {
        $coverImagePath = '/covers/' . $filename;  // 存储到数据库的相对路径
    }
} elseif ($_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    // 非"未选择文件"的错误（如文件过大、格式错误）
    $errorMessage = '封面图片上传失败（错误代码：' . $_FILES['cover_image']['error'] . '）';
}

// 校验必填字段
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$views = (int)trim($_POST['views'] ?? 0);
$discussions = (int)trim($_POST['discussions'] ?? 0);
$isHot = isset($_POST['is_hot']) ? 1 : 0;
$source = trim($_POST['source'] ?? '');
$creator = trim($_POST['creator'] ?? '');

// 验证基础字段
if (empty($title)) {
    $errorMessage = '话题标题不能为空';
} elseif (empty($description)) {
    $errorMessage = '话题描述不能为空';
} elseif ($views < 0 || $discussions < 0) {
    $errorMessage = '阅读量/讨论量不能为负数';
}

// 处理错误（如有）
if (!empty($errorMessage)) {
    header("Location: admin.php?topic_type=error&topic_msg=" . urlencode($errorMessage));
    $conn->close();
    exit;
}

// 预处理SQL插入（包含阅读量和讨论量）
$stmt = $conn->prepare("INSERT INTO topics 
    (title, cover_image, source, creator, description, is_hot, views, discussions) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

// 绑定参数（注意：最后两个参数类型为整数i）
$stmt->bind_param("ssssssii", 
    $title, 
    $coverImagePath, 
    $source, 
    $creator, 
    $description, 
    $isHot, 
    $views, 
    $discussions
);

// 执行插入
if (!$stmt->execute()) {
    $errorMessage = '数据库插入失败：' . $conn->error;
    header("Location: admin.php?topic_type=error&topic_msg=" . urlencode($errorMessage));
    $stmt->close();
    $conn->close();
    exit;
}

// 清理资源并跳转
$stmt->close();
$conn->close();
header("Location: admin.php?topic_type=success&topic_msg=话题发布成功");
exit;
?>