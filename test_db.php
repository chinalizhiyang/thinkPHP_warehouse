<?php

// 加载配置和函数
require __DIR__ . '/application/config/database.php';
require __DIR__ . '/application/common.php';

// 测试数据库连接
function test_db_connection() {
    echo "测试数据库连接...\n";
    
    $conn = db_connect();
    
    if ($conn) {
        echo "数据库连接成功!\n";
        $conn->close();
        return true;
    } else {
        echo "数据库连接失败!\n";
        return false;
    }
}

// 执行SQL文件
function execute_sql_file($file) {
    echo "执行SQL文件: $file\n";
    
    // 读取SQL文件内容
    $sql = file_get_contents($file);
    
    if (!$sql) {
        echo "无法读取SQL文件!\n";
        return false;
    }
    
    // 分割SQL语句
    $statements = explode(';', $sql);
    
    $conn = db_connect();
    if (!$conn) {
        return false;
    }
    
    $success = true;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                echo "SQL错误: " . $conn->error . "\n";
                echo "SQL语句: " . $statement . "\n";
                $success = false;
            }
        }
    }
    
    $conn->close();
    
    if ($success) {
        echo "SQL文件执行成功!\n";
    } else {
        echo "SQL文件执行失败!\n";
    }
    
    return $success;
}

// 测试模型功能
function test_models() {
    echo "测试模型功能...\n";
    
    // 测试Role模型
    echo "\n测试Role模型...\n";
    $roles = app\model\Role::getList();
    echo "角色数量: " . count($roles) . "\n";
    
    // 测试Material模型
    echo "\n测试Material模型...\n";
    $materials = app\model\Material::getList();
    echo "物料数量: " . count($materials) . "\n";
    
    // 测试User模型
    echo "\n测试User模型...\n";
    $users = app\model\User::getList();
    echo "用户数量: " . count($users) . "\n";
    
    echo "\n模型测试完成!\n";
}

// 主测试函数
function main() {
    echo "开始数据库测试...\n\n";
    
    // 测试数据库连接
    if (test_db_connection()) {
        // 执行SQL文件
        $sql_file = __DIR__ . '/backup_20260118_180102.sql';
        if (file_exists($sql_file)) {
            if (execute_sql_file($sql_file)) {
                // 测试模型
                test_models();
            }
        } else {
            echo "SQL文件不存在: $sql_file\n";
        }
    }
    
    echo "\n数据库测试完成!\n";
}

// 运行测试
main();
