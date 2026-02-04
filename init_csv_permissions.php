<?php
/**
 * 权限数据库初始化脚本
 * 用于添加导入CSV相关的细粒度权限
 */

require_once 'application/common.php';

// 连接数据库
$conn = db_connect();

try {
    // 检查权限表是否存在
    $tables = db_get_all("SHOW TABLES LIKE 'auth_permissions'");
    if (empty($tables)) {
        echo "权限表不存在，请先运行数据库初始化脚本。\n";
        exit(1);
    }
    
    // 定义新的权限项
    $new_permissions = [
        [
            'name' => 'material_import_csv',
            'description' => '物料导入CSV',
            'module' => 'material'
        ],
        [
            'name' => 'inventory_export_csv',
            'description' => '库存导出CSV',
            'module' => 'inventory'
        ],
        [
            'name' => 'inbound_history_import_csv',
            'description' => '入库历史导入CSV',
            'module' => 'inbound_history'
        ],
        [
            'name' => 'inbound_history_export_csv',
            'description' => '入库历史导出CSV',
            'module' => 'inbound_history'
        ],
        [
            'name' => 'outbound_history_import_csv',
            'description' => '出库历史导入CSV',
            'module' => 'outbound_history'
        ],
        [
            'name' => 'outbound_history_export_csv',
            'description' => '出库历史导出CSV',
            'module' => 'outbound_history'
        ]
    ];
    
    echo "开始添加CSV相关权限...\n";
    
    foreach ($new_permissions as $permission) {
        // 检查权限是否已存在
        $existing = db_get_row(
            "SELECT id FROM auth_permissions WHERE name = ?", 
            [$permission['name']]
        );
        
        if ($existing) {
            echo "权限 {$permission['name']} 已存在，跳过。\n";
            continue;
        }
        
        // 插入新权限
        $sql = "INSERT INTO auth_permissions (name, description, module, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $result = db_exec($sql, [
            $permission['name'],
            $permission['description'],
            $permission['module']
        ]);
        
        if ($result) {
            echo "✓ 成功添加权限: {$permission['description']} ({$permission['name']})\n";
        } else {
            echo "✗ 添加权限失败: {$permission['name']}\n";
        }
    }
    
    echo "\n权限初始化完成！\n";
    echo "现在可以在权限分配页面看到导入CSV相关的权限选项。\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
} finally {
    $conn->close();
}
?>