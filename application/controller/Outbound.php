<?php
namespace app\controller;

use app\model\Outbound as OutboundModel;
use app\model\Material as MaterialModel;

class Outbound
{
    // 出库单列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('outbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库单列表
        $outbounds = OutboundModel::getList();
        
        // 显示出库单列表页面
        return view('outbound/index', ['outbounds' => $outbounds]);
    }
    
    // 添加出库单
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('outbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'customer' => $_POST['customer'] ?? '',
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 创建出库单
            $outbound = OutboundModel::create($data);
            
            if ($outbound) {
                // 处理出库明细
                $material_ids = $_POST['material_id'] ?? [];
                $quantities = $_POST['quantity'] ?? [];
                $prices = $_POST['price'] ?? [];
                
                foreach ($material_ids as $key => $material_id) {
                    if (!empty($material_id) && !empty($quantities[$key])) {
                        // 获取物料信息
                        $material = MaterialModel::getById($material_id);
                        
                        // 检查库存是否足够
                        if ($material['stock'] < $quantities[$key]) {
                            redirect('outbound/add', '物料 ' . $material['name'] . ' 库存不足');
                        }
                        
                        $detail_data = [
                            'outbound_id' => $outbound['id'],
                            'material_id' => $material_id,
                            'material_name' => $material['name'],
                            'material_code' => $material['code'],
                            'unit' => $material['unit'],
                            'price' => $prices[$key] ?? $material['price'],
                            'quantity' => $quantities[$key],
                            'amount' => ($prices[$key] ?? $material['price']) * $quantities[$key]
                        ];
                        
                        // 创建出库明细
                        OutboundModel::createDetail($detail_data);
                        
                        // 更新库存（减少）
                        OutboundModel::updateStock($material_id, -$quantities[$key]);
                    }
                }
                
                redirect('outbound', '添加成功');
            } else {
                redirect('outbound/add', '添加失败');
            }
        }
        
        // 获取物料列表
        $materials = MaterialModel::getList();
        
        // 显示添加出库单页面
        return view('outbound/add', ['materials' => $materials]);
    }
    
    // 编辑出库单
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('outbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库单信息
        $outbound = OutboundModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'customer' => $_POST['customer'] ?? '',
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 更新出库单
            $result = OutboundModel::update($id, $data);
            
            if ($result) {
                redirect('outbound', '编辑成功');
            } else {
                redirect('outbound/edit/' . $id, '编辑失败');
            }
        }
        
        // 显示编辑出库单页面
        return view('outbound/edit', ['outbound' => $outbound]);
    }
    
    // 删除出库单
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('outbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除出库单
        $result = OutboundModel::delete($id);
        
        if ($result) {
            redirect('outbound', '删除成功');
        } else {
            redirect('outbound', '删除失败');
        }
    }
}
