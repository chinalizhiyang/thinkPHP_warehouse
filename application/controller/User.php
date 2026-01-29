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
            
            if ($user && password_verify_custom($password, $user['password'])) {
                // 保存用户信息到session
                $_SESSION['user'] = $user;
                redirect('/', '登录成功');
            } else {
                redirect('login', '用户名或密码错误');
            }
        }
        
        // 显示登录页面
        return view('user/login');
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
        
        // 显示个人资料页面
        return view('user/profile', ['user' => $user]);
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
        
        // 显示用户列表页面
        return view('user/index', ['users' => $users]);
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
                'role' => $_POST['role'] ?? 'user'
            ];
            
            // 创建用户
            $user = UserModel::create($data);
            
            if ($user) {
                redirect('user/index', '添加成功');
            } else {
                redirect('user/add', '添加失败');
            }
        }
        
        // 显示添加用户页面
        return view('user/add');
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
                redirect('user/index', '编辑成功');
            } else {
                redirect('user/edit/' . $id, '编辑失败');
            }
        }
        
        // 显示编辑用户页面
        return view('user/edit', ['user' => $user]);
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
            redirect('user/index', '删除成功');
        } else {
            redirect('user/index', '删除失败');
        }
    }
}
