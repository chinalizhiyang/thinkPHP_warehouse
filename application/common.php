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
    // 确保URL是绝对路径
    if (strpos($url, 'http') !== 0) {
        if (strpos($url, '/') !== 0) {
            // 相对路径，转换为绝对路径
            $url = '/' . $url;
        }
        $full_url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    } else {
        $full_url = $url;
    }
    
    if (empty($message)) {
        header('Location: ' . $full_url);
        exit;
    } else {
        echo "<script>alert('$message');setTimeout(function(){window.location.href='$full_url';},{$wait}000);</script>";
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
    
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    $user_role = $_SESSION['user']['role'];
    
    // 引入角色模型
    require_once __DIR__ . '/model/Role.php';
    
    $result = \app\model\Role::checkPermission($user_role, $permission);
    
    // 添加调试输出
    // error_log("检查权限: $permission, 用户角色: $user_role, 结果: " . ($result ? 'true' : 'false'));
    
    return $result;
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
    
    // 定义所有菜单（带下拉菜单结构）
    $all_menu = [
        [
            'name' => '物料管理',
            'url' => '#',
            'permission' => 'material_manage',
            'children' => [
                [
                    'name' => '物料列表',
                    'url' => '/material',
                    'permission' => 'material_manage'
                ],
                [
                    'name' => '添加物料',
                    'url' => '/material/add',
                    'permission' => 'material_manage'
                ]
            ]
        ],

        [
            'name' => '入库管理',
            'url' => '#',
            'permission' => 'inbound_manage',
            'children' => [
                [
                    'name' => '入库记录',
                    'url' => '/inbound',
                    'permission' => 'inbound'
                ],
                [
                    'name' => '创建入库单',
                    'url' => '/inbound/add',
                    'permission' => 'inbound_manage'
                ]
            ]
        ],
        [
            'name' => '出库管理',
            'url' => '#',
            'permission' => 'outbound_manage',
            'children' => [
                [
                    'name' => '出库记录',
                    'url' => '/outbound',
                    'permission' => 'outbound'
                ],
                [
                    'name' => '创建出库单',
                    'url' => '/outbound/add',
                    'permission' => 'outbound_manage'
                ]
            ]
        ],
        [
            'name' => '库存管理',
            'url' => '#',
            'permission' => 'inventory_manage',
            'children' => [
                [
                    'name' => '库存查询',
                    'url' => '/inventory',
                    'permission' => 'inventory_manage'
                ],
                [
                    'name' => '月度库存',
                    'url' => '/inventory/report',
                    'permission' => 'inventory_manage'
                ]
            ]
        ],
        [
            'name' => '历史查询',
            'url' => '#',
            'permission' => '',
            'children' => [
                [
                    'name' => '入库总记录',
                    'url' => '/inbound-history',
                    'permission' => 'inbound_history'
                ],
                [
                    'name' => '出库总记录',
                    'url' => '/outbound-history',
                    'permission' => 'outbound_history'
                ]
            ]
        ],
        [
            'name' => '系统管理',
            'url' => '#',
            'permission' => 'record_manage',
            'children' => [
                [
                    'name' => '操作记录',
                    'url' => '/record',
                    'permission' => 'record_manage'
                ],
                [
                    'name' => '系统日志',
                    'url' => '/record/system',
                    'permission' => 'record_manage'
                ],
                [
                    'name' => '数据备份',
                    'url' => '/record/backup',
                    'permission' => 'record_manage'
                ]
            ]
        ],
        [
            'name' => '用户管理',
            'url' => '#',
            'permission' => 'user_manage',
            'children' => [
                [
                    'name' => '用户列表',
                    'url' => '/user',
                    'permission' => 'user_manage'
                ],
                [
                    'name' => '添加用户',
                    'url' => '/user/add',
                    'permission' => 'user_manage'
                ],
                [
                    'name' => '角色权限管理',
                    'url' => '/user/role-permission',
                    'permission' => 'user_manage'
                ]
            ]
        ]
    ];
    
    // 根据权限过滤菜单
    $menu = [];
    foreach ($all_menu as $item) {
        // 先处理子菜单，不管父菜单的权限
        $has_children = false;
        $filtered_children = [];
        if (isset($item['children'])) {
            foreach ($item['children'] as $child) {
                // 直接检查子菜单权限
                $has_permission = empty($child['permission']) || check_permission($child['permission']);
                // 调试信息
                // error_log("菜单项: " . (isset($child['name']) ? $child['name'] : 'unknown') . ", 权限要求: " . (isset($child['permission']) ? $child['permission'] : 'none') . ", 有权限: " . (\$has_permission ? 'true' : 'false'));
                if ($has_permission) {
                    $filtered_children[] = $child;
                    $has_children = true;
                }
            }
        }
        
        // 如果有子菜单，或者父菜单有权限，就添加到菜单中
        $show_item = $has_children || empty($item['permission']) || check_permission($item['permission']);
        // error_log("菜单项: " . (isset($item['name']) ? $item['name'] : 'unknown') . ", 显示: " . (\$show_item ? 'true' : 'false'));
        if ($show_item) {
            if ($has_children) {
                $item['children'] = $filtered_children;
            }
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
    
    // 创建 mysqli 对象时禁用异常抛出
    mysqli_report(MYSQLI_REPORT_OFF);
    
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
    try {
        $conn = db_connect();
        
        if ($stmt = $conn->prepare($sql)) {
            if (!empty($params)) {
                $types = '';
                $bind_params = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_double($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $bind_params[] = $param;
                }
                
                // 使用可变参数绑定
                $stmt->bind_param($types, ...$bind_params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $result = false;
        }
        
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        return false;
    }
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
    try {
        $conn = db_connect();
        
        if ($stmt = $conn->prepare($sql)) {
            if (!empty($params)) {
                $types = '';
                $bind_params = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_double($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $bind_params[] = $param;
                }
                
                // 使用可变参数绑定，避免引用传递的问题
                $stmt->bind_param($types, ...$bind_params);
            }
            
            $result = $stmt->execute();
            $stmt->close();
        } else {
            $result = false;
        }
        
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        return false;
    }
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
