<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" >
    <title>Aniy音乐 - 个性十足的小音乐网站</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

        html {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Ubuntu', sans-serif;
            font-size: 12px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f4f4;
        }

        .main-container {
            position: relative;
            width: 90%;
            max-width: 1200px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .content-wrapper {
            display: flex;
            width: 100%;
        }

        .player-container {
            width: 400px;
            padding: 20px;
            margin-top: 16px;
            position: relative;
        }

        .background {
            position: fixed;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            z-index: -1;
        }

        .background img {
            position: absolute;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            min-width: 50%;
            min-height: 50%;
            filter: blur(15px);
            -webkit-filter: blur(50px);
            transform: scale(1.1);
        }

        .player-img {
            width: 300px;
            height: 300px;
            position: relative;
            top: -50px;
            left: 50px;
            margin-top: 50px;
        }

        .player-img img {
            object-fit: cover;
            border-radius: 20px;
            height: 0;
            width: 0;
            opacity: 0;
            box-shadow: 0 5px 30px 5px rgba(0, 0, 0, 0.5);
        }

        .player-img img.active {
            width: 100%;
            height: 100%;
            transition: all 0.5s;
            opacity: 1;
        }

        h2 {
            font-size: 25px;
            text-align: center;
            font-weight: 500;
            margin: 10px 0 0;
        }

        h3 {
            font-size: 18px;
            text-align: center;
            font-weight: 500;
            margin: 10px 0 0;
        }

        .player-progress {
            background-color: darkgray;
            border-radius: 5px;
            cursor: pointer;
            margin: 40px 20px 35px;
            height: 6px;
            width: 90%;
        }

        .progress {
            background-color: #212121;
            border-radius: 5px;
            height: 100%;
            width: 0%;
            transition: width 0.1s linear;
        }

        .music-duration {
            position: relative;
            top: -25px;
            display: flex;
            justify-content: space-between;
        }

        .player-controls {
            position: relative;
            top: -15px;
            left: 58%;
            transform: translateX(-50%);
            width: fit-content;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fa-solid {
            font-size: 30px;
            color: #666;
            margin: 0 15px;
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
        }

        .fa-solid:hover {
            filter: brightness(40%);
        }

        .play-button {
            font-size: 44px;
            position: relative;
            top: 3px;
        }

        .music-list-container {
            flex: 1;
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .music-list-container::-webkit-scrollbar {
            display: none;
        }

        .music-list {
            list-style-type: none;
            padding: 0;
        }

        .music-list li {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .music-list li:hover {
            background-color: #f0f0f0;
        }

        .music-list li img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        .music-list li h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
        }

        .music-list li p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 100%;
            width: 250px;
            height: 100%;
            background: #fff;
            border-left: 1px solid #ddd;
            padding: 20px;
            box-shadow: -5px 0 10px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 100;
        }

        .sidebar.show {
            right: 0;
        }

        .toggle-sidebar-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 30px;
            height: 30px;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            z-index: 101;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 15px;
            text-align: left;
        }

        .bgtx {
            height: 100%;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .bgtx::-webkit-scrollbar {
            display: none;
        }

        .bgtx p {
            font-size: 14px;
            line-height: 1.5;
            color: #555;
        }

        .bgtx ul {
            padding-left: 20px;
            margin: 10px 0;
        }

        .bgtx li {
            margin-bottom: 8px;
        }

        .bgtx hr {
            margin: 15px 0;
            border: 0;
            border-top: 1px solid #eee;
        }

        .bgtx a {
            color: #2D68F0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .bgtx a:hover {
            color: #1A4BC9;
            text-decoration: underline;
        }

        .bgtx h1 {
            font-size: 22px;
            margin: 0 0 15px;
            color: #2D68F0;
        }

        .play-count {
            font-size: 10px;
            color: #999;
        }

        /* 搜索栏样式 */
.search-bar {
    padding: 15px 20px;
    border-radius: 30px;
    margin: 0 20px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    justify-content: flex-end; /* 整体内容右对齐 */
}

.search-input-wrap {
    position: relative;
    flex: none; /* 取消弹性扩展 */
    width: 30%; /* 设定输入框容器宽度（可根据需要调整） */
    border-radius: 15px;
    background-color: white;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.search-input {
    width: 70%; /* 输入框占满容器宽度 */
    padding: 12px 40px 12px 20px;
    background: linear-gradient(135deg, #f8f9fa, #edf2f7);
    border: none;
    border-radius: 15px;
    font-size: 15px;
    color: #4b5563;
    outline: none;
    transition: box-shadow 0.3s ease, transform 0.2s ease;
}

.search-input:focus {
    box-shadow: inset 0 0 0 2px #2D68F0;
    transform: scale(1.02);
}

.search-btn {
    padding: 12px 25px;
    font-size: 12px;
    font-weight: 500;
    background: linear-gradient(135deg,rgb(93, 94, 95),rgb(46, 45, 45));
    color: white;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 12px rgba(45, 104, 240, 0.2);
}

.search-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 18px rgba(45, 104, 240, 0.3);
}
    

        /* 清除按钮样式 */
        .clear-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9ca3af;
            cursor: pointer;
            display: none;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .clear-btn:hover {
            color: #4b5563;
            transform: translateY(-50%) scale(1.1);
        }

        /* 返回顶部按钮样式 */
        #back-to-top {
            position: fixed;
            right: 190px;
            bottom: 70px;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 10%;
            background-color: rgba(141, 138, 138, 0.8);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100;
            transition: opacity 0.3s ease;
        }

        #back-to-top:hover {
            background-color: rgba(42, 40, 40, 0.9);
        }
       /* 修正类名拼写错误并调整布局 */
    .titlebt {  /* 原titiebt拼写错误 */
        margin-top: -5px;
        /* 移除不合理的右外边距 */
        /* margin-right: 185px; */
        display: inline-block;  /* 保持行内块布局 */
        vertical-align: middle;  /* 垂直居中对齐 */
        margin-right: 20px;  /* 适当添加右间距 */
        font-size: 35px;
    }

    .search-bar {
        /* 添加弹性布局方便元素排列 */
        display: flex;
        align-items: center;
        justify-content: space-between; /* 两端对齐 */
        padding: 10px 0;
    }

    .tb {
    color: black;
    /* 添加过渡效果：颜色过渡0.3秒，延迟0.2秒 */
    transition: color 0.3s 0.2s;
}
.tb:hover {
    color:rgb(243, 8, 8);
}
    
    
    .dynamic-hr {
    height: 3px;
    border: 0;
    margin: 24px 0;
    position: relative;
    border-radius: 2px;
    overflow: hidden;  /* 配合伪元素裁剪 */
    
    /* 主渐变层 */
    background: linear-gradient(90deg,
        hsl(271, 76%, 65%) 0%,     /* 更柔和的紫色 */
        hsl(197, 100%, 36%) 25%,   /* 海洋蓝 */
        hsl(45, 100%, 50%) 50%,    /* 活力黄 */
        hsl(350, 88%, 44%) 75%,    /* 热情红 */
        hsl(271, 76%, 65%) 100%
    );
    background-size: 200% 100%;
    animation: gradient-flow 8s linear infinite;

    /* 添加底部柔光层 */
    &::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        height: 8px;
        background: inherit;
        filter: blur(12px);
        opacity: 0.2;
        transform: translateY(4px);
    }
}

