<?php
namespace app\model;

class Inventory
{
    // 获取库存列表
    public static function getList($where = [], $params = [], $page = 1, $page_size = 25)
    {
        // 构建WHERE条件
        $where_sql = '';
        if (!empty($where) && is_array($where)) {
            $where_sql = "WHERE " . implode(' AND ', $where);
        }
        
        // 获取总记录数
        $count_sql = "SELECT COUNT(*) as total FROM materials $where_sql";
        $total_result = db_get_row($count_sql, $params);
        $total = $total_result['total'] ?? 0;
        
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 使用数据库查询materials表获取库存列表
        $sql = "SELECT 
                    id as material_id, 
                    id, 
                    name as material_name, 
                    material_code, 
                    category as category_id, 
                    category as category_name, 
                    spec, 
                    unit, 
                    price, 
                    stock, 
                    0 as min_stock, 
                    1 as status, 
                    created_at, 
                    updated_at, 
                    location 
                FROM materials $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
        
        // 添加分页参数
        $params[] = $page_size;
        $params[] = $offset;
        
        $data = db_get_all($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size
        ];
    }
    
    // 获取库存预警列表
    public static function getWarningList()
    {
        // 使用数据库查询materials表获取库存预警列表（库存低于最低库存）
        // 注意：这里假设min_stock是一个字段，如果实际表中没有，需要根据业务逻辑调整
        $sql = "SELECT 
                    id as material_id, 
                    id, 
                    name as material_name, 
                    material_code, 
                    category as category_id, 
                    category as category_name, 
                    spec, 
                    unit, 
                    price, 
                    stock, 
                    0 as min_stock, 
                    1 as status, 
                    created_at, 
                    updated_at 
                FROM materials 
                WHERE stock < 0 
                ORDER BY id DESC";
        
        return db_get_all($sql);
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
        // 使用数据库更新materials表中的库存
        $sql = "UPDATE materials SET stock = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [$stock, $material_id]);
        
        return $result;
    }
    
    // 生成库存报表
    public static function generateReport($params = [])
    {
        $start_date = $params['start_date'] ?? date('Y-m-01');
        $end_date = $params['end_date'] ?? date('Y-m-d');
        $page = $params['page'] ?? 1;
        $page_size = $params['page_size'] ?? 25;
            
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
            
        // 获取所有物料 ID 列表，用于后续计算
        $material_ids_sql = "SELECT id FROM materials WHERE created_at <= ? ORDER BY updated_at DESC, id DESC";
        $material_ids_result = db_get_all($material_ids_sql, [$end_date . ' 23:59:59']);
        $material_ids = array_column($material_ids_result, 'id');
            
        // 获取总记录数
        $total = count($material_ids);
            
        // 分页获取物料 ID
        $paginated_material_ids = array_slice($material_ids, $offset, $page_size);
            
        if (empty($paginated_material_ids)) {
            return [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_materials' => 0,
                'total_stock' => 0,
                'total_amount' => 0.00,
                'details' => [],
                'total' => 0,
                'page' => $page,
                'page_size' => $page_size
            ];
        }
            
        // 初始化结果数组
        $details = [];
        $total_materials = $total;
        $total_stock = 0;
        $total_amount = 0.00;
            
        // 批量获取物料基本信息
        $material_ids_str = implode(',', array_map('intval', $paginated_material_ids));
        $materials_sql = "SELECT id as material_id, name as material_name, material_code, spec, unit, price, stock as current_stock, location
                          FROM materials WHERE id IN ($material_ids_str)";
        $materials = db_get_all($materials_sql);
            
        // 获取本期入库数据（批量查询）
        $material_codes = array_column($materials, 'material_code');
        $material_codes_str = implode("','", array_map(function($code) {
            return addslashes($code);
        }, $material_codes));
            
        $inbound_sql = "SELECT material_code, SUM(quantity) as total_inbound 
                        FROM inbound 
                        WHERE material_code IN ('$material_codes_str') 
                        AND in_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                        GROUP BY material_code";
        $inbound_data = db_get_all($inbound_sql);
        $inbound_map = array_column($inbound_data, 'total_inbound', 'material_code');
            
        // 获取本期出库数据（批量查询）
        $outbound_sql = "SELECT material_code, SUM(quantity) as total_outbound 
                         FROM outbound 
                         WHERE material_code IN ('$material_codes_str') 
                         AND out_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                         GROUP BY material_code";
        $outbound_data = db_get_all($outbound_sql);
        $outbound_map = array_column($outbound_data, 'total_outbound', 'material_code');
            
        // 处理每个物料
        foreach ($materials as $material) {
            $material_code = $material['material_code'];
                
            // 获取本期入库和出库（使用映射，避免重复查询）
            $total_inbound = $inbound_map[$material_code] ?? 0;
            $total_outbound = $outbound_map[$material_code] ?? 0;
                
            // 计算期初库存：期末库存（当前库存） - 本期入库 + 本期出库
            $begin_stock = $material['current_stock'] - $total_inbound + $total_outbound;
                
            // 期末库存就是当前库存
            $end_stock = $material['current_stock'];
                
            // 计算库存金额
            $amount = $end_stock * $material['price'];
                
            // 添加到结果数组
            $details[] = [
                'material_id' => $material['material_id'],
                'material_name' => $material['material_name'],
                'material_code' => $material['material_code'],
                'spec' => $material['spec'] ?? '',
                'unit' => $material['unit'],
                'stock' => $begin_stock, // 期初库存
                'inbound' => $total_inbound, // 本期入库
                'outbound' => $total_outbound, // 本期出库
                'end_stock' => $end_stock, // 期末库存
                'price' => $material['price'],
                'amount' => $amount,
                'location' => $material['location'] ?? '-'
            ];
                
            // 累加总库存和总金额
            $total_stock += $end_stock;
            $total_amount += $amount;
        }
            
        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_materials' => $total_materials,
            'total_stock' => $total_stock,
            'total_amount' => $total_amount,
            'details' => $details,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size
        ];
    }
}
