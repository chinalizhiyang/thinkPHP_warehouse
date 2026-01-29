<?php

// 导入SQL文件
function import_sql($file)
{
    // 读取SQL文件内容
    $sql = file_get_contents($file);
    
    // 连接数据库
    $config = require __DIR__ . '/application/config/database.php';
    
    $conn = new mysqli(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['hostport']
    );
    
    if ($conn->connect_error) {
        die('数据库连接失败: ' . $conn->connect_error);
    }
    
    $conn->set_charset($config['charset']);
    
    // 分割SQL语句
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                echo 'SQL错误: ' . $conn->error . '\n';
                echo 'SQL语句: ' . $statement . '\n';
            }
        }
    }
    
    $conn->close();
    
    echo 'SQL导入完成!\n';
}

// 执行导入
if ($argc > 1) {
    $file = $argv[1];
    
    if (file_exists($file)) {
        import_sql($file);
    } else {
        echo '文件不存在: ' . $file . '\n';
    }
} else {
    echo '请指定SQL文件路径\n';
}
