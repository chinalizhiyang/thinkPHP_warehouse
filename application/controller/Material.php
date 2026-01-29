<?php
namespace app\controller;

use app\model\Material as MaterialModel;

class Material
{
    // 物料列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取物料列表
        $materials = MaterialModel::getList();
        
        // 显示物料列表页面
        return view('material/index', ['materials' => $materials]);
    }
    
    // 添加物料
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'unit' => $_POST['unit'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'min_stock' => $_POST['min_stock'] ?? 0
            ];
            
            // 创建物料
            $material = MaterialModel::create($data);
            
            if ($material) {
                redirect('material', '添加成功');
            } else {
                redirect('material/add', '添加失败');
            }
        }
        
        // 获取分类列表
        $categories = MaterialModel::getCategoryList();
        
        // 显示添加物料页面
        return view('material/add', ['categories' => $categories]);
    }
    
    // 编辑物料
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取物料信息
        $material = MaterialModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'unit' => $_POST['unit'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'min_stock' => $_POST['min_stock'] ?? 0
            ];
            
            // 更新物料
            $result = MaterialModel::update($id, $data);
            
            if ($result) {
                redirect('material', '编辑成功');
            } else {
                redirect('material/edit/' . $id, '编辑失败');
            }
        }
        
        // 获取分类列表
        $categories = MaterialModel::getCategoryList();
        
        // 显示编辑物料页面
        return view('material/edit', ['material' => $material, 'categories' => $categories]);
    }
    
    // 删除物料
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除物料
        $result = MaterialModel::delete($id);
        
        if ($result) {
            redirect('material', '删除成功');
        } else {
            redirect('material', '删除失败');
        }
    }
    
    // 分类管理
    public function category()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'add':
                    $data = [
                        'name' => $_POST['name'] ?? '',
                        'parent_id' => $_POST['parent_id'] ?? 0
                    ];
                    $category = MaterialModel::createCategory($data);
                    if ($category) {
                        redirect('material/category', '添加分类成功');
                    } else {
                        redirect('material/category', '添加分类失败');
                    }
                    break;
                case 'edit':
                    $id = $_POST['id'] ?? 0;
                    $data = [
                        'name' => $_POST['name'] ?? '',
                        'parent_id' => $_POST['parent_id'] ?? 0
                    ];
                    $result = MaterialModel::updateCategory($id, $data);
                    if ($result) {
                        redirect('material/category', '编辑分类成功');
                    } else {
                        redirect('material/category', '编辑分类失败');
                    }
                    break;
                case 'delete':
                    $id = $_POST['id'] ?? 0;
                    $result = MaterialModel::deleteCategory($id);
                    if ($result) {
                        redirect('material/category', '删除分类成功');
                    } else {
                        redirect('material/category', '删除分类失败');
                    }
                    break;
            }
        }
        
        // 获取分类列表
        $categories = MaterialModel::getCategoryList();
        
        // 显示分类管理页面
        return view('material/category', ['categories' => $categories]);
    }
}
