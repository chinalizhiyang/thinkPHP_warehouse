<?php
namespace app\model;

class Role
{
    // 角色表结构
    // id, name, description, created_at, updated_at
    
    // 权限表结构
    // id, name, description, module, created_at, updated_at
    
    // 角色权限关联表结构
    // id, role_id, permission_id, created_at
    
    // 获取角色列表
    public static function getList()
    {
        // 使用数据库查询
        $sql = "SELECT * FROM auth_group";
        $roles = db_get_all($sql);
        
        return $roles;
    }
    
    // 根据ID获取角色
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM auth_group WHERE id = ?";
        $role = db_get_row($sql, [$id]);
        
        if ($role) {
            return $role;
        }
        
        return false;
    }
    
    // 创建角色
    public static function create($data)
    {
        // 使用数据库插入
        $sql = "INSERT INTO auth_group (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $result = db_exec($sql, [$data['name'], $data['description'] ?? '']);
        
        if ($result) {
            $conn = db_connect();
            $id = $conn->insert_id;
            $conn->close();
            return self::getById($id);
        }
        
        return false;
    }
    
    // 更新角色
    public static function update($id, $data)
    {
        // 使用数据库更新
        $sql = "UPDATE auth_group SET name = ?, description = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [$data['name'], $data['description'] ?? '', $id]);
        
        return $result;
    }
    
    // 删除角色
    public static function delete($id)
    {
        // 先删除角色权限关联
        db_exec("DELETE FROM auth_group_permissions WHERE role_id = ?", [$id]);
        
        // 再删除角色
        $sql = "DELETE FROM auth_group WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
    
    // 获取权限列表
    public static function getPermissionList()
    {
        // 使用数据库查询
        $sql = "SELECT * FROM auth_permissions";
        $permissions = db_get_all($sql);
        
        // 如果数据库中没有权限记录，返回默认权限列表
        if (empty($permissions)) {
            return [
                [
                    'id' => 1,
                    'name' => 'user_manage',
                    'description' => '用户管理',
                    'module' => 'user'
                ],
                [
                    'id' => 2,
                    'name' => 'material_manage',
                    'description' => '物料管理',
                    'module' => 'material'
                ],
                [
                    'id' => 3,
                    'name' => 'material_import_csv',
                    'description' => '物料导入CSV',
                    'module' => 'material'
                ],
                [
                    'id' => 4,
                    'name' => 'inbound_manage',
                    'description' => '入库管理',
                    'module' => 'inbound'
                ],
                [
                    'id' => 5,
                    'name' => 'outbound_manage',
                    'description' => '出库管理',
                    'module' => 'outbound'
                ],
                [
                    'id' => 6,
                    'name' => 'inventory_manage',
                    'description' => '库存管理',
                    'module' => 'inventory'
                ],
                [
                    'id' => 7,
                    'name' => 'inventory_export_csv',
                    'description' => '库存导出CSV',
                    'module' => 'inventory'
                ],
                [
                    'id' => 8,
                    'name' => 'record_manage',
                    'description' => '记录管理',
                    'module' => 'record'
                ],
                [
                    'id' => 9,
                    'name' => 'inbound_history',
                    'description' => '入库历史',
                    'module' => 'inbound_history'
                ],
                [
                    'id' => 10,
                    'name' => 'inbound_history_import_csv',
                    'description' => '入库历史导入CSV',
                    'module' => 'inbound_history'
                ],
                [
                    'id' => 11,
                    'name' => 'inbound_history_export_csv',
                    'description' => '入库历史导出CSV',
                    'module' => 'inbound_history'
                ],
                [
                    'id' => 12,
                    'name' => 'outbound_history',
                    'description' => '出库历史',
                    'module' => 'outbound_history'
                ],
                [
                    'id' => 13,
                    'name' => 'outbound_history_import_csv',
                    'description' => '出库历史导入CSV',
                    'module' => 'outbound_history'
                ],
                [
                    'id' => 14,
                    'name' => 'outbound_history_export_csv',
                    'description' => '出库历史导出CSV',
                    'module' => 'outbound_history'
                ],
                [
                    'id' => 15,
                    'name' => 'role_manage',
                    'description' => '角色管理',
                    'module' => 'role'
                ]
            ];
        }
        
        return $permissions;
    }
    
    // 获取角色的权限
    public static function getRolePermissions($role_id)
    {
        // 使用数据库查询
        $sql = "SELECT permission_id FROM auth_group_permissions WHERE group_id = ?";
        $rows = db_get_all($sql, [$role_id]);
        
        $permission_ids = [];
        foreach ($rows as $row) {
            $permission_ids[] = $row['permission_id'];
        }
        
        return $permission_ids;
    }
    
    // 分配权限给角色
    public static function assignPermissions($role_id, $permission_ids)
    {
        // 先删除旧的权限关联
        db_exec("DELETE FROM auth_group_permissions WHERE group_id = ?", [$role_id]);
        
        // 插入新的权限关联
        foreach ($permission_ids as $permission_id) {
            $sql = "INSERT INTO auth_group_permissions (group_id, permission_id) VALUES (?, ?)";
            db_exec($sql, [$role_id, $permission_id]);
        }
        
        return true;
    }
    
    // 检查用户是否有某个权限
    public static function checkPermission($user_role, $permission_name)
    {
        try {
            // 先获取角色ID
            $sql = "SELECT id FROM auth_group WHERE name = ?";
            $role = db_get_row($sql, [$user_role]);
            
            // 如果数据库中没有角色记录，返回false
            if (!$role) {
                return false;
            }
            
            // 获取权限ID
            $sql = "SELECT id FROM auth_permissions WHERE name = ?";
            $permission = db_get_row($sql, [$permission_name]);
            
            if (!$permission) {
                return false;
            }
            
            // 检查角色是否有该权限（注意：表中使用的是group_id而非role_id）
            $sql = "SELECT COUNT(*) as count FROM auth_group_permissions WHERE group_id = ? AND permission_id = ?";
            $result = db_get_row($sql, [$role['id'], $permission['id']]);
            
            if (!$result) {
                return false;
            }
            
            // 如果数据库中有权限记录，使用数据库中的权限分配
            return $result['count'] > 0;
        } catch (\Exception $e) {
            // 如果发生数据库错误（例如表不存在），返回false
            return false;
        }
    }
}
