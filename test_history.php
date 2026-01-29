<?php
// 测试出入库历史记录功能

// 加载数据库连接函数
require_once 'application/common.php';

// 测试数据
$test_material_id = 1; // 假设物料ID为1
$test_quantity = 5;

// 测试入库操作
echo "\n=== 测试入库操作 ===\n";
try {
    // 模拟入库数据
    $inbound_data = [
        'supplier' => '测试供应商',
        'total_amount' => 100,
        'details' => [
            [
                'material_id' => $test_material_id,
                'quantity' => $test_quantity,
                'price' => 20,
                'amount' => 100
            ]
        ]
    ];
    
    // 模拟会话
    $_SESSION['user']['username'] = 'test_user';
    
    // 调用入库模型创建方法
    require_once 'application/model/Inbound.php';
    require_once 'application/model/Material.php';
    
    $result = app\model\Inbound::create($inbound_data);
    
    if ($result) {
        echo "入库操作成功！\n";
        echo "入库单号: " . $result['order_no'] . "\n";
        
        // 检查入库历史表
        $sql = "SELECT * FROM inbound_history ORDER BY created_at DESC LIMIT 1";
        $history = db_get_row($sql);
        
        if ($history) {
            echo "\n入库历史记录创建成功！\n";
            echo "历史记录ID: " . $history['id'] . "\n";
            echo "入库单号: " . $history['in_no'] . "\n";
            echo "数量: " . $history['quantity'] . "\n";
            echo "入库时间: " . $history['in_time'] . "\n";
            echo "采购人: " . $history['purchaser'] . "\n";
            echo "物料名称: " . $history['name'] . "\n";
        } else {
            echo "\n错误: 入库历史记录未创建！\n";
        }
    } else {
        echo "入库操作失败！\n";
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 测试出库操作
echo "\n=== 测试出库操作 ===\n";
try {
    // 模拟出库数据
    $outbound_data = [
        'customer' => '测试客户',
        'total_amount' => 60,
        'details' => [
            [
                'material_id' => $test_material_id,
                'quantity' => 3,
                'price' => 20,
                'amount' => 60
            ]
        ]
    ];
    
    // 调用出库模型创建方法
    require_once 'application/model/Outbound.php';
    
    $result = app\model\Outbound::create($outbound_data);
    
    if ($result) {
        echo "出库操作成功！\n";
        echo "出库单号: " . $result['order_no'] . "\n";
        
        // 检查出库历史表
        $sql = "SELECT * FROM outbound_history ORDER BY created_at DESC LIMIT 1";
        $history = db_get_row($sql);
        
        if ($history) {
            echo "\n出库历史记录创建成功！\n";
            echo "历史记录ID: " . $history['id'] . "\n";
            echo "出库单号: " . $history['out_no'] . "\n";
            echo "数量: " . $history['quantity'] . "\n";
            echo "出库时间: " . $history['out_time'] . "\n";
            echo "部门: " . $history['dept'] . "\n";
            echo "领用人: " . $history['receiver'] . "\n";
            echo "物料名称: " . $history['name'] . "\n";
        } else {
            echo "\n错误: 出库历史记录未创建！\n";
        }
    } else {
        echo "出库操作失败！\n";
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 测试历史记录查询
echo "\n=== 测试历史记录查询 ===\n";
try {
    // 测试入库历史查询
    require_once 'application/model/InboundHistory.php';
    $inbound_history_list = app\model\InboundHistory::getList();
    echo "入库历史记录数量: " . count($inbound_history_list) . "\n";
    
    // 测试出库历史查询
    require_once 'application/model/OutboundHistory.php';
    $outbound_history_list = app\model\OutboundHistory::getList();
    echo "出库历史记录数量: " . count($outbound_history_list) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

echo "\n测试完成！\n";
