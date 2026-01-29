<?php
namespace app\model;

class Inventory
{
    // 获取库存列表
    public static function getList($where = [])
    {
        // 实际项目中使用数据库查询
        return [
            [
                'id' => 1,
                'material_id' => 1,
                'material_name' => '物料1',
                'material_code' => 'MAT001',
                'category_id' => 1,
                'category_name' => '电子元件',
                'unit' => '个',
                'price' => 10.00,
                'stock' => 100,
                'min_stock' => 10,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'material_id' => 2,
                'material_name' => '物料2',
                'material_code' => 'MAT002',
                'category_id' => 2,
                'category_name' => '机械零件',
                'unit' => '件',
                'price' => 20.00,
                'stock' => 50,
                'min_stock' => 5,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'material_id' => 3,
                'material_name' => '物料3',
                'material_code' => 'MAT003',
                'category_id' => 1,
                'category_name' => '电子元件',
                'unit' => '个',
                'price' => 15.00,
                'stock' => 5,
                'min_stock' => 10,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // 获取库存预警列表
    public static function getWarningList()
    {
        // 实际项目中使用数据库查询
        return [
            [
                'id' => 3,
                'material_id' => 3,
                'material_name' => '物料3',
                'material_code' => 'MAT003',
                'category_id' => 1,
                'category_name' => '电子元件',
                'unit' => '个',
                'price' => 15.00,
                'stock' => 5,
                'min_stock' => 10,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // 创建库存盘点单
    public static function createCheck($data)
    {
        // 实际项目中使用数据库插入
        return [
            'id' => rand(1000, 9999),
            'check_no' => 'CHECK' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'checker' => $_SESSION['user']['username'] ?? 'admin',
            'total_amount' => $data['total_amount'] ?? 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // 创建库存盘点明细
    public static function createCheckDetail($data)
    {
        // 实际项目中使用数据库插入
        return [
            'id' => rand(1000, 9999),
            'check_id' => $data['check_id'] ?? 0,
            'material_id' => $data['material_id'] ?? 0,
            'material_name' => $data['material_name'] ?? '',
            'material_code' => $data['material_code'] ?? '',
            'unit' => $data['unit'] ?? '',
            'system_stock' => $data['system_stock'] ?? 0,
            'actual_stock' => $data['actual_stock'] ?? 0,
            'difference' => $data['difference'] ?? 0,
            'price' => $data['price'] ?? 0,
            'amount' => $data['amount'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // 更新库存
    public static function updateStock($material_id, $stock)
    {
        // 实际项目中使用数据库更新
        return true;
    }
    
    // 生成库存报表
    public static function generateReport($params = [])
    {
        // 实际项目中使用数据库查询
        $start_date = $params['start_date'] ?? date('Y-m-01');
        $end_date = $params['end_date'] ?? date('Y-m-d');
        
        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_materials' => 3,
            'total_stock' => 155,
            'total_amount' => 2250.00,
            'details' => [
                [
                    'material_id' => 1,
                    'material_name' => '物料1',
                    'material_code' => 'MAT001',
                    'unit' => '个',
                    'stock' => 100,
                    'price' => 10.00,
                    'amount' => 1000.00
                ],
                [
                    'material_id' => 2,
                    'material_name' => '物料2',
                    'material_code' => 'MAT002',
                    'unit' => '件',
                    'stock' => 50,
                    'price' => 20.00,
                    'amount' => 1000.00
                ],
                [
                    'material_id' => 3,
                    'material_name' => '物料3',
                    'material_code' => 'MAT003',
                    'unit' => '个',
                    'stock' => 5,
                    'price' => 15.00,
                    'amount' => 75.00
                ]
            ]
        ];
    }
}
