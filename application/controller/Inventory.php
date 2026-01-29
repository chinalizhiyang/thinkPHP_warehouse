<?php
namespace app\controller;

use app\model\Inventory as InventoryModel;
use app\model\Material as MaterialModel;

class Inventory
{
    // 库存列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取库存列表
        $inventories = InventoryModel::getList();
        
        // 获取库存预警列表
        $warnings = InventoryModel::getWarningList();
        
        // 显示库存列表页面
        return view('inventory/index', ['inventories' => $inventories, 'warnings' => $warnings]);
    }
    
    // 库存盘点
    public function check()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 创建盘点单
            $check = InventoryModel::createCheck($data);
            
            if ($check) {
                // 处理盘点明细
                $material_ids = $_POST['material_id'] ?? [];
                $system_stocks = $_POST['system_stock'] ?? [];
                $actual_stocks = $_POST['actual_stock'] ?? [];
                $prices = $_POST['price'] ?? [];
                
                foreach ($material_ids as $key => $material_id) {
                    if (!empty($material_id)) {
                        // 获取物料信息
                        $material = MaterialModel::getById($material_id);
                        
                        $difference = $actual_stocks[$key] - $system_stocks[$key];
                        $amount = $difference * $prices[$key];
                        
                        $detail_data = [
                            'check_id' => $check['id'],
                            'material_id' => $material_id,
                            'material_name' => $material['name'],
                            'material_code' => $material['code'],
                            'unit' => $material['unit'],
                            'system_stock' => $system_stocks[$key],
                            'actual_stock' => $actual_stocks[$key],
                            'difference' => $difference,
                            'price' => $prices[$key],
                            'amount' => $amount
                        ];
                        
                        // 创建盘点明细
                        InventoryModel::createCheckDetail($detail_data);
                        
                        // 更新库存
                        if ($difference != 0) {
                            InventoryModel::updateStock($material_id, $actual_stocks[$key]);
                        }
                    }
                }
                
                redirect('inventory', '盘点成功');
            } else {
                redirect('inventory/check', '盘点失败');
            }
        }
        
        // 获取物料列表
        $materials = MaterialModel::getList();
        
        // 显示库存盘点页面
        return view('inventory/check', ['materials' => $materials]);
    }
    
    // 库存报表
    public function report()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        $params = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d')
        ];
        
        // 生成库存报表
        $report = InventoryModel::generateReport($params);
        
        // 显示库存报表页面
        return view('inventory/report', ['report' => $report]);
    }
}
