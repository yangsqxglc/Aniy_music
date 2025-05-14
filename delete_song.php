<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

// 权限检查
if (!isset($_SESSION['admin_logged_in']) || 
    $_SESSION['admin_logged_in'] !== true || 
    $_SESSION['admin_role'] !== '超级管理员'
) {
    header("Location: login.php");
    exit;
}

// 获取并验证歌曲ID（防止SQL注入）
$songId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
if ($songId === 0) {
    header("Location: admin.php?module=song-management&error=无效的歌曲ID");
    exit;
}

// 数据库连接
$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    header("Location: admin.php?module=song-management&error=数据库连接失败: " . urlencode($conn->connect_error));
    exit;
}

// 1. 查询文件路径
$selectStmt = $conn->prepare("SELECT path, cover FROM music WHERE id = ?");
if (!$selectStmt) {
    header("Location: admin.php?module=song-management&error=查询准备失败: " . urlencode($conn->error));
    exit;
}
$selectStmt->bind_param("i", $songId);
$selectStmt->execute();
$result = $selectStmt->get_result();
$songData = $result->fetch_assoc();
$selectStmt->close();

if (!$songData) {
    header("Location: admin.php?module=song-management&error=歌曲记录不存在");
    exit;
}

// 关键调整：Windows路径处理（统一为反斜杠）
$rootDir = realpath($_SERVER['DOCUMENT_ROOT']);  // 网站根目录（如D:\phpstudy_pro\WWW）
$musicDir = realpath($rootDir . '/music');       // 音乐目录（D:\phpstudy_pro\WWW\music）
$coversDir = realpath($rootDir . '/covers');     // 封面目录（D:\phpstudy_pro\WWW\covers）

// 2. 处理音乐文件删除
$fileErrors = [];
if (!empty($songData['path'])) {
    // 拼接绝对路径（自动转换斜杠方向）
    $musicPath = realpath($rootDir . '/' . $songData['path']);  // 结果如：D:\phpstudy_pro\WWW\music\xxx.mp3
    
    // 验证逻辑调整：
    // 1. 确保路径存在 2. 路径以musicDir开头（兼容Windows反斜杠）
    if ($musicPath && strpos($musicPath, $musicDir) === 0) {
        if (file_exists($musicPath)) {
            if (!unlink($musicPath)) {
                $fileErrors[] = "音乐文件删除失败（权限问题？）: {$musicPath}";
            }
        } else {
            $fileErrors[] = "音乐文件不存在: {$musicPath}";
        }
    } else {
        $fileErrors[] = "音乐路径越界（期望目录: {$musicDir}, 实际路径: {$musicPath}）";
    }
}

// 3. 处理封面文件删除
if (!empty($songData['cover'])) {
    $coverPath = realpath($rootDir . '/' . $songData['cover']);  // 结果如：D:\phpstudy_pro\WWW\covers\xxx.png
    
    if ($coverPath && strpos($coverPath, $coversDir) === 0) {
        if (file_exists($coverPath)) {
            if (!unlink($coverPath)) {
                $fileErrors[] = "封面文件删除失败（权限问题？）: {$coverPath}";
            }
        } else {
            $fileErrors[] = "封面文件不存在: {$coverPath}";
        }
    } else {
        $fileErrors[] = "封面路径越界（期望目录: {$coversDir}, 实际路径: {$coverPath}）";
    }
}

// 4. 处理文件删除错误
if (!empty($fileErrors)) {
    $errorMsg = "文件操作异常: " . implode('; ', $fileErrors);
    header("Location: admin.php?module=song-management&error=" . urlencode($errorMsg));
    $conn->close();
    exit;
}

// 5. 删除数据库记录
$deleteStmt = $conn->prepare("DELETE FROM music WHERE id = ?");
if (!$deleteStmt) {
    header("Location: admin.php?module=song-management&error=删除准备失败: " . urlencode($conn->error));
    $conn->close();
    exit;
}
$deleteStmt->bind_param("i", $songId);
$deleteSuccess = $deleteStmt->execute();
$affectedRows = $deleteStmt->affected_rows;
$deleteStmt->close();
$conn->close();

// 6. 最终结果反馈
if ($deleteSuccess && $affectedRows > 0) {
    header("Location: admin.php?module=song-management&success=歌曲及关联文件已成功删除");
} else {
    $errorMsg = $deleteSuccess ? 
        "错误：歌曲ID（{$songId}）记录不存在" : 
        "数据库删除失败：" . $deleteStmt->error;
    header("Location: admin.php?module=song-management&error=" . urlencode($errorMsg));
}

exit;
?>
