<?php
header('Content-Type: text/html; charset=utf-8');

// 配置项
$musicDir = "music/";       // 音乐存储目录
$coverDir = "covers/";      // 封面存储目录
$allowedTypes = ['audio/mpeg', 'audio/mp3'];  // 允许的MIME类型

// 创建目录（如果不存在）
if(!is_dir($musicDir)) mkdir($musicDir, 0755, true);
if(!is_dir($coverDir)) mkdir($coverDir, 0755, true);

// 数据库连接
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "musicdb";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(header("Location: admin.php?type=error&msg=数据库连接失败: " . urlencode($conn->connect_error)));
}

// 检查是否有文件上传
if(!isset($_FILES['musicFiles'])) {
    header("Location: admin.php?type=error&msg=未检测到上传文件");
    exit;
}

$uploaded = 0;
$errors = [];

// 处理每个上传文件
foreach($_FILES['musicFiles']['tmp_name'] as $index => $tmpPath) {
    $fileError = $_FILES['musicFiles']['error'][$index];
    $fileName = $_FILES['musicFiles']['name'][$index];
    $fileType = $_FILES['musicFiles']['type'][$index];

    // 错误检查
    if($fileError !== UPLOAD_ERR_OK) {
        $errors[] = "文件 {$fileName} 上传失败（错误代码：{$fileError}）";
        continue;
    }

    // 类型验证
    if(!in_array($fileType, $allowedTypes) && pathinfo($fileName, PATHINFO_EXTENSION) !== 'mp3') {
        $errors[] = "文件 {$fileName} 类型不合法（仅支持MP3）";
        continue;
    }

    // 生成唯一文件名
    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $fileName);
    $targetPath = $musicDir . $safeName;

    // 移动文件到目标目录
    if(!move_uploaded_file($tmpPath, $targetPath)) {
        $errors[] = "文件 {$fileName} 保存失败";
        continue;
    }

    // 提取音乐元数据
    require_once("getid3/getid3.php");
    $getID3 = new getID3();
    $fileInfo = $getID3->analyze($targetPath);

    // 解析标签信息
    $title = isset($fileInfo['tags']['id3v2']['title'][0]) 
        ? $fileInfo['tags']['id3v2']['title'][0] 
        : (isset($fileInfo['tags']['id3v1']['title']) ? $fileInfo['tags']['id3v1']['title'] : '未知标题');
    
    $artist = isset($fileInfo['tags']['id3v2']['artist'][0]) 
        ? $fileInfo['tags']['id3v2']['artist'][0] 
        : (isset($fileInfo['tags']['id3v1']['artist']) ? $fileInfo['tags']['id3v1']['artist'] : '未知艺术家');

    // 处理专辑封面
    $cover = 'default_cover.png';
    if(isset($fileInfo['id3v2']['APIC'][0]['data'])) {
        $coverData = $fileInfo['id3v2']['APIC'][0]['data'];
        $coverExt = $fileInfo['id3v2']['APIC'][0]['image_mime'] === 'image/png' ? 'png' : 'jpg';
        $coverName = uniqid('cover_') . '.' . $coverExt;
        $coverPath = $coverDir . $coverName;
        if(file_put_contents($coverPath, $coverData)) {
            $cover = $coverPath;
        }
    }

    // 检查数据库是否已存在
    $checkStmt = $conn->prepare("SELECT id FROM music WHERE path = ?");
    $checkStmt->bind_param("s", $targetPath);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if($result->num_rows === 0) {
        // 插入新记录
        $insertStmt = $conn->prepare("INSERT INTO music (path, title, artist, cover) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $targetPath, $title, $artist, $cover);
        if(!$insertStmt->execute()) {
            $errors[] = "文件 {$fileName} 数据库插入失败: " . $insertStmt->error;
            unlink($targetPath);  // 插入失败则删除文件
            continue;
        }
        $uploaded++;
        $insertStmt->close();
    } else {
        $errors[] = "文件 {$fileName} 已存在于数据库";
        unlink($targetPath);  // 已存在则删除临时文件
        continue;
    }
    $checkStmt->close();
}

$conn->close();

// 构造提示信息
$msg = "上传完成！成功处理 {$uploaded} 个文件";
if(!empty($errors)) {
    $msg .= "，以下文件处理失败：<br>" . implode("<br>", $errors);
}

header("Location: admin.php?type=" . ($uploaded > 0 ? 'success' : 'error') . "&msg=" . urlencode($msg));
exit;