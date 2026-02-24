<?php
// 性能优化配置文件

// 开启OPcache（如果可用）
if (extension_loaded('Zend OPcache') && function_exists('opcache_get_status')) {
    // OPcache已启用，无需额外操作
}

// 设置内存限制
ini_set('memory_limit', '256M');

// 设置执行时间限制
ini_set('max_execution_time', 30);

// 开启输出缓冲
ob_start();

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 错误报告级别（生产环境应该关闭）
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// 如果是生产环境，可以开启这些优化
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);  // 默认为调试模式
}

if (!DEBUG_MODE) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// 数据库连接池配置（示例）
$db_config = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'warehouse_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_PERSISTENT => true,  // 持久连接
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
];

// 缓存配置
$cache_config = [
    'type' => 'file',  // 可以是 redis, memcached 等
    'path' => __DIR__ . '/runtime/cache/',
    'expire' => 3600  // 缓存过期时间（秒）
];

// 静态资源优化配置
$static_config = [
    'enable_compression' => true,
    'enable_cache' => true,
    'cache_time' => 86400,  // 24小时
    'minify_css' => true,
    'minify_js' => true
];

// 输出优化后的配置信息
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "性能优化配置:\n";
    echo "PHP版本: " . PHP_VERSION . "\n";
    echo "内存限制: " . ini_get('memory_limit') . "\n";
    echo "执行时间限制: " . ini_get('max_execution_time') . "秒\n";
    echo "OPcache状态: " . (function_exists('opcache_get_status') ? '已启用' : '未启用') . "\n";
    echo "输出缓冲: " . (ob_get_level() > 0 ? '已启用' : '未启用') . "\n";
    echo "</pre>";
}
?>