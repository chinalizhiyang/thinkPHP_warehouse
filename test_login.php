<?php
// 测试登录功能

// 包含公共函数文件
require_once __DIR__ . '/application/common.php';
// 包含UserModel类
require_once __DIR__ . '/application/model/User.php';

// 测试用户名和密码
$username = 'admin';
$password = 'admin';

echo "测试登录功能：<br>";
echo "用户名：$username<br>";
echo "密码：$password<br>";

// 测试getByUsername方法
echo "<br>测试getByUsername方法：<br>";
$user = \app\model\User::getByUsername($username);
if ($user) {
    echo "获取用户成功！<br>";
    echo "用户信息：<br>";
    echo "id: {$user['id']}<br>";
    echo "username: {$user['username']}<br>";
    echo "password: {$user['password']}<br>";
    echo "email: {$user['email']}<br>";
    echo "phone: {$user['phone']}<br>";
    echo "role: {$user['role']}<br>";
    
    // 测试verifyPassword方法
    echo "<br>测试verifyPassword方法：<br>";
    $result = \app\model\User::verifyPassword($user, $password);
    if ($result) {
        echo "密码验证成功！<br>";
    } else {
        echo "密码验证失败！<br>";
    }
} else {
    echo "获取用户失败！<br>";
}
?>