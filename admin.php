<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax'
]);

if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
    $_SESSION['theme'] = $_GET['theme'];
}
$currentTheme = $_SESSION['theme'] ?? 'light';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['admin_username'] ?? '未知用户';
$role = $_SESSION['admin_role'] ?? '普通管理员';

$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "musicdb";
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 处理搜索参数
$searchQuery = $_GET['q'] ?? '';
$searchQuery = $conn->real_escape_string(trim($searchQuery));

$totalSongs = $conn->query("SELECT COUNT(*) AS total FROM music")->fetch_assoc()['total'] ?? 0;
$topSongs = [];
$rankResult = $conn->query("SELECT id, title, artist, play_count FROM music ORDER BY play_count DESC LIMIT 10");
if ($rankResult->num_rows > 0) {
    while ($row = $rankResult->fetch_assoc()) {
        $topSongs[] = $row;
    }
}
$rankResult->free_result();

$chartLabels = [];
$chartData = [];
if (!empty($topSongs)) {
    $chartLabels = array_map(function($song) { 
        return mb_strimwidth($song['title'], 0, 10, '...'); 
    }, $topSongs);
    $chartData = array_map(function($song) { 
        return $song['play_count']; 
    }, $topSongs);
}

$messageResult = $conn->query("SELECT id, music_name, author, platform, create_time, status FROM music_info ORDER BY create_time DESC");

$songs = [];
if ($role === '超级管理员') {
    $sql = "SELECT id, title, artist, play_count FROM music";
    if (!empty($searchQuery)) {
        $sql .= " WHERE title LIKE '%{$searchQuery}%' OR artist LIKE '%{$searchQuery}%'";
    }
    $songsResult = $conn->query($sql);
    if ($songsResult->num_rows > 0) {
        while ($row = $songsResult->fetch_assoc()) {
            $songs[] = $row;
        }
    }
    $songsResult->free_result();
}

$conn->close();

$weekMap = ['一', '二', '三', '四', '五', '六', '日'];
$currentWeek = '星期' . $weekMap[(int)date('N') - 1]; 
$currentHour = (int)date('G'); 

if ($currentHour >= 5 && $currentHour < 12) {
    $timePeriod = '早上';
} elseif ($currentHour >= 12 && $currentHour < 14) {
    $timePeriod = '中午';
} elseif ($currentHour >= 14 && $currentHour < 18) {
    $timePeriod = '下午';
} elseif ($currentHour >= 18 && $currentHour < 20) {
    $timePeriod = '傍晚';
} else {
    $timePeriod = '深夜'; 
}

