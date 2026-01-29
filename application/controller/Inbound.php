<?php
namespace app\controller;

use app\model\Inbound as InboundModel;
use app\model\Material as MaterialModel;

class Inbound
{
    // 入库单列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库单列表
        $inbounds = InboundModel::getList();
        
        // 显示入库单列表页面
        return view('inbound/index', ['inbounds' => $inbounds]);
    }
    
    // 添加入库单
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'supplier' => $_POST['supplier'] ?? '',
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 创建入库单
            $inbound = InboundModel::create($data);
            
            if ($inbound) {
                // 处理入库明细
                $material_ids = $_POST['material_id'] ?? [];
                $quantities = $_POST['quantity'] ?? [];
                $prices = $_POST['price'] ?? [];
                
                foreach ($material_ids as $key => $material_id) {
                    if (!empty($material_id) && !empty($quantities[$key])) {
                        // 获取物料信息
                        $material = MaterialModel::getById($material_id);
                        
                        $detail_data = [
                            'inbound_id' => $inbound['id'],
                            'material_id' => $material_id,
                            'material_name' => $material['name'],
                            'material_code' => $material['code'],
                            'unit' => $material['unit'],
                            'price' => $prices[$key] ?? $material['price'],
                            'quantity' => $quantities[$key],
                            'amount' => ($prices[$key] ?? $material['price']) * $quantities[$key]
                        ];
                        
                        // 创建入库明细
                        InboundModel::createDetail($detail_data);
                        
                        // 更新库存
                        InboundModel::updateStock($material_id, $quantities[$key]);
                    }
                }
                
                redirect('inbound', '添加成功');
            } else {
                redirect('inbound/add', '添加失败');
            }
        }
        
        // 获取物料列表
        $materials = MaterialModel::getList();
        
        // 显示添加入库单页面
        return view('inbound/add', ['materials' => $materials]);
    }
    
    // 编辑入库单
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库单信息
        $inbound = InboundModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'supplier' => $_POST['supplier'] ?? '',
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 更新入库单
            $result = InboundModel::update($id, $data);
            
            if ($result) {
                redirect('inbound', '编辑成功');
            } else {
                redirect('inbound/edit/' . $id, '编辑失败');
            }
        }
        
        // 显示编辑入库单页面
        return view('inbound/edit', ['inbound' => $inbound]);
    }
    
    // 删除入库单
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除入库单
        $result = InboundModel::delete($id);
        
        if ($result) {
            redirect('inbound', '删除成功');
        } else {
            redirect('inbound', '删除失败');
        }
    }
}
