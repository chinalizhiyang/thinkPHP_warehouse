<?php
namespace app\model;

class User
{
    // 用户表结构
    // id, username, password, email, phone, role, created_at, updated_at
    
    // 获取用户信息
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM users WHERE id = ?";
        $user = db_get_row($sql, [$id]);
        
        if ($user) {
            return $user;
        }
        
        return false;
    }
    
    // 根据用户名获取用户
    public static function getByUsername($username)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM users WHERE username = ?";
        $user = db_get_row($sql, [$username]);
        
        if ($user) {
            return $user;
        }
        
        return null;
    }
    
    // 验证密码
    public static function verifyPassword($user, $password)
    {
        // 检查密码是否匹配
        // 这里处理两种情况：1. 密码是哈希值 2. 密码是明文（用于初始测试）
        // bcrypt哈希长度通常是60个字符，所以使用 >= 60 来判断
        if (strlen($user['password']) >= 60) {
            // 可能是哈希值
            return password_verify($password, $user['password']);
        } else {
            // 可能是明文密码
            return $user['password'] === $password;
        }
    }
    
    // 获取用户列表
    public static function getList($where = [])
    {
        // 使用数据库查询
        $sql = "SELECT * FROM users";
        $users = db_get_all($sql);
        
        return $users;
    }
    
    // 创建用户
    public static function create($data)
    {
        // 使用数据库插入
        $sql = "INSERT INTO users (username, password, email, phone, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $result = db_exec($sql, [$data['username'], password_hash_custom($data['password']), $data['email'], $data['phone'], $data['role']]);
        
        if ($result) {
            $conn = db_connect();
            $id = $conn->insert_id;
            $conn->close();
            return self::getById($id);
        }
        
        return false;
    }
    
    // 更新用户信息
    public static function update($id, $data)
    {
        // 使用数据库更新
        if (isset($data['password'])) {
            // 如果提供了密码，包含密码字段
            $sql = "UPDATE users SET username = ?, password = ?, email = ?, phone = ?, role = ?, updated_at = NOW() WHERE id = ?";
            $result = db_exec($sql, [$data['username'], $data['password'], $data['email'], $data['phone'], $data['role'] ?? 'user', $id]);
        } else {
            // 如果没有提供密码，不更新密码字段
            $sql = "UPDATE users SET username = ?, email = ?, phone = ?, role = ?, updated_at = NOW() WHERE id = ?";
            $result = db_exec($sql, [$data['username'], $data['email'], $data['phone'], $data['role'] ?? 'user', $id]);
        }
        
        return $result;
    }
    
    // 删除用户
    public static function delete($id)
    {
        // 使用数据库删除
        $sql = "DELETE FROM users WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
}
