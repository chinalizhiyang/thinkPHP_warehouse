<?php
// 简单的路由器脚本，用于处理PHP内置服务器的路由

// 获取请求的URI
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// 不处理静态资源文件
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// 将所有其他请求转发到入口文件
require_once __DIR__ . '/../index.php';