$welcomeText = "今天是{$currentWeek}，{$timePeriod}好，辛苦了";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>音乐管理后台 | 音乐库</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #22c55e;
            --error-color: #ef4444;
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-300: #cbd5e1;
            --neutral-800: #1e293b;
            --neutral-900: #0f172a;
            
            --bg-color: var(--neutral-50);
            --text-color: var(--neutral-900);
            --card-bg: white;
            --border-color: var(--neutral-200);
            --table-head-bg: var(--neutral-100);
            
            --transition-duration: 0.35s;
            --transition-easing: cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .theme-dark {
            --bg-color: #0f172a;
            --text-color: #f8fafc;
            --card-bg: #1e293b;
            --border-color: #334155;
            --table-head-bg: #2d3748;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            transition: 
                background-color var(--transition-duration) var(--transition-easing),
                color var(--transition-duration) var(--transition-easing),
                border-color var(--transition-duration) var(--transition-easing),
                box-shadow var(--transition-duration) var(--transition-easing);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: var(--card-bg);
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 6 -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu-item {
            margin-bottom: 0.75rem;
        }

        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color var(--transition-duration) var(--transition-easing);
        }

        .sidebar-menu-item a:hover,
        .sidebar-menu-item a.active {
            background-color: var(--neutral-200);
            color: var(--primary-color);
        }

        .main-content {
            margin-left: 240px;
            padding: 2rem;
        }

        .content-module {
            display: none;
            margin-bottom: 2rem;
        }

        .content-module.active {
            display: block;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--neutral-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .theme-dark .user-avatar {
            background-color: #334155;
        }

        .user-avatar:hover {
            background-color: var(--neutral-300);
            transform: scale(1.1);
        }

        .user-avatar i {
            color: var(--neutral-300);
            font-size: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none; 
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-logout {
            background-color: var(--error-color);
            color: white;
            text-decoration: none; 
        }

        .btn-logout:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .data-container {
            padding: 1.5rem;
            margin-top: -30px;
            margin-bottom: 2rem;
        }

        .stats-number {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0.5rem 0;
        }

        .upload-section {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 0.75rem;
        }

        .file-input {
            width: 100%;
            padding: 1.5rem;
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            background-color: var(--bg-color);
            cursor: pointer;
        }

        .file-input:hover {
            border-color: var(--primary-color);
        }

        .message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .message-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .message-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .message-scroll-container {
            max-height: 600px; 
            height: auto;
            width: 100%;
            overflow-y: auto;
            scrollbar-width: none; 
            -ms-overflow-style: none;
        }

        .message-scroll-container::-webkit-scrollbar {
            display: none; 
        }

        .message-table, .rank-table {
            width: 100%;
            border-collapse: collapse;
        }

        .message-table th, 
        .message-table td,
        .rank-table th,
        .rank-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .message-table th {
            position: sticky;
            top: 0;
            background: var(--table-head-bg);
            z-index: 1;
        }

        .rank-table th {
            color: var(--text-color);
            font-weight: 600;
        }

        .rank-td {
            width: 40px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .theme-toggle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: 
                transform var(--transition-duration) var(--transition-easing),
                box-shadow var(--transition-duration) var(--transition-easing);
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle i {
            font-size: 1.5rem;
            color: var(--text-color);
            transition: color var(--transition-duration) var(--transition-easing);
        }

        .chart-container {
            height: 400px; 
            margin-top: 1.5rem;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
        }
        .hh{
            text-decoration: none;
        }

        /* 搜索相关样式 */
        .search-form {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            float: right;
        }

        .search-input {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--bg-color);
            color: var(--text-color);
            width: 200px;
            font-size: 0.875rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            height: fit-content;
        }
        /* 话题发布模块专属样式 */
#topic-publish .data-container {
    max-width: 800px; /* 限制最大宽度，提升阅读体验 */
    margin: 0 auto; /* 居中显示 */
}

#topic-publish .form-group {
    margin-bottom: 1.5rem; /* 增加表单组间距 */
}

#topic-publish .form-input {
    /* 统一表单控件样式 */
    width: 100%;
    padding: 0.875rem 1.25rem;
    border: 2px solid var(--border-color); /* 加粗边框 */
    border-radius: 0.75rem;
    background-color: var(--card-bg);
    color: var(--text-color);
    font-size: 0.9rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

#topic-publish .form-input:focus {
    outline: none;
    border-color: var(--primary-color); /* 聚焦时边框变主题色 */
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); /* 增加聚焦阴影 */
}

#topic-publish .form-input:disabled {
    background-color: var(--neutral-100); /* 只读状态浅背景 */
    cursor: not-allowed;
}

#topic-publish textarea.form-input {
    /* 文本域样式优化 */
    min-height: 150px;
    resize: vertical; /* 允许垂直调整高度 */
}

#topic-publish .form-check {
    /* 复选框组样式 */
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
    color: var(--neutral-800);
}

#topic-publish .form-check-input {
    /* 自定义复选框样式 */
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid var(--border-color);
    border-radius: 0.375rem;
    background-color: var(--card-bg);
    appearance: none;
    cursor: pointer;
    transition: border-color 0.3s ease, background-color 0.3s ease;
}

#topic-publish .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3E%3C/svg%3E");
    background-size: 60%;
    background-position: center;
}

#topic-publish .form-check-label {
    color: var(--neutral-700);
    font-weight: 500;
}

#topic-publish .btn-primary {
    /* 提交按钮样式增强 */
    width: 100%;
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    margin-top: 2rem; /* 增加顶部间距 */
}

