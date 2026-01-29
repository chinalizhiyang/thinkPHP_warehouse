<?php
namespace app\controller;

class Index
{
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 显示首页
        return view('index/index', ['menu' => $menu]);
    }
}
