<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aniy音乐-资讯</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header-section {
            padding: 40px 15px 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .main-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e272e;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .sub-title {
            font-size: 1.125rem;
            color: #57606f;
            line-height: 1.5;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 15px;
        }

        .container {
            flex: 0 0 calc(50% - 10px);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .container:hover {
            transform: translateY(-4px);
        }

        .bg-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .text-overlay {
            position: absolute;
            top: 0; 
            bottom: 0; 
            left: 0; 
            right: 0;
            padding: 20px; /* 保持热问标签的初始内边距 */
            background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 60%);
            color: white;
        }

        .hot-tag {
            display: inline-flex;
            align-items: center;
            background: #ff4757;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 12px; /* 热问标签与下方内容的间距 */
        }

        .hot-dot {
            width: 8px;
            height: 8px;
            background: #ffeaa7;
            border-radius: 50%;
            margin-right: 6px;
            animation: blink 1.2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .topic-title {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
            margin-top: 105px;
            margin-bottom: 8px; /* 标题与元信息的间距 */
        }

        .meta-info {
            font-size: 14px;
            color: #f1f2f6;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px; /* 元信息与描述的间距 */
        }

        .description {
            font-size: 14px;
            line-height: 1.5;
            color: #dfe6e9;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 25px; /* 关键！调整此值控制文字与底部的距离 */
        }

        @media (max-width: 768px) {
            .container {
                flex: 0 0 100%;
            }
            
            .text-overlay {
                padding: 15px;
            }

            .description {
                margin-bottom: 15px; /* 移动端调整为较小值 */
            }
        }

        @media (max-width: 480px) {
            .header-section {
                padding: 25px 10px 15px;
            }
            
            .main-title {
                font-size: 1.5rem;
            }

            .card-container {
                padding: 0 10px;
            }

            .bg-image {
                height: 260px;
            }

            .topic-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h1 class="main-title">Aniy音乐热点资讯</h1>
        <p class="sub-title">精选当前世界上最热门的音乐专辑和音乐制作人动态，为用户提供最新的消息</p>
    </div>

    <div class="card-container" id="cardContainer"></div>

    <script>
        async function loadTopics() {
            try {
                const response = await fetch('musicbokapi.php?action=list');
                const data = await response.json();

                if (data.code !== 200) {
                    throw new Error(data.error || '数据加载失败');
                }

                const cardContainer = document.getElementById('cardContainer');
                cardContainer.innerHTML = '';

                data.data.forEach(topic => {
                    const cardHtml = `
                        <div class="container" data-id="${topic.id}">
                            <img src="${topic.cover_image}" class="bg-image" alt="${topic.title}背景图">
                            <div class="text-overlay">
                                <div class="hot-tag">
                                    <span class="hot-dot"></span>
                                    <span>热问</span>
                                </div>
                                <h3 class="topic-title">${topic.title}</h3>
                                <div class="meta-info">
                                    <span>${topic.source}</span>
                                    <span>${topic.creator}</span>
                                    <span>${topic.views}阅读</span>
                                    <span>${topic.discussions}讨论</span>
                                </div>
                                <p class="description">${topic.description}</p>
                            </div>
                        </div>
                    `;

                    cardContainer.insertAdjacentHTML('beforeend', cardHtml);
                });

                document.querySelectorAll('.container').forEach(card => {
                    card.addEventListener('click', () => {
                        const topicId = card.dataset.id;
                        window.location.href = `detail.php?id=${encodeURIComponent(topicId)}`;
                    });
                });

            } catch (error) {
                console.error('加载数据失败:', error);
                cardContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: red;">数据加载失败，请重试</div>';
            }
        }

        window.onload = loadTopics;
    </script>
</body>
</html>
    