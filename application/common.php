<?php
// 公共函数文件

/**
 * 密码加密
 * @param string $password 原始密码
 * @return string 加密后的密码
 */
function password_hash_custom($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 密码验证
 * @param string $password 原始密码
 * @param string $hash 加密后的密码
 * @return bool 验证结果
 */
function password_verify_custom($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * 生成随机字符串
 * @param int $length 字符串长度
 * @return string 随机字符串
 */
function generate_random_string($length = 32)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}

/**
 * 跳转函数
 * @param string $url 跳转地址
 * @param string $message 提示信息
 * @param int $wait 等待时间
 */
function redirect($url, $message = '', $wait = 3)
{
    if (empty($message)) {
        header('Location: ' . $url);
        exit;
    } else {
        echo "<script>alert('$message');setTimeout(function(){window.location.href='$url';},{$wait}000);</script>";
        exit;
    }
}

/**
 * 检查用户权限
 * @param string $permission 权限名称
 * @return bool 是否有权限
 */
function check_permission($permission)
{
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $user_role = $_SESSION['user']['role'];
    
    // 引入角色模型
    require_once __DIR__ . '/model/Role.php';
    
    return \app\model\Role::checkPermission($user_role, $permission);
}

/**
 * 获取用户导航菜单
 * @return array 导航菜单
 */
function get_nav_menu()
{
    if (!isset($_SESSION['user'])) {
        return [];
    }
    
    $user_role = $_SESSION['user']['role'];
    
    // 定义所有菜单
    $all_menu = [
        [
            'name' => '首页',
            'url' => '/',
            'permission' => ''
        ],
        [
            'name' => '用户管理',
            'url' => '/user',
            'permission' => 'user_manage'
        ],
        [
            'name' => '物料管理',
            'url' => '/material',
            'permission' => 'material_manage'
        ],
        [
            'name' => '入库管理',
            'url' => '/inbound',
            'permission' => 'inbound_manage'
        ],
        [
            'name' => '出库管理',
            'url' => '/outbound',
            'permission' => 'outbound_manage'
        ],
        [
            'name' => '库存管理',
            'url' => '/inventory',
            'permission' => 'inventory_manage'
        ],
        [
            'name' => '记录管理',
            'url' => '/record',
            'permission' => 'record_manage'
        ],
        [
            'name' => '入库历史',
            'url' => '/inbound-history',
            'permission' => 'inbound_history'
        ],
        [
            'name' => '出库历史',
            'url' => '/outbound-history',
            'permission' => 'outbound_history'
        ],
        [
            'name' => '角色管理',
            'url' => '/role',
            'permission' => 'role_manage'
        ],
        [
            'name' => '个人资料',
            'url' => '/user/profile',
            'permission' => ''
        ],
        [
            'name' => '注销',
            'url' => '/logout',
            'permission' => ''
        ]
    ];
    
    // 根据权限过滤菜单
    $menu = [];
    foreach ($all_menu as $item) {
        if (empty($item['permission']) || check_permission($item['permission'])) {
            $menu[] = $item;
        }
    }
    
    return $menu;
}

/**
 * 数据库连接函数
 * @return mysqli 数据库连接对象
 */
function db_connect()
{
    $config = require __DIR__ . '/config/database.php';
    
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
    
    return $conn;
}

/**
 * 执行SQL查询
 * @param string $sql SQL语句
 * @param array $params 参数
 * @return mysqli_result|bool 查询结果
 */
function db_query($sql, $params = [])
{
    $conn = db_connect();
    
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = &$param;
            }
            
            array_unshift($values, $types);
            call_user_func_array([$stmt, 'bind_param'], $values);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = false;
    }
    
    $conn->close();
    
    return $result;
}

/**
 * 获取单行数据
 * @param string $sql SQL语句
 * @param array $params 参数
 * @return array|bool 数据
 */
function db_get_row($sql, $params = [])
{
    $result = db_query($sql, $params);
    
    if ($result) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * 获取多行数据
 * @param string $sql SQL语句
 * @param array $params 参数
 * @return array 数据
 */
function db_get_all($sql, $params = [])
{
    $result = db_query($sql, $params);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return [];
}

/**
 * 执行SQL语句（插入、更新、删除）
 * @param string $sql SQL语句
 * @param array $params 参数
 * @return bool 是否成功
 */
function db_exec($sql, $params = [])
{
    $conn = db_connect();
    
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = &$param;
            }
            
            array_unshift($values, $types);
            call_user_func_array([$stmt, 'bind_param'], $values);
        }
        
        $result = $stmt->execute();
        $stmt->close();
    } else {
        $result = false;
    }
    
    $conn->close();
    
    return $result;
}

/**
 * 获取插入的ID
 * @param mysqli $conn 数据库连接对象
 * @return int 插入的ID
 */
function db_insert_id($conn)
{
    return $conn->insert_id;
}
