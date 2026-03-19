<?php
// 检查数据库连接和users表状态

// 包含公共函数文件
require_once __DIR__ . '/application/common.php';

try {
    // 连接数据库
    $conn = db_connect();
    echo "数据库连接成功！<br>";
    
    // 检查users表是否存在
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "users表存在！<br>";
        
        // 检查users表中的数据
        $result = $conn->query("SELECT * FROM users");
        $num_rows = $result->num_rows;
        echo "users表中有 $num_rows 条记录！<br>";
        
        // 显示前5条记录
        echo "前5条记录：<br>";
        echo "<table border='1'>";
        echo "<tr><th>id</th><th>username</th><th>password</th><th>email</th><th>phone</th><th>role</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 5) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['password']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['phone']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
    } else {
        echo "users表不存在！<br>";
    }
    
    // 关闭数据库连接
    $conn->close();
} catch (Exception $e) {
    echo "数据库连接失败：" . $e->getMessage() . "<br>";
}
?>