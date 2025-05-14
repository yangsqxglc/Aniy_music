<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>话题详情</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header-section {
            padding: 40px 15px 25px;
            max-width: 800px;
            margin: 0 auto;
        }

        .main-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e272e;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .meta-info {
            font-size: 14px;
            color: #57606f;
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 16px 0;
        }

        .content-section {
            max-width: 800px;
            margin: 0 auto 40px;
            padding: 0 15px;
            color: #2d3436;
            line-height: 1.7;
            font-size: 1.125rem;
        }

        .content-section img {
            width: 100%;
            border-radius: 12px;
            margin: 24px 0;
        }

        .content-section p {
            white-space: pre-wrap;  /* 保留换行符并自动换行 */
            word-break: break-all;  /* 长单词/URL强制换行 */
            margin: 16px 0;         /* 段落间距 */
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            color: #57606f;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .back-btn i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .loading {
            padding: 40px;
            text-align: center;
            color: #57606f;
        }

        .error {
            padding: 40px;
            text-align: center;
            color: #ff4757;
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 30px 15px 20px;
            }
            
            .main-title {
                font-size: 1.75rem;
            }

            .content-section {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .header-section {
                padding: 25px 10px 15px;
            }
            
            .main-title {
                font-size: 1.5rem;
            }

            .content-section {
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header-section" id="detailHeader"></div>
    <div class="content-section" id="detailContent"></div>

    <script>
        async function loadDetail() {
            try {
                const params = new URLSearchParams(window.location.search);
                const topicId = params.get('id');

                if (!topicId) {
                    throw new Error('缺少话题ID参数');
                }

                document.getElementById('detailHeader').innerHTML = '<div class="loading">加载中...</div>';

                // 请求详情接口（添加action=detail参数）
                const response = await fetch(`musicbokapi.php?action=detail&id=${topicId}`);
                const data = await response.json();

                if (data.code !== 200) {
                    throw new Error(data.error || '获取详情失败');
                }

                const topic = data.data;

                // 渲染头部信息（使用后端返回的create_time）
                document.getElementById('detailHeader').innerHTML = `
                    <a href="musicbok.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    <h1 class="main-title">${topic.title}</h1>
                    <div class="meta-info">
                        <span>${topic.source}</span>
                        <span>${new Date(topic.create_time).toLocaleDateString()}</span>
                        <span>${topic.views}阅读</span>
                        <span>${topic.discussions}讨论</span>
                    </div>
                `;

                // 渲染内容区域（将换行符转换为<br>并保留原始格式）
                const formattedContent = topic.detail_content
                    .replace(/\r?\n/g, '<br>')  // 替换Windows(\r\n)和Unix(\n)换行符
                    .replace(/\s{2,}/g, ' ');   // 合并连续空格（可选，根据需求）

                document.getElementById('detailContent').innerHTML = `
                    <img src="${topic.detail_image}" alt="${topic.title}详情图">
                    <p>${formattedContent}</p>
                `;

            } catch (error) {
                console.error('加载详情失败:', error);
                document.getElementById('detailHeader').innerHTML = `<div class="error">${error.message}</div>`;
                document.getElementById('detailContent').innerHTML = '';
            }
        }

        window.onload = loadDetail;
    </script>
</body>
</html>
    