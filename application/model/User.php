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
        $result = db_exec($sql, [$data['username'], password_hash_custom($data['password']), $data['email'], $data['phone'], $data['role'] ?? 'user']);
        
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
        $sql = "UPDATE users SET username = ?, email = ?, phone = ?, role = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [$data['username'], $data['email'], $data['phone'], $data['role'] ?? 'user', $id]);
        
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
