<?php
// 数据库配置
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "musicdb";

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 处理表单提交
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $music_name = $conn->real_escape_string($_POST['music_name']);
    $author = $conn->real_escape_string($_POST['author']);
    $platform = $conn->real_escape_string($_POST['platform']);
    
    // 处理"其他平台"情况
    if ($platform === 'other') {
        $platform = $conn->real_escape_string($_POST['other_platform']);
    }

    // 插入数据SQL
    $sql = "INSERT INTO music_info (music_name, author, platform, create_time) 
            VALUES ('$music_name', '$author', '$platform', NOW())";

    if ($conn->query($sql) === TRUE) {
        $message = '<div class="alert alert-success" role="alert"><i class="fa-solid fa-check me-2"></i>提交成功！</div>';
    } else {
        $message = '<div class="alert alert-error" role="alert"><i class="fa-solid fa-exclamation-triangle me-2"></i>提交失败: ' . $conn->error . '</div>';
    }
}

// 创建表（如果不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS music_info (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    music_name VARCHAR(100) NOT NULL,
    author VARCHAR(50) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_table_sql);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aniy音乐留言-分享你的好歌</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;    /* 主色调（蓝色） */
            --success-color: #22c55e;   /* 成功色（绿色） */
            --error-color: #ef4444;     /* 错误色（红色） */
            --border-color: #e5e7eb;    /* 边框色（浅灰） */
            --bg-light: #f8fafc;        /* 轻背景色 */
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 680px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .container {
            background: white;
            width: 60%;
            padding: 3.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #111827;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 2rem;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        select.input-field {
            appearance: none;
            background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 1rem center;
            background-size: 18px;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .submit-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        #otherPlatform {
            margin-top: 0.5rem;
            display: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* 响应式优化 */
        @media (max-width: 640px) {
            .container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Aniy音乐留言</h2>
        <p>如果你有好听的音乐，不妨与大家分享</p>
        <?php echo $message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="music_name">音乐名称：</label>
                <input type="text" id="music_name" name="music_name" class="input-field" required>
            </div>

            <div class="form-group">
                <label for="author">作者：</label>
                <input type="text" id="author" name="author" class="input-field" required>
            </div>

            <div class="form-group">
                <label>来源平台：</label>
                <select id="platform" name="platform" class="input-field" onchange="toggleOtherPlatform()">
                    <option value="网易云音乐">网易云音乐</option>
                    <option value="QQ音乐">QQ音乐</option>
                    <option value="酷狗音乐">酷狗音乐</option>
                    <option value="哔哩哔哩">哔哩哔哩</option>
                    <option value="抖音">抖音</option>
                    <option value="other">其他</option>
                </select>
                <input type="text" id="otherPlatform" name="other_platform" class="input-field" placeholder="请输入其他平台名称">
            </div>

            <div class="form-group">
                <button type="submit" class="submit-btn">
                     立即提交
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleOtherPlatform() {
            const select = document.getElementById('platform');
            const otherInput = document.getElementById('otherPlatform');
            
            // 控制显示隐藏
            otherInput.style.display = select.value === 'other' ? 'block' : 'none';
            
            // 控制必填状态
            otherInput.required = select.value === 'other';
            
            // 输入框获得焦点
            if (select.value === 'other') {
                otherInput.focus();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