/* 响应式布局：小屏幕下优化表单间距 */
@media (max-width: 768px) {
    #topic-publish .data-container {
        padding: 1rem;
    }
}
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
</head>
<body class="theme-<?= htmlspecialchars($currentTheme) ?>">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="admin.php" class="hh"><div class="sidebar-logo"><i class="fa-solid fa-headphones-simple"></i> Aniy音乐</div></a>
        </div>

        <div class="user-info" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
            <a href="repassword.php">
                <div class="user-avatar" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="fa-solid fa-user"></i>
                </div>
            </a>
            <div>
                <div><?= htmlspecialchars($username) ?></div>
                <div style="font-size: 0.875rem; color: var(--neutral-300);"><?= htmlspecialchars($role) ?></div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="#" class="active" data-module="dashboard" onclick="showModule('dashboard')">
                    <i class="fa-solid fa-house"></i> 数据看板
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" data-module="song-stats" onclick="showModule('song-stats')">
                    <i class="fa-solid fa-chalkboard"></i> 歌曲统计
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" data-module="play-chart" onclick="showModule('play-chart')">
                    <i class="fa-solid fa-chart-bar"></i> 播放图表
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" data-module="rankings" onclick="showModule('rankings')">
                    <i class="fa-solid fa-chart-simple"></i> 播放排行
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="#" data-module="messages" onclick="showModule('messages')">
                    <i class="fa-solid fa-comments"></i> 求歌留言
                </a>
            </li>
            <?php if ($role !== '普通管理员'): ?>
            <li class="sidebar-menu-item">
                <a href="#" data-module="upload-section" onclick="showModule('upload-section')">
                    <i class="fa-solid fa-circle-check"></i> 批量上传
                </a>
            </li>
            <?php endif; ?>
            <?php if ($role === '超级管理员'): ?>
            <li class="sidebar-menu-item">
                <a href="#" data-module="song-management" onclick="showModule('song-management')">
                    <i class="fa-solid fa-music"></i> 歌曲管理
                </a>
            </li>
            <?php endif; ?>
            <?php if ($role === '超级管理员'): ?>
            <li class="sidebar-menu-item">
            <a href="#" data-module="topic-publish" onclick="showModule('topic-publish')">
            <i class="fa-solid fa-newspaper"></i> 发布话题
            </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-menu-item" style="margin-top: auto;">
                <a href="logout.php" class="btn-logout-sidebar">
                    <i class="fa-solid fa-right-from-bracket"></i> 退出登录
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h3><?= htmlspecialchars($welcomeText) ?></h3>
            <a href="#" class="theme-toggle" onclick="toggleTheme()">
                <?= $currentTheme === 'light' ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>' ?>
            </a>
        </div>

        <div class="content-module active" id="dashboard">
            <div class="data-container">
                <h2 class="form-label"><i class="fa-solid fa-dice"></i> 欢迎回到Aniy音乐管理后台</h2>
                <p>在这里你可以查看音乐库的各项数据和进行相关管理操作</p>
                <?php
