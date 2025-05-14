<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aniy音乐曲库</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;          /* 主色调 */
            --secondary-color: #ec4899;       /* 辅助色调 */
            --soft-white: #fafafa;            /* 柔和白色 */
            --light-gray: #f3f4f6;            /* 浅灰色背景 */
            --text-dark: #1a1a1a;             /* 深文本色 */
            --text-medium: #4b5563;           /* 中等文本色 */
            --text-light: #6b7280;            /* 浅文本色 */
            --shadow-depth: 0 4px 24px rgba(0, 0, 0, 0.06);
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Noto+Sans+SC:wght@300;400;500;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            list-style: none;
            text-decoration: none;
        }

        body {
            font-family: 'Inter', 'Noto Sans SC', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-medium);
            background: var(--light-gray);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 40px 24px;
            flex: 1;
        }

        /* 新增标题区域样式 */
        .page-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .page-title {
            font-size: 2.5rem;         /* 40px */
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: 1.25rem;       /* 20px */
            font-weight: 400;
            color: var(--text-medium);
            opacity: 0.9;
            letter-spacing: 0.5px;
        }

        .loading {
            text-align: center;
            padding: 30px;
            font-size: 1.125rem;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .loading i {
            font-size: 20px;
            animation: spin 1.5s linear infinite;
        }

        .music-card-container {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 20px 0;
        }

        .music-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-depth);
            overflow: hidden;
            border: 1px solid transparent;
            transition: all 0.3s cubic-bezier(0.075, 0.82, 0.165, 1);
            transform: scale(1);
        }

        .music-card:hover {
            transform: scale(1.015);
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.1);
            border-color: rgba(37, 99, 235, 0.08);
        }

        .card-header {
            position: relative;
            height: 0;
            padding-top: 100%; /* 1:1宽高比 */
            overflow: hidden;
        }

        .card-cover {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            filter: brightness(0.95);
            transition: filter 0.3s, transform 0.6s;
        }

        .card-cover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            flex-direction: column;
            padding: 16px;
            justify-content: flex-end;
        }

        .music-card:hover .card-cover {
            filter: brightness(1.05);
            transform: scale(1.02);
        }

        .play-icon {
            position: absolute;
            right: 16px;
            bottom: 16px;
            font-size: 24px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-content {
            padding: 24px 28px 32px;
            text-align: left;
        }

        .card-title {
            font-size: 1.375rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-artist {
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 16px;
            font-weight: 400;
            opacity: 0.9;
        }

        .tag-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .tag {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tag-primary {
            background: linear-gradient(135deg, #ebf2ff 0%, #dbeafe 100%);
            color: var(--primary-color);
        }

        .tag-secondary {
            background: linear-gradient(135deg, #fee2f8 0%, #fecdd3 100%);
            color: var(--secondary-color);
        }

        .tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.06);
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (min-width: 1440px) {
            .music-card-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .music-card-container {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 1024px) {
            .music-card-container {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 1.5rem;
            }
            
            .card-header {
                padding-top: 100%;
            }
        }

        @media (max-width: 768px) {
            .music-card-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .card-header {
                padding-top: 100%;
            }
        }

        @media (max-width: 480px) {
            .music-card-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .card-header {
                padding-top: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 新增标题区域 -->
        <div class="page-header">
            <h1 class="page-title">Aniy音乐曲库</h1>
            <p class="page-subtitle">发现全球优质音乐，探索声音的无限可能</p>
        </div>

        <div class="loading" id="loading">
            <i class="fas fa-spinner"></i>
            <span>加载音乐数据中...</span>
        </div>
        <div class="music-card-container" id="cardContainer"></div>
    </div>

    <script>
        const TAG_POOL = ['流行', '摇滚', '民谣', '治愈', '励志', '电子', '古典', '爵士', '独立', '国风'];
        
        async function loadMusicData() {
            try {
                const response = await fetch('api.php'); 
                const songs = await response.json();
                
                const container = document.getElementById('cardContainer');
                const loading = document.getElementById('loading');
                loading.style.display = 'none';
                
                songs.forEach(song => {
                    const card = createMusicCard(song);
                    container.appendChild(card);
                });
            } catch (error) {
                console.error('获取音乐数据失败:', error);
                const loading = document.getElementById('loading');
                loading.textContent = '加载失败，请刷新重试';
                loading.style.color = '#dc2626';
            }
        }

        function generateRandomTags(title) {
            let hash = 0;
            for (let i = 0; i < title.length; i++) {
                hash = title.charCodeAt(i) + ((hash << 5) - hash);
            }
            
            const tagCount = Math.floor(Math.abs(hash) % 3) + 2;
            const tags = [];
            for (let i = 0; i < tagCount; i++) {
                const index = Math.abs(hash + i) % TAG_POOL.length;
                tags.push(TAG_POOL[index]);
            }
            return [...new Set(tags)];
        }

        function createMusicCard(song) {
            const card = document.createElement('div');
            card.className = 'music-card';

            const header = document.createElement('div');
            header.className = 'card-header';
            
            const cover = document.createElement('img');
            cover.className = 'card-cover';
            cover.src = song.cover || 'https://picsum.photos/300/300?random=' + Math.random();
            cover.alt = `${song.title} 专辑封面`;
            
            const overlay = document.createElement('div');
            overlay.className = 'card-cover-overlay';
            
            const playIcon = document.createElement('div');
            playIcon.className = 'play-icon';
            playIcon.innerHTML = '<i class="fas fa-play"></i>';
            
            const content = document.createElement('div');
            content.className = 'card-content';
            
            const title = document.createElement('h3');
            title.className = 'card-title';
            title.textContent = song.title || '未知歌曲';
            
            const artist = document.createElement('p');
            artist.className = 'card-artist';
            artist.textContent = song.artist || '未知歌手';
            
            const tags = document.createElement('ul');
            tags.className = 'tag-list';
            const randomTags = generateRandomTags(song.title);
            randomTags.forEach(tagText => {
                const tagItem = document.createElement('li');
                tagItem.className = `tag ${Math.random() > 0.5 ? 'tag-primary' : 'tag-secondary'}`;
                tagItem.textContent = tagText;
                tags.appendChild(tagItem);
            });

            header.appendChild(cover);
            header.appendChild(overlay);
            header.appendChild(playIcon);
            content.appendChild(title);
            content.appendChild(artist);
            content.appendChild(tags);
            card.appendChild(header);
            card.appendChild(content);

            return card;
        }

        window.addEventListener('load', loadMusicData);
    </script>
</body>
</html>
    