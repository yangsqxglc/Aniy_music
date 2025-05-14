<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>音乐流行排行榜</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-50': '#f5f7ff',
                        'primary-100': '#e6e9fa',
                        'primary-600': '#4361ee',
                        'primary-700': '#3a0ca3',
                    },
                    boxShadow: {
                        'card': '0 4px 24px rgba(58, 12, 163, 0.08)',
                        'card-hover': '0 8px 32px rgba(58, 12, 163, 0.15)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4361ee 20%, #3a0ca3 80%);
            --hover-gradient: linear-gradient(135deg, #4361ee 30%, #3a0ca3 70%);
        }
        .rank-number {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 900;
            letter-spacing: -0.08em;
            line-height: 1;
            padding: 3px;
        }
        .song-card {
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
            backdrop-filter: blur(16px);
        }
        .song-card:hover {
            transform: translateY(-4px) scale(1.005);
            background: linear-gradient(135deg, white 0%, rgba(255,255,255,0.98) 100%);
        }
        .divider {
            border-color: #e6e9fa;
            margin: 1.5rem 0;
            opacity: 0.2;
        }
        .skeleton {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.8s infinite linear;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .cover-shadow {
            box-shadow: 0 4px 16px rgba(58, 12, 163, 0.15);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-50 to-indigo-50">
    <div class="container mx-auto px-3 py-8 sm:px-6">
        <!-- 标题部分 -->
        <div class="mb-12 sm:mb-16 text-center relative">
            <div class="absolute -top-8 -left-1/2 -translate-x-1/2 mix-blend-soft-light opacity-10">
                <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                    <circle cx="100" cy="100" r="80" fill="url(#gradient1)"/>
                </svg>
                <defs>
                    <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#4361ee" stop-opacity="0.2"/>
                        <stop offset="100%" stop-color="#3a0ca3" stop-opacity="0.2"/>
                    </linearGradient>
                </defs>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-3 relative">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-700">Aniy音乐</span>-热度排行榜
            </h1>
            <p class="text-sm sm:text-base text-primary-600 max-w-md mx-auto">
                基于实时播放数据计算，每15分钟更新
            </p>
        </div>
        
        <!-- 排行榜主容器 -->
        <div class="bg-white/95 backdrop-blur-3xl rounded-3xl shadow-card p-4 sm:p-6">
            <div class="space-y-4" id="rankList"></div>
        </div>
    </div>

    <script>
        const API_URL = 'api.php'; 
        const TOP_COUNT = 30;

        async function loadRankData() {
            try {
                const rankList = document.getElementById('rankList');
                rankList.innerHTML = Array(TOP_COUNT).fill().map((_, i) => `
                    <div class="song-card flex items-center p-4 sm:p-5 rounded-2xl">
                        <div class="flex items-center mr-4 sm:mr-6">
                            <div class="skeleton w-6 h-6 rounded-full mr-3 sm:mr-4"></div>
                            <div class="skeleton w-8 h-8 rounded-lg"></div>
                        </div>
                        <div class="skeleton w-14 h-14 sm:w-18 sm:h-18 rounded-lg mr-4 sm:mr-6 cover-shadow"></div>
                        <div class="flex-1">
                            <div class="skeleton h-4 sm:h-5 w-5/6 mb-2 sm:mb-3"></div>
                            <div class="skeleton h-3 sm:h-4 w-4/6"></div>
                        </div>
                        <div class="text-right">
                            <div class="skeleton h-4 sm:h-5 w-5/6 mb-2"></div>
                            <div class="skeleton h-3 sm:h-4 w-4/6"></div>
                        </div>
                    </div>
                    ${i !== TOP_COUNT-1 ? '<div class="divider border-t"></div>' : ''}
                `).join('');

                const response = await fetch(API_URL);
                if (!response.ok) throw new Error('网络请求失败');
                const songs = await response.json();
                
                const sortedSongs = songs.sort((a, b) => b.play_count - a.play_count).slice(0, TOP_COUNT);
                renderRankList(sortedSongs);
            } catch (error) {
                console.error('加载数据失败:', error);
                rankList.innerHTML = `
                    <div class="p-8 text-center">
                        <p class="text-lg sm:text-xl text-gray-600">很抱歉，暂时无法加载榜单数据</p>
                        <p class="text-sm text-gray-400 mt-2">请检查网络连接或稍后重试</p>
                    </div>
                `;
            }
        }

        function renderRankList(songs) {
            const rankList = document.getElementById('rankList');
            const displayCount = Math.min(songs.length, TOP_COUNT);
            
            rankList.innerHTML = songs.map((song, index) => {
                if (index >= TOP_COUNT) return '';

                return `
                    <div class="song-card flex items-center p-4 sm:p-5 rounded-2xl">
                        <div class="flex items-center mr-4 sm:mr-6">
                            <span class="text-4xl sm:text-5xl rank-number">${String(index + 1).padStart(2, '0')}</span>
                        </div>
                        
                        <img src="${song.cover || 'https://picsum.photos/180/180?random='+index}" 
                             alt="${song.title}封面" 
                             class="w-14 h-14 sm:w-18 sm:h-18 rounded-lg object-cover mr-4 sm:mr-6 cover-shadow transition-transform hover:scale-105">
                        
                        <div class="flex-1">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 truncate">${song.title}</h4>
                            <p class="text-sm text-gray-500 mt-1.5">
                                ${song.artist || '未知艺术家'} · ${song.album || '未知专辑'}
                            </p>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-sm font-medium text-primary-600">
                                <i class="fa-solid fa-play mr-1 text-xs"></i>${song.play_count.toLocaleString()}次
                            </p>
                            <p class="text-xs text-gray-400 mt-1">涨跌 ${song.play_count.toLocaleString()}%</p>
                        </div>
                    </div>
                    ${index !== displayCount - 1 ? '<div class="divider border-t"></div>' : ''}
                `;
            }).join('');
        }

        loadRankData();
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>
</html>