if (isset($_GET['success'])): ?>
    <div class="message message-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="message message-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>
            </div>
        </div>

        <div class="content-module" id="song-stats">
            <div class="data-container">
                <h2 class="form-label"><i class="fa-solid fa-chalkboard"></i> 当前服务器共存储歌曲</h2>
                <div class="stats-number"><?= $totalSongs ?></div>
            </div>
        </div>

        <div class="content-module" id="play-chart">
            <div class="data-container">
                <h2 class="form-label"><i class="fa-solid fa-chart-bar"></i> 歌曲播放量统计</h2>
                <?php if (!empty($topSongs)): ?>
                    <div class="chart-container">
                        <canvas id="playCountChart"></canvas>
                    </div>
                <?php else: ?>
                    <p style="color: var(--neutral-300); font-size: 0.9rem;">暂无足够数据生成图表</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-module" id="rankings">
            <div class="data-container">
                <h2 class="form-label"><i class="fa-solid fa-chart-simple"></i> 播放量 TOP10 排行榜</h2>
                <?php if (!empty($topSongs)): ?>
                    <div class="message-scroll-container">
                        <table class="rank-table">
                            <thead>
                                <tr>
                                    <th>RVTOP</th>
                                    <th>歌曲标题</th>
                                    <th>艺术家</th>
                                    <th>播放次数</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSongs as $index => $song): ?>
                                    <tr>
                                        <td class="rank-td">#<?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($song['title']) ?></td>
                                        <td><?= htmlspecialchars($song['artist']) ?></td>
                                        <td><?= number_format($song['play_count']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>暂无播放数据</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-module" id="messages">
            <div class="data-container">
                <h2 class="form-label"><i class="fa-solid fa-comments"></i> 求歌留言数据</h2>
                <?php if ($messageResult->num_rows > 0): ?>
                    <div class="message-scroll-container">
                        <table class="message-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>音乐名称</th>
                                    <th>歌手</th>
                                    <th>音源平台</th>
                                    <th>留言时间</th>
                                    <th>处理状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $messageResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['music_name']) ?></td>
                                    <td><?= htmlspecialchars($row['author']) ?></td>
                                    <td><?= htmlspecialchars($row['platform']) ?></td>
                                    <td><?= htmlspecialchars($row['create_time']) ?></td>
                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="status-completed">
                                                <i class="fa-solid fa-check"></i> 已完成
                                            </span>
                                        <?php else: ?>
                                            <?php if ($role === '超级管理员'): ?>
                                                <span class="status-pending">未处理</span>
                                                <a href="mark_done.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-primary btn-sm" style="margin-left: 0.5rem;">
                                                    <i class="fa-solid fa-circle-check"></i> 标记完成
                                                </a>
                                            <?php else: ?>
                                                <span class="status-disabled">
                                                    <i class="fa-solid fa-lock"></i> 无权限处理
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>暂无留言数据。</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-module" id="song-management">
            <?php if ($role === '超级管理员'): ?>
            <div class="data-container">
                <h2 class="form-label">
                    <i class="fa-solid fa-music"></i> 歌曲管理
                    <form method="get" class="search-form">
                        <input type="text" 
                               name="q" 
                               placeholder="搜索歌曲标题或艺术家" 
                               class="search-input" 
                               value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-magnifying-glass"></i> 搜索
                        </button>
                    </form>
                </h2>
                <br>
                <div class="message-scroll-container">
                    <table class="message-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>歌曲标题</th>
                                <th>艺术家</th>
                                <th>播放次数</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($songs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--neutral-300); padding: 1rem;">
                                        未找到符合条件的歌曲<?= !empty($searchQuery) ? "（关键词：{$searchQuery}）" : "" ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($songs as $song): ?>
                                <tr>
                                    <td><?= htmlspecialchars($song['id']) ?></td>
                                    <td><?= htmlspecialchars($song['title']) ?></td>
                                    <td><?= htmlspecialchars($song['artist']) ?></td>
                                    <td><?= number_format($song['play_count']) ?></td>
                                    <td>
                                        <a href="delete_song.php?id=<?= htmlspecialchars($song['id']) ?>" 
                                           class="btn btn-sm" 
                                           style="background-color: var(--error-color); color: white;" 
                                           onclick="return confirm('确定要删除这首歌曲吗？删除后无法恢复！')">
                                            <i class="fa-solid fa-trash"></i> 删除
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="content-module" id="upload-section">
            <?php if ($role !== '普通管理员'): ?>
            <div class="upload-section">
                <h2 class="form-label"><i class="fa-solid fa-circle-check"></i> 批量上传音乐</h2>
                <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="musicFiles" class="form-label">选择MP3文件（可多选）</label>
                        <input type="file" name="musicFiles[]" id="musicFiles" 
                               accept=".mp3" multiple 
                               class="file-input" 
                               required>
                    </div>
                    <br>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> 开始上传
                        </button>
                    </div>
                </form>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="message <?= $_GET['type'] === 'success' ? 'message-success' : 'message-error' ?>">
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="content-module" id="topic-publish">
    <?php if ($role === '超级管理员'): ?>  
    <div class="data-container">
        <h2 class="form-label"><i class="fa-solid fa-newspaper"></i> 发布新话题</h2>
        
        <?php if (isset($_GET['topic_msg'])): ?>
        <div class="message <?= $_GET['topic_type'] === 'success' ? 'message-success' : 'message-error' ?>">
            <?= htmlspecialchars($_GET['topic_msg']) ?>
        </div>
        <?php endif; ?>

        <form action="handle_topic_publish.php" method="post" enctype="multipart/form-data">
            <!-- 话题标题 -->
            <div class="form-group">
                <label class="form-label">话题标题</label>
                <input type="text" name="title" required class="form-input" 
                       placeholder="请输入话题标题（必填，建议不超过50字）">
            </div>

            <!-- 封面图片 -->
            <div class="form-group">
                <label class="form-label">封面图片（可选，建议尺寸 1200x600）</label>
                <input type="file" name="cover_image" accept="image/*" class="form-input file-input">
            </div>

            <!-- 内容来源 -->
            <div class="form-group">
                <label class="form-label">内容来源（如：官方资讯）</label>
                <input type="text" name="source" class="form-input" 
                       placeholder="选填，最多255字">
            </div>

            <!-- 创建者（只读） -->
            <div class="form-group">
                <label class="form-label">创建者</label>
                <input type="text" name="creator" value="<?= htmlspecialchars($username) ?>" 
                       readonly class="form-input">
            </div>

            <!-- 话题描述 -->
            <div class="form-group">
                <label class="form-label">话题描述</label>
                <textarea name="description" rows="5" required class="form-input" 
                          placeholder="请输入详细描述（必填，建议不超过1000字）"></textarea>
            </div>

            <!-- 阅读量和讨论量（并排布局） -->
            <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <label class="form-label">初始阅读量</label>
                    <input type="number" name="views" min="0" value="0" class="form-input" 
                           placeholder="请输入初始阅读量（默认0）">
                </div>
                <div>
                    <label class="form-label">初始讨论量</label>
                    <input type="number" name="discussions" min="0" value="0" class="form-input" 
                           placeholder="请输入初始讨论量（默认0）">
                </div>
            </div>

            <!-- 热问话题复选框 -->
            <div class="form-group form-check">
                <input type="checkbox" name="is_hot" value="1" class="form-check-input">
                <label class="form-check-label">设为热问话题（勾选后会在前台热问板块显示）</label>
            </div>

            <!-- 提交按钮 -->
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane me-1"></i> 发布话题
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="data-container">
        <p style="color: var(--error-color);">权限不足：您没有发布话题的权限</p>
    </div>
    <?php endif; ?>
