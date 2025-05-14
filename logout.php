<?php
session_start();
// 销毁会话数据
$_SESSION = array();
// 清除会话Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
// 销毁会话
session_destroy();
// 跳转登录页
header("Location: login.php");
exit;
    