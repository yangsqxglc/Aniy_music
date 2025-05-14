<?php
// 数据库配置（与之前一致）
$db_host = 'localhost';
$db_name = 'musicdb';
$db_user = 'root';
$db_pass = 'root';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        // 列表接口保持不变（已包含id字段）
        $stmt = $pdo->prepare("
            SELECT id, title, cover_image, source, creator, views, discussions, description, create_time 
            FROM topics 
            WHERE is_hot = 1 
            ORDER BY create_time DESC
        ");
        $stmt->execute();
        $topics = $stmt->fetchAll();

        if (empty($topics)) {
            throw new Exception("当前无热问话题数据");
        }

        $formattedTopics = array_map(function($topic) {
            return [
                'id' => $topic['id'],
                'cover_image' => $topic['cover_image'],
                'title' => $topic['title'],
                'source' => $topic['source'],
                'creator' => $topic['creator'],
                'views' => number_format($topic['views']),
                'discussions' => number_format($topic['discussions']),
                'description' => $topic['description'],
                'create_time' => $topic['create_time']
            ];
        }, $topics);

        $response = ['code' => 200, 'data' => $formattedTopics];

    } else if ($action === 'detail') {
        $topicId = $_GET['id'] ?? null;
        if (!$topicId) {
            throw new Exception("缺少必要的话题ID参数");
        }

        // 修复：使用现有字段（假设详情内容存储在description字段，或根据实际表结构调整）
        $stmt = $pdo->prepare("
            SELECT id, title, cover_image AS detail_image, source, creator, views, discussions, 
                   description AS detail_content, create_time 
            FROM topics 
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt->execute();
        $topic = $stmt->fetch();

        if (!$topic) {
            throw new Exception("未找到指定话题");
        }

        $response = [
            'code' => 200,
            'data' => [
                'id' => $topic['id'],
                'title' => $topic['title'],
                'source' => $topic['source'],
                'creator' => $topic['creator'],
                'views' => number_format($topic['views']),
                'discussions' => number_format($topic['discussions']),
                'create_time' => $topic['create_time'],
                'detail_image' => $topic['detail_image'],  // 复用cover_image作为详情图
                'detail_content' => $topic['detail_content']  // 使用description作为详情内容（临时方案）
            ]
        ];

    } else {
        throw new Exception("无效的请求类型");
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'error' => '数据库错误：' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'code' => 404,
        'error' => $e->getMessage()
    ]);
}
?>