</div>
    </main>

    <script>
        function showModule(moduleId) {
            document.querySelectorAll('.content-module').forEach(module => {
                module.classList.remove('active');
            });
            document.getElementById(moduleId).classList.add('active');
            
            document.querySelectorAll('.sidebar-menu-item a').forEach(link => {
                link.classList.remove('active');
                if (link.dataset.module === moduleId) {
                    link.classList.add('active');
                }
            });
        }

        function toggleTheme() {
            const currentTheme = document.body.className.replace('theme-', '');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            fetch(`?theme=${newTheme}`, { method: 'GET' })
                .then(() => {
                    document.body.className = `theme-${newTheme}`;
                })
                .catch(error => console.error('主题切换失败:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 初始化图表
            const chartCanvas = document.getElementById('playCountChart');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            label: '播放次数',
                            data: <?= json_encode($chartData) ?>,
                            pointRadius: 6,
                            pointBackgroundColor: 'white',
                            pointBorderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color'),
                            pointBorderWidth: 3,
                            borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color'),
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'TOP5歌曲播放量趋势',
                                font: {
                                    size: 16,
                                    weight: '500'
                                },
                                padding: { bottom: 20 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '播放次数',
                                    font: {
                                        size: 14,
                                        weight: '400'
                                    }
                                },
                                grid: {
                                    color: getComputedStyle(document.documentElement).getPropertyValue('--border-color'),
                                    drawBorder: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // 新增：搜索后保持歌曲管理模块逻辑
            const urlParams = new URLSearchParams(window.location.search);
            // 检测是否有搜索参数q 或 URL中包含歌曲管理模块标识
            if (urlParams.has('q') || window.location.hash === '#song-management') {
                showModule('song-management');
            }

            // 上传结果自动切换
            if (urlParams.has('msg')) {
                showModule('upload-section');
                const messageElement = document.querySelector('.message');
                if (messageElement) {
                    messageElement.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest' 
                    });
                }
            }
        });
    </script>
</body>
</html>