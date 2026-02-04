<?php
namespace app\controller;

use app\model\User as UserModel;

class User
{
    // 登录页面
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // 获取用户信息
            $user = UserModel::getByUsername($username);
            
            if ($user && UserModel::verifyPassword($user, $password)) {
                // 保存用户信息到session
                $user['login_time'] = date('Y-m-d H:i:s');
                $_SESSION['user'] = $user;
                redirect('/', '登录成功');
            } else {
                redirect('login', '用户名或密码错误');
            }
        }
        
        // 直接渲染登录页面，不使用主布局（特殊情况）
        ob_start();
        ?>        
        <!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>登录 - 仓储管理系统</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        </head>
        <body>
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-center">登录</h3>
                            </div>
                            <div class="card-body">
                                <form action="/login" method="post">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">用户名</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">密码</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">登录</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    // 注销
    public function logout()
    {
        // 清空session
        unset($_SESSION['user']);
        redirect('login', '注销成功');
    }
    
    // 个人资料
    public function profile()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        $user_id = $_SESSION['user']['id'];
        $user = UserModel::getById($user_id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            // 更新用户信息
            $result = UserModel::update($user_id, $data);
            
            if ($result) {
                redirect('user/profile', '更新成功');
            } else {
                redirect('user/profile', '更新失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染个人资料内容
        $content = $this->renderProfileContent($user);
        
        // 显示个人资料页面
        return view('layout/main', [
            'title' => '个人资料',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'User'
        ]);
    }
    
    // 渲染个人资料内容
    private function renderProfileContent($user)
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-user"></i> 个人资料</h3>
            </div>
            <div class="card-body">
                <form action="/user/profile" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">用户名</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">邮箱</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">电话</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">角色</label>
                        <input type="text" class="form-control" id="role" name="role" value="<?php echo $user['role']; ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 用户列表
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
        
        // 获取用户列表
        $users = UserModel::getList();
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染用户列表内容
        $content = $this->renderUserListContent($users);
        
        // 显示用户列表页面
        return view('layout/main', [
            'title' => '用户列表',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'User'
        ]);
    }
    
    // 渲染用户列表内容
    private function renderUserListContent($users)
    {
        // 获取所有角色，用于显示中文名称
        $roles = \app\model\Role::getList();
        $role_map = [];
        foreach ($roles as $role) {
            // 由于auth_group表没有description字段，我们只使用name
            $role_map[$role['name']] = $role['name'];
        }
        
        // 默认角色映射
        $default_role_map = [
            'admin' => '管理员',
            'user' => '普通用户',
            'warehouse' => '仓库管理员',
            'purchaser' => '采购员',
            'manager' => '经理'
        ];
        
        // 合并角色映射（默认映射优先）
        $role_map = array_merge($role_map, $default_role_map);
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-users"></i> 用户列表</h3>
                <a href="/user/add" class="btn btn-primary float-end"><i class="fa fa-plus"></i> 添加用户</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>电话</th>
                            <th>角色</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['phone']; ?></td>
                            <td><?php echo isset($role_map[$user['role']]) ? $role_map[$user['role']] : $user['role']; ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <a href="/user/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> 编辑</a>
                                <a href="/user/delete/<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗？');"><i class="fa fa-trash"></i> 删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 添加用户
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
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];
            
            // 创建用户
            $user = UserModel::create($data);
            
            if ($user) {
                redirect('/user', '添加成功');
            } else {
                redirect('/user/add', '添加失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染添加用户内容
        $content = $this->renderAddUserContent();
        
        // 显示添加用户页面
        return view('layout/main', [
            'title' => '添加用户',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'User'
        ]);
    }
    
    // 渲染添加用户内容
    private function renderAddUserContent()
    {
        // 获取所有角色
        $roles = \app\model\Role::getList();
        
        // 角色中文名称映射
        $role_name_map = [
            'admin' => '管理员',
            'user' => '普通用户',
            'warehouse' => '仓库管理员',
            'purchaser' => '采购员',
            'manager' => '经理'
        ];
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-user-plus"></i> 添加用户</h3>
            </div>
            <div class="card-body">
                <form action="/user/add" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">用户名</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">密码</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">邮箱</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">电话</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">角色</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="" disabled selected>请选择角色</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['name']; ?>"><?php echo $role['description'] ?? ($role_name_map[$role['name']] ?? $role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">添加用户</button>
                    <a href="/user/index" class="btn btn-secondary">取消</a>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 编辑用户
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
        
        // 获取用户信息
        $user = UserModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'role' => $_POST['role'] ?? 'user'
            ];
            
            // 如果输入了密码，则更新密码
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash_custom($_POST['password']);
            }
            
            // 更新用户信息
            $result = UserModel::update($id, $data);
            
            if ($result) {
                redirect('/user', '编辑成功');
            } else {
                redirect('/user/edit/' . $id, '编辑失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染编辑用户内容
        $content = $this->renderEditUserContent($user);
        
        // 显示编辑用户页面
        return view('layout/main', [
            'title' => '编辑用户',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'User'
        ]);
    }
    
    // 渲染编辑用户内容
    private function renderEditUserContent($user)
    {
        // 获取所有角色
        $roles = \app\model\Role::getList();
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-user-edit"></i> 编辑用户</h3>
            </div>
            <div class="card-body">
                <form action="/user/edit/<?php echo $user['id']; ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">用户名</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">密码 (留空表示不修改)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">邮箱</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">电话</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">角色</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="" disabled>请选择角色</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['name']; ?>" <?php echo $user['role'] === $role['name'] ? 'selected' : ''; ?>><?php echo $role['description'] ?? $role['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/user/index" class="btn btn-secondary">取消</a>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 删除用户
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
        
        // 删除用户
        $result = UserModel::delete($id);
        
        if ($result) {
            redirect('/user', '删除成功');
        } else {
            redirect('/user', '删除失败');
        }
    }
    
    // 角色权限管理
    public function rolePermission()
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
        $roles = \app\model\Role::getList();
        
        // 获取权限列表
        $permissions = \app\model\Role::getPermissionList();
        
        // 如果选择了角色，获取该角色的权限
        $selected_role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : null;
        $role_permissions = [];
        $selected_role = null;
        
        if ($selected_role_id) {
            $role_permissions = \app\model\Role::getRolePermissions($selected_role_id);
            $selected_role = \app\model\Role::getById($selected_role_id);
        }
        
        // 处理权限分配
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_role_id) {
            $permission_ids = $_POST['permission_id'] ?? [];
            $result = \app\model\Role::assignPermissions($selected_role_id, $permission_ids);
            
            if ($result) {
                redirect('user/role-permission?role_id=' . $selected_role_id, '权限分配成功');
            } else {
                redirect('user/role-permission?role_id=' . $selected_role_id, '权限分配失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染角色权限管理内容
        $content = $this->renderRolePermissionContent($roles, $permissions, $selected_role_id, $role_permissions, $selected_role);
        
        // 显示角色权限管理页面
        return view('layout/main', [
            'title' => '角色权限管理',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'User'
        ]);
    }
    
    // 渲染角色权限管理内容
    private function renderRolePermissionContent($roles, $permissions, $selected_role_id, $role_permissions, $selected_role)
    {
        // 角色中文名称映射
        $role_name_map = [
            'admin' => '管理员',
            'user' => '普通用户',
            'warehouse' => '仓库管理员',
            'purchaser' => '采购员',
            'manager' => '经理'
        ];
        
        ob_start();
        ?>
        <style>
            .permission-module {
                background-color: #f8f9fa;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 15px;
            }
            .permission-module h5 {
                color: #495057;
                font-weight: 600;
                font-size: 16px;
                margin-bottom: 12px;
                padding-bottom: 8px;
            }
            .permission-module .form-check-label {
                font-size: 13px;
                padding-top: 2px;
            }
            .form-check {
                margin-bottom: 6px;
            }
        </style>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-shield-alt"></i> 角色权限管理</h3>
            </div>
            <div class="card-body">
                <!-- 角色选择 -->
                <div class="mb-4">
                    <form action="/user/role-permission" method="get" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="role_id" class="mr-2">选择角色：</label>
                            <select class="form-control" id="role_id" name="role_id" onchange="this.form.submit()">
                                <option value="">请选择角色</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" <?php echo $selected_role_id == $role['id'] ? 'selected' : ''; ?>><?php echo $role['description'] ?? ($role_name_map[$role['name']] ?? $role['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                
                <?php if ($selected_role): ?>
                <!-- 权限分配表单 -->
                <form action="/user/role-permission?role_id=<?php echo $selected_role_id; ?>" method="post">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><?php echo $selected_role['description'] ?? ($role_name_map[$selected_role['name']] ?? $selected_role['name']); ?> - 权限分配</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            // 按模块分组权限
                            $permissions_by_module = [];
                            foreach ($permissions as $permission) {
                                $permissions_by_module[$permission['module']][] = $permission;
                            }
                            ksort($permissions_by_module);
                            ?>
                            
                            <?php foreach ($permissions_by_module as $module => $module_permissions): ?>
                            <div class="permission-module mb-4">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <?php 
                                    $module_names = [
                                        'user' => '用户管理',
                                        'material' => '物料管理',
                                        'inbound' => '入库管理',
                                        'outbound' => '出库管理',
                                        'inventory' => '库存管理',
                                        'record' => '记录管理',
                                        'role' => '角色管理',
                                        'inbound_history' => '入库历史',
                                        'outbound_history' => '出库历史'
                                    ];
                                    echo $module_names[$module] ?? $module;
                                    ?>
                                </h5>
                                <div class="row">
                                    <?php foreach ($module_permissions as $permission): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="permission_<?php echo $permission['id']; ?>" name="permission_id[]" value="<?php echo $permission['id']; ?>" <?php echo in_array($permission['id'], $role_permissions) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="permission_<?php echo $permission['id']; ?>">
                                                <?php echo $permission['description']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">保存权限</button>
                            <a href="/user/role-permission" class="btn btn-secondary">取消</a>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info" role="alert">
                    请选择一个角色来分配权限
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
