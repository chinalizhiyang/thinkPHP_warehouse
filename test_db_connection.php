<?php
require 'application/common.php';

// 测试数据库连接
echo "=== 数据库连接测试 ===\n";
$config = require __DIR__ . '/application/config/database.php';
echo "数据库：{$config['database']}\n";
echo "主机：{$config['hostname']}\n";
echo "端口：{$config['hostport']}\n";
echo "用户：{$config['username']}\n\n";

// 尝试直接连接
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['hostport']
);

if ($conn->connect_error) {
    echo "连接失败：" . $conn->connect_error . "\n";
} else {
    echo "连接成功！\n\n";
    
    // 查询所有表
    echo "=== 数据库表 ===\n";
    $tables = db_get_all("SHOW TABLES");
    echo json_encode($tables, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // 查询 categories 表
    echo "=== categories 表结构 ===\n";
    $structure = db_get_all("DESCRIBE categories");
    echo json_encode($structure, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // 查询 categories 数据
    echo "=== categories 数据 ===\n";
    $categories = db_get_all("SELECT * FROM categories");
    echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // 查询 materials 表
    echo "=== materials 表结构 ===\n";
    $structure = db_get_all("DESCRIBE materials");
    echo json_encode($structure, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // 查询 materials 数据
    echo "=== materials 数据（前 5 条）===\n";
    $materials = db_get_all("SELECT * FROM materials LIMIT 5");
    echo json_encode($materials, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // 统计物料类别
    echo "=== 物料类别统计 ===\n";
    $stats = db_get_all("SELECT category, COUNT(*) as count FROM materials GROUP BY category");
    echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}

$conn->close();