@keyframes gradient-flow {
    0% {
        background-position: 0% 0%;
    }
    100% {
        background-position: -200% 0%;  /* 反向流动更自然 */
    }
}

/* 鼠标悬停时加速动画 */
@media (hover: hover) {
    .dynamic-hr:hover {
        animation-duration: 4s;
    }
}
    
    
</style>
</head>

<body>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="player-container">
                <div class="background">
                    <img src="" id="bg-img">
                </div>

                <div class="player-img">
                    <img src="" class="active" id="cover">
                </div>

                <h2 id="music-title"></h2>
                <h3 id="music-artist"></h3>

                <div class="player-progress" id="player-progress">
                    <div class="progress" id="progress"></div>
                    <div class="music-duration">
                        <span id="current-time">0:00</span>
                        <span id="duration">0:00</span>
                    </div>
                </div>

                <div class="player-controls">
                    <i class="fa-solid fa-backward-step" title="Previous" id="prev"></i>
                    <i class="fa-solid fa-circle-play" title="Play" id="play"></i>
                    <i class="fa-solid fa-forward-step" title="Next" id="next"></i>
                    <i class="fa-solid fa-list" title="Normal Play" id="play-mode"></i>
                </div>
            </div>

            <div class="music-list-container">
                <div class="search-bar">
                    <!-- 修正类名并调整结构 -->
                    <h3 class="titlebt">Aniy<span style="font-size:15px;">音乐</span></h3> <!-- 修正类名 -->
                    <a href="musicpm.php" target="_blank" class="tb"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M2 13H8V21H2V13ZM16 8H22V21H16V8ZM9 3H15V21H9V3ZM4 15V19H6V15H4ZM11 5V19H13V5H11ZM18 10V19H20V10H18Z"></path></svg></a>
                    <a href="musicacard.php" target="_blank" class="tb"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M10.4222 11.375C10.1278 12.4026 10.4341 13.4395 11.2058 14.0282C12.267 14.8376 13.7712 14.3289 14.0796 13.0331C14.1599 12.6958 14.1833 12.311 14.1067 11.9767C13.8775 10.9756 13.586 9.98862 13.3147 8.98094C11.9843 9.13543 10.7722 10.1533 10.4222 11.375ZM15.9698 11.0879C16.2427 12.1002 16.2553 13.1053 15.8435 14.0875C14.7148 16.7784 11.1215 17.2286 9.26951 14.9136C7.96829 13.2869 7.99065 10.953 9.32982 9.18031C10.1096 8.14796 11.1339 7.47322 12.3776 7.12595C12.5007 7.09159 12.6241 7.058 12.7566 7.02157C12.6731 6.60736 12.569 6.20612 12.5143 5.79828C12.3375 4.48137 13.026 3.29477 14.2582 2.7574C15.4836 2.22294 16.9661 2.54204 17.7889 3.51738C18.1936 3.99703 18.183 4.59854 17.7631 4.98218C17.3507 5.359 16.7665 5.32761 16.3276 4.89118C16.0809 4.64585 15.8185 4.45112 15.451 4.45569C14.9264 4.46223 14.4642 4.87382 14.5058 5.39329C14.5432 5.86105 14.6785 6.32376 14.8058 6.77892C14.8276 6.85679 15.0218 6.91415 15.1436 6.9321C16.4775 7.12862 17.6476 7.66332 18.6165 8.60769C21.1739 11.1006 21.4772 15.1394 19.2882 18.0482C17.7593 20.0797 15.6785 21.2165 13.1609 21.4567C8.53953 21.8977 4.49683 18.9278 3.46188 14.3992C2.5147 10.2551 4.8397 5.83074 8.79509 4.25032C9.38067 4.01635 9.93787 4.21869 10.1664 4.74827C10.3982 5.28546 10.147 5.83389 9.55552 6.09847C7.18759 7.15787 5.73935 8.9527 5.34076 11.5215C4.80806 14.9546 6.99662 18.2982 10.3416 19.2428C13.0644 20.0117 15.9994 19.0758 17.6494 16.9123C19.2354 14.8328 19.0484 11.8131 17.2221 10.0389C16.7172 9.54838 16.1246 9.21455 15.3988 9.02564C15.5974 9.74151 15.7879 10.4136 15.9698 11.0879Z"></path></svg></a>
                    <a href="musicbok.php" target="_blank" class="tb"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14 22.5L11.2 19H6C5.44772 19 5 18.5523 5 18V7.10256C5 6.55028 5.44772 6.10256 6 6.10256H22C22.5523 6.10256 23 6.55028 23 7.10256V18C23 18.5523 22.5523 19 22 19H16.8L14 22.5ZM15.8387 17H21V8.10256H7V17H11.2H12.1613L14 19.2984L15.8387 17ZM2 2H19V4H3V15H1V3C1 2.44772 1.44772 2 2 2Z"></path></svg></a>
                    <a href="code.php" target="_blank" class="tb"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M5.88401 18.6533C5.58404 18.4526 5.32587 18.1975 5.0239 17.8369C4.91473 17.7065 4.47283 17.1524 4.55811 17.2583C4.09533 16.6833 3.80296 16.417 3.50156 16.3089C2.9817 16.1225 2.7114 15.5499 2.89784 15.0301C3.08428 14.5102 3.65685 14.2399 4.17672 14.4263C4.92936 14.6963 5.43847 15.1611 6.12425 16.0143C6.03025 15.8974 6.46364 16.441 6.55731 16.5529C6.74784 16.7804 6.88732 16.9182 6.99629 16.9911C7.20118 17.1283 7.58451 17.1874 8.14709 17.1311C8.17065 16.7489 8.24136 16.3783 8.34919 16.0358C5.38097 15.3104 3.70116 13.3952 3.70116 9.63971C3.70116 8.40085 4.0704 7.28393 4.75917 6.3478C4.5415 5.45392 4.57433 4.37284 5.06092 3.15636C5.1725 2.87739 5.40361 2.66338 5.69031 2.57352C5.77242 2.54973 5.81791 2.53915 5.89878 2.52673C6.70167 2.40343 7.83573 2.69705 9.31449 3.62336C10.181 3.41879 11.0885 3.315 12.0012 3.315C12.9129 3.315 13.8196 3.4186 14.6854 3.62277C16.1619 2.69 17.2986 2.39649 18.1072 2.52651C18.1919 2.54013 18.2645 2.55783 18.3249 2.57766C18.6059 2.66991 18.8316 2.88179 18.9414 3.15636C19.4279 4.37256 19.4608 5.45344 19.2433 6.3472C19.9342 7.28337 20.3012 8.39208 20.3012 9.63971C20.3012 13.3968 18.627 15.3048 15.6588 16.032C15.7837 16.447 15.8496 16.9105 15.8496 17.4121C15.8496 18.0765 15.8471 18.711 15.8424 19.4225C15.8412 19.6127 15.8397 19.8159 15.8375 20.1281C16.2129 20.2109 16.5229 20.5077 16.6031 20.9089C16.7114 21.4504 16.3602 21.9773 15.8186 22.0856C14.6794 22.3134 13.8353 21.5538 13.8353 20.5611C13.8353 20.4708 13.836 20.3417 13.8375 20.1145C13.8398 19.8015 13.8412 19.599 13.8425 19.4094C13.8471 18.7019 13.8496 18.0716 13.8496 17.4121C13.8496 16.7148 13.6664 16.2602 13.4237 16.051C12.7627 15.4812 13.0977 14.3973 13.965 14.2999C16.9314 13.9666 18.3012 12.8177 18.3012 9.63971C18.3012 8.68508 17.9893 7.89571 17.3881 7.23559C17.1301 6.95233 17.0567 6.54659 17.199 6.19087C17.3647 5.77663 17.4354 5.23384 17.2941 4.57702L17.2847 4.57968C16.7928 4.71886 16.1744 5.0198 15.4261 5.5285C15.182 5.69438 14.8772 5.74401 14.5932 5.66413C13.7729 5.43343 12.8913 5.315 12.0012 5.315C11.111 5.315 10.2294 5.43343 9.40916 5.66413C9.12662 5.74359 8.82344 5.69492 8.57997 5.53101C7.8274 5.02439 7.2056 4.72379 6.71079 4.58376C6.56735 5.23696 6.63814 5.77782 6.80336 6.19087C6.94565 6.54659 6.87219 6.95233 6.61423 7.23559C6.01715 7.8912 5.70116 8.69376 5.70116 9.63971C5.70116 12.8116 7.07225 13.9683 10.023 14.2999C10.8883 14.3971 11.2246 15.4769 10.5675 16.0482C10.3751 16.2156 10.1384 16.7802 10.1384 17.4121V20.5611C10.1384 21.5474 9.30356 22.2869 8.17878 22.09C7.63476 21.9948 7.27093 21.4766 7.36613 20.9326C7.43827 20.5204 7.75331 20.2116 8.13841 20.1276V19.1381C7.22829 19.1994 6.47656 19.0498 5.88401 18.6533Z"></path></svg></a>
                    <div class="search-input-wrap">
                        <input type="text" class="search-input" id="search-input" placeholder="搜索歌曲或歌手...">
                        <i class="fa-solid fa-xmark clear-btn" id="clear-btn"></i>
                    </div>
                    <button class="search-btn" id="search-btn">搜索</button>
                </div>
                <hr class="dynamic-hr">
                <ul class="music-list" id="music-list"></ul>
            </div>
        </div>
    </div>

    <!-- 右侧边栏 -->
    <div class="sidebar" id="sidebar">
        <div class="toggle-sidebar-btn" id="toggle-btn">
            <i class="fa-solid fa-square-plus" style="color:rgb(189, 191, 193);"></i>
        </div>
        <div class="bgtx">
            <br>
            <br>
            <br>
            <br>
            <h1 id="sidebar-title">🔥 欢迎来到Aniy音乐 🌈</h1>
            <p id="sidebar-text">
                <span style="color:red">🚫注意：</span>欢迎来到Aniy音乐，本网站为非盈利网站，本站数据来源于网络，如有侵权请联系删除！
            </p>
            <hr />
            <h2 id="sidebar-title">📥 如何下载</h2>
            <p id="sidebar-text">
                <p>本站音乐支持下载，如有需求前往下列地址进行下载</p>
                <ul>
                    <li><h4><a href="http://www.baidu.com">第一下载服务器</a></h4></li>
                    <li><h4><a href="http://www.baidu.com">第二下载服务器</a></h4></li>
                </ul>
            </p>
            <hr />
            <h2 id="sidebar-title">📝 下载不成功怎么办？</h2>
            <p id="sidebar-text">
                下载不成功可以检查以下原因：
                <ul>
                    <li>文件命是否正确,是否认真阅读过本站歌曲使用指南</li>
                    <li>下载服务器是否正常连接，是否成功获取到数据</li>
                    <li>检查你的电脑是否正常启动，host IP 是否成功正确配置</li>
                    <li>检查文件后缀名是否正确，是否下载为mp3文件</li>
                </ul>
            </p>
            <hr />
            <h2 id="sidebar-title">💰 赞助我</h2>
            <p id="sidebar-text">
                如果你觉得本网站还可以，可以打赏一杯咖啡钱，打赏的钱仅用于维持服务器租用。
                <br>
                <img src="covers/zsm.jpg" style="width: 100px; height: 100px;" />
            </p>
            <hr />
            <h2 id="sidebar-title">😃 歌曲留言</h2>
            <p id="sidebar-text">
                如果发现想听的歌本站没有收录，可以前往以下页面留言
                <br>
                <h4> 留言：<a href="addmusic.php" target="_blank">前往留言</a></h4>
            </p>
            <hr />
            <h2 id="sidebar-title">📞 联系我</h2>
            <p id="sidebar-text">
                商务合作：yangsqwy@petalmail.com<br>
                技术支持：2687137354@qq.com<br>
                工作时间：周一至周五 9:00-18:00
                <br>
                <br>
            </p>
        </div>
    </div>

    <!-- 返回顶部按钮 -->
    <button id="back-to-top" title="返回顶部">
        <i class="fa-solid fa-angle-up" style="color: #ffffff;"></i>
    </button>

    <script>
        // 音乐播放相关元素
        const image = document.getElementById('cover'),
            title = document.getElementById('music-title'),
            artist = document.getElementById('music-artist'),
            currentTimeEl = document.getElementById('current-time'),
            durationEl = document.getElementById('duration'),
            progress = document.getElementById('progress'),
            playerProgress = document.getElementById('player-progress'),
            prevBtn = document.getElementById('prev'),
            nextBtn = document.getElementById('next'),
            playBtn = document.getElementById('play'),
            background = document.getElementById('bg-img'),
            musicList = document.getElementById('music-list'),
            playModeBtn = document.getElementById('play-mode'),
            searchInput = document.getElementById('search-input'),
            searchBtn = document.getElementById('search-btn'),
            clearBtn = document.getElementById('clear-btn');

        // 音频对象和歌曲列表
        const music = new Audio();
        let songs = [];
        let currentIndex = 0;
        let isPlaying = false;
        let playMode = 'normal';

        // ------------------- 播放次数统计相关 -------------------
        function updatePlayCount(songId) {
            fetch(`http://localhost/api.php?action=play&song_id=${songId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.warn('播放次数更新失败:', data.error);
                    }
                })
                .catch(error => {
                    console.error('播放次数请求失败:', error);
                });
        }

        // ------------------- 音乐控制函数 -------------------
        function togglePlay() {
            isPlaying ? pauseMusic() : playMusic();
        }

        function playMusic() {
            isPlaying = true;
            playBtn.classList.replace('fa-circle-play', 'fa-circle-pause');
            playBtn.setAttribute('title', 'Pause');
            music.play();
            
            const currentSong = songs[currentIndex];
            if (currentSong) updatePlayCount(currentSong.id);
        }

        function pauseMusic() {
            isPlaying = false;
            playBtn.classList.replace('fa-circle-pause', 'fa-circle-play');
            playBtn.setAttribute('title', 'Play');
            music.pause();
        }

        function loadMusic(song) {
            music.src = song.path;
            title.textContent = song.title;
            artist.textContent = song.artist;
            image.src = song.cover;
            background.src = song.cover;
            music.load();
            
            if (isPlaying) playMusic();
        }

        function changeMusic(direction) {
            if (playMode === 'random') {
                let newIndex;
                do {
                    newIndex = Math.floor(Math.random() * songs.length);
                } while (newIndex === currentIndex);
                currentIndex = newIndex;
            } else {
                currentIndex = (currentIndex + direction + songs.length) % songs.length;
            }
            loadMusic(songs[currentIndex]);
            playMusic();
        }

        function playMusicByIndex(index) {
            currentIndex = index;
            loadMusic(songs[currentIndex]);
            playMusic();
        }

        // ------------------- 进度条和时间处理 -------------------
        function updateProgressBar() {
            const { duration, currentTime } = music;
            const progressPercent = (currentTime / duration) * 100;
            progress.style.width = `${progressPercent}%`;

            const formatTime = (time) => String(Math.floor(time)).padStart(2, '0');
            durationEl.textContent = duration ? `${formatTime(duration / 60)}:${formatTime(duration % 60)}` : '0:00';
            currentTimeEl.textContent = `${formatTime(currentTime / 60)}:${formatTime(currentTime % 60)}`;
        }

        function setProgressBar(e) {
            const width = playerProgress.clientWidth;
            const clickX = e.offsetX;
            music.currentTime = (clickX / width) * music.duration;
        }

        // ------------------- 播放模式切换 -------------------
        playModeBtn.addEventListener('click', () => {
            if (playMode === 'normal') {
                playMode = 'random';
                playModeBtn.classList.remove('fa-list');
                playModeBtn.classList.add('fa-shuffle');
                playModeBtn.setAttribute('title', 'Random Play');
            } else if (playMode === 'random') {
                playMode = 'repeat';
                playModeBtn.classList.remove('fa-shuffle');
                playModeBtn.classList.add('fa-repeat');
                playModeBtn.setAttribute('title', 'Repeat Current Song');
            } else {
                playMode = 'normal';
                playModeBtn.classList.remove('fa-repeat');
                playModeBtn.classList.add('fa-list');
                playModeBtn.setAttribute('title', 'Normal Play');
            }
        });

        // ------------------- 数据获取和列表渲染 -------------------
        function renderMusicList(list) {
            musicList.innerHTML = '';
            list.forEach((song, index) => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <img src="${song.cover}" alt="${song.title}">
                    <div>
                        <h4>${song.title}</h4>
                        <p>${song.artist}</p>
                        <span class="play-count">播放: ${song.play_count}次</span>
                    </div>
                `;
                li.addEventListener('click', () => playMusicByIndex(index));
                musicList.appendChild(li);
            });
            if (list.length > 0) {
                songs = list;
                if (currentIndex >= list.length) currentIndex = 0;
                loadMusic(songs[currentIndex]);
            }
        }

        async function fetchAllSongs() {
            try {
                const response = await fetch('http://localhost/api.php');
                if (!response.ok) throw new Error('网络请求失败');
                const data = await response.json();
                renderMusicList(data);
            } catch (error) {
                console.error('获取音乐数据时出错:', error);
                alert('加载歌曲失败，请检查网络连接');
            }
        }

        async function searchSongs() {
            const keyword = searchInput.value.trim();
            toggleClearBtn(); // 搜索后更新清除按钮状态
            if (!keyword) {
                fetchAllSongs();
                return;
            }
            try {
                const response = await fetch(`http://localhost/api.php?action=search&keyword=${encodeURIComponent(keyword)}`);
                if (!response.ok) throw new Error('搜索失败');
                const data = await response.json();
                renderMusicList(data);
            } catch (error) {
                console.error('搜索时出错:', error);
                alert('搜索失败，请检查网络连接');
            }
        }

        // ------------------- 新增清除功能逻辑 -------------------
        function toggleClearBtn() {
            clearBtn.style.display = searchInput.value.trim() ? 'block' : 'none';
        }

        function clearSearch() {
            searchInput.value = '';
            toggleClearBtn();
            fetchAllSongs();
        }

        // ------------------- 返回顶部功能 -------------------
        const musicListContainer = document.querySelector('.music-list-container');
        const backToTopBtn = document.getElementById('back-to-top');

        function handleScroll() {
            if (musicListContainer.scrollTop > 50) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        }

        backToTopBtn.addEventListener('click', () => {
            musicListContainer.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        musicListContainer.addEventListener('scroll', handleScroll);

        // ------------------- 事件监听 -------------------
        playBtn.addEventListener('click', togglePlay);
        prevBtn.addEventListener('click', () => changeMusic(-1));
        nextBtn.addEventListener('click', () => changeMusic(1));
        music.addEventListener('ended', () => {
            if (playMode === 'repeat') {
                loadMusic(songs[currentIndex]);
            } else {
                changeMusic(1);
            }
        });
        music.addEventListener('timeupdate', updateProgressBar);
        playerProgress.addEventListener('click', setProgressBar);
        searchBtn.addEventListener('click', searchSongs);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchSongs();
        });
        searchInput.addEventListener('input', toggleClearBtn); // 输入时动态显示清除按钮
        clearBtn.addEventListener('click', clearSearch); // 点击清除按钮

        // 初始加载音乐数据
        fetchAllSongs();

        // ------------------- 侧边栏逻辑 -------------------
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const toggleIcon = toggleBtn.querySelector('i');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            if (sidebar.classList.contains('show')) {
                toggleIcon.classList.replace('fa-square-plus', 'fa-square-minus');
            } else {
                toggleIcon.classList.replace('fa-square-minus', 'fa-square-plus');
            }
        });
    </script>
</body>

</html>
