<?php
namespace app\controller;

use app\model\Role as RoleModel;

class Role
{
    // 角色列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/', '无权限访问');
        }
        
        // 获取角色列表
        $roles = RoleModel::getList();
        
        // 显示角色列表页面
        return view('role/index', ['roles' => $roles]);
    }
    
    // 添加角色
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            // 创建角色
            $role = RoleModel::create($data);
            
            if ($role) {
                redirect('role', '添加成功');
            } else {
                redirect('role/add', '添加失败');
            }
        }
        
        // 显示添加角色页面
        return view('role/add');
    }
    
    // 编辑角色
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/', '无权限访问');
        }
        
        // 获取角色信息
        $role = RoleModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            // 更新角色
            $result = RoleModel::update($id, $data);
            
            if ($result) {
                redirect('role', '编辑成功');
            } else {
                redirect('role/edit/' . $id, '编辑失败');
            }
        }
        
        // 显示编辑角色页面
        return view('role/edit', ['role' => $role]);
    }
    
    // 删除角色
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/', '无权限访问');
        }
        
        // 删除角色
        $result = RoleModel::delete($id);
        
        if ($result) {
            redirect('role', '删除成功');
        } else {
            redirect('role', '删除失败');
        }
    }
    
    // 分配权限
    public function assign($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/', '无权限访问');
        }
        
        // 获取角色信息
        $role = RoleModel::getById($id);
        
        // 获取权限列表
        $permissions = RoleModel::getPermissionList();
        
        // 获取角色当前的权限
        $role_permissions = RoleModel::getRolePermissions($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permission_ids = $_POST['permission_id'] ?? [];
            
            // 分配权限
            $result = RoleModel::assignPermissions($id, $permission_ids);
            
            if ($result) {
                redirect('role', '分配权限成功');
            } else {
                redirect('role/assign/' . $id, '分配权限失败');
            }
        }
        
        // 显示分配权限页面
        return view('role/assign', ['role' => $role, 'permissions' => $permissions, 'role_permissions' => $role_permissions]);
    }
}
