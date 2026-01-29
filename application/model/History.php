<?php
namespace app\model;

class History
{
    // 获取入库历史记录列表
    public static function getInboundList($where = [])
    {
        // 实际项目中使用数据库查询
        return [
            [
                'id' => 1,
                'order_no' => 'IN20240101001',
                'supplier' => '供应商1',
                'operator' => '管理员',
                'total_amount' => 1000.00,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'order_no' => 'IN20240101002',
                'supplier' => '供应商2',
                'operator' => '管理员',
                'total_amount' => 2000.00,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // 获取出库历史记录列表
    public static function getOutboundList($where = [])
    {
        // 实际项目中使用数据库查询
        return [
            [
                'id' => 1,
                'order_no' => 'OUT20240101001',
                'customer' => '客户1',
                'operator' => '管理员',
                'total_amount' => 500.00,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'order_no' => 'OUT20240101002',
                'customer' => '客户2',
                'operator' => '管理员',
                'total_amount' => 1000.00,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // 获取统计分析数据
    public static function getAnalysisData($params = [])
    {
        // 实际项目中使用数据库查询
        $start_date = $params['start_date'] ?? date('Y-m-01');
        $end_date = $params['end_date'] ?? date('Y-m-d');
        
        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_inbound' => 3000.00,
            'total_outbound' => 1500.00,
            'net_amount' => 1500.00,
            'top_materials' => [
                [
                    'material_id' => 1,
                    'material_name' => '物料1',
                    'inbound_quantity' => 100,
                    'outbound_quantity' => 50,
                    'net_quantity' => 50
                ],
                [
                    'material_id' => 2,
                    'material_name' => '物料2',
                    'inbound_quantity' => 80,
                    'outbound_quantity' => 40,
                    'net_quantity' => 40
                ]
            ],
            'daily_data' => [
                ['date' => '2024-01-01', 'inbound' => 1000, 'outbound' => 500],
                ['date' => '2024-01-02', 'inbound' => 2000, 'outbound' => 1000],
                ['date' => '2024-01-03', 'inbound' => 0, 'outbound' => 0]
            ]
        ];
    }
}
