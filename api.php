<?php
// 设置响应头为JSON格式
header('Content-Type: application/json');

// 引入 getID3 库
require_once("getid3/getid3.php");
$getID3 = new getID3();

// 音乐文件目录
$musicDir = "music/";
// 获取所有MP3文件
$mp3Files = glob($musicDir . "*.mp3");
// 存储歌曲信息的数组
$songs = [];

// 数据库连接信息
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "musicdb";

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => '数据库连接失败: ' . $conn->connect_error]);
    exit;
}

// 处理播放次数统计逻辑（新增防刷功能）
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'play') {
    if (!isset($_REQUEST['song_id']) || !is_numeric($_REQUEST['song_id'])) {
        http_response_code(400);
        echo json_encode(['error' => '无效的歌曲ID']);
        $conn->close();
        exit;
    }

    $songId = (int)$_REQUEST['song_id'];
    $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';  // 获取用户IP
    $coolDownMinutes = 5;  // 设置冷却时间（分钟）

    // 检查5分钟内是否有相同IP的播放记录
    $checkLogStmt = $conn->prepare("SELECT id FROM play_log 
                                   WHERE song_id = ? 
                                   AND user_ip = ? 
                                   AND access_time >= NOW() - INTERVAL ? MINUTE");
    $checkLogStmt->bind_param("isi", $songId, $userIp, $coolDownMinutes);
    $checkLogStmt->execute();
    $logResult = $checkLogStmt->get_result();

    if ($logResult->num_rows > 0) {
        http_response_code(429);  // 太多请求状态码
        echo json_encode(['error' => '冷却时间内，请勿重复刷播放量']);
        $checkLogStmt->close();
        $conn->close();
        exit;
    }

    // 执行播放次数更新
    $updateStmt = $conn->prepare("UPDATE music SET play_count = play_count + 1 WHERE id = ?");
    $updateStmt->bind_param("i", $songId);
    
    if ($updateStmt->execute()) {
        if ($conn->affected_rows > 0) {
            // 记录播放日志
            $insertLogStmt = $conn->prepare("INSERT INTO play_log (song_id, user_ip, access_time) VALUES (?, ?, NOW())");
            $insertLogStmt->bind_param("is", $songId, $userIp);
            $insertLogStmt->execute();
            $insertLogStmt->close();
            
            echo json_encode(['success' => true, 'message' => '播放次数更新成功']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => '未找到指定歌曲']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => '播放次数更新失败: ' . $conn->error]);
    }
    
    $updateStmt->close();
    $checkLogStmt->close();
    $conn->close();
    exit;
}

// 以下为原有代码（未修改部分保持不变）
// 处理歌曲搜索逻辑（新增）
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'search') {
    if (!isset($_REQUEST['keyword']) || empty(trim($_REQUEST['keyword']))) {
        http_response_code(400);
        echo json_encode(['error' => '请提供有效的搜索关键词']);
        $conn->close();
        exit;
    }
    
    $keyword = '%' . trim($_REQUEST['keyword']) . '%';
    $searchStmt = $conn->prepare("SELECT id, path, title, artist, cover, play_count 
                                 FROM music 
                                 WHERE title LIKE ? OR artist LIKE ? 
                                 ORDER BY play_count DESC");
    $searchStmt->bind_param("ss", $keyword, $keyword);
    
    if (!$searchStmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => '搜索执行失败: ' . $conn->error]);
        $searchStmt->close();
        $conn->close();
        exit;
    }
    
    $result = $searchStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    
    $searchStmt->close();
    $conn->close();
    echo json_encode($songs);
    exit;
}

// 原有文件同步逻辑（保留优化）
foreach ($mp3Files as $mp3File) {
    $checkStmt = $conn->prepare("SELECT id FROM music WHERE path = ?");
    $checkStmt->bind_param("s", $mp3File);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        $fileInfo = $getID3->analyze($mp3File);
        $title = isset($fileInfo['tags']['id3v2']['title'][0])
            ? $fileInfo['tags']['id3v2']['title'][0]
            : (isset($fileInfo['tags']['id3v1']['title']) ? $fileInfo['tags']['id3v1']['title'] : '未知');
        $artist = isset($fileInfo['tags']['id3v2']['artist'][0])
            ? $fileInfo['tags']['id3v2']['artist'][0]
            : (isset($fileInfo['tags']['id3v1']['artist']) ? $fileInfo['tags']['id3v1']['artist'] : '未知');
        $cover = '';
        $coverPath = '';

        if (isset($fileInfo['id3v2']['APIC'][0]['data'])) {
            $imageData = $fileInfo['id3v2']['APIC'][0]['data'];
            $coverFileName = uniqid() . '.png';
            $coverPath = 'covers/' . $coverFileName;
            file_put_contents($coverPath, $imageData);
            $cover = $coverPath;
        } else {
            $cover = "default_cover.png";
        }

        $insertStmt = $conn->prepare("INSERT INTO music (path, title, artist, cover, play_count) VALUES (?, ?, ?, ?, 0)");
        $insertStmt->bind_param("ssss", $mp3File, $title, $artist, $cover);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $checkStmt->close();
}

// 查询数据库中所有音乐信息（原有）
$selectStmt = $conn->prepare("SELECT id, path, title, artist, cover, play_count FROM music ORDER BY id DESC");
$selectStmt->execute();
$result = $selectStmt->get_result();
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}
$selectStmt->close();

$conn->close();

// 输出包含播放次数的JSON数据
echo json_encode($songs);
?>