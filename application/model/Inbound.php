<?php
namespace app\model;

class Inbound
{
    // 获取入库单列表
    public static function getList($where = [], $page = 1, $page_size = 25, $search = '')
    {
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 构建搜索条件
        $search_where = '';
        $params = [];
        if (!empty($search)) {
            $search_where = " WHERE material_code LIKE ? OR material_name LIKE ? OR category LIKE ? OR spec LIKE ? OR handler LIKE ? OR purchaser LIKE ?";
            $search_value = "%$search%";
            $params = array_fill(0, 6, $search_value);
        }
        
        // 使用数据库查询，获取所有入库明细，带分页
        $sql = "SELECT id, material_code, category, material_name, spec, unit, quantity, price, in_time, handler, purchaser as supplier, created_at FROM inbound" . $search_where . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $page_size;
        $params[] = $offset;
        $data = db_get_all($sql, $params);
        
        // 获取总记录数
        $total_sql = "SELECT COUNT(*) as total FROM inbound" . $search_where;
        $total_params = $search ? array_fill(0, 6, "%$search%") : [];
        $total = db_get_row($total_sql, $total_params)['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'search' => $search
        ];
    }
    
    // 根据ID获取入库单
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM inbound WHERE id = ?";
        $order = db_get_row($sql, [$id]);
        
        if ($order) {
            // 获取该入库单的所有明细
            $sql_items = "SELECT * FROM inbound WHERE in_no = ?";
            $items = db_get_all($sql_items, [$order['in_no']]);
            
            // 将明细添加到订单中
            $order['details'] = $items;
            $order['total_amount'] = $order['price'] * $order['quantity'];
        }
        
        return $order;
    }
    
    // 创建入库单
    public static function create($data)
    {
        // 获取入库单号
        $order_no = $data['in_no'] ?? 'IN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // 获取入库日期
        $in_time = $data['in_time'] ?? date('Y-m-d');
        $in_time .= ' ' . date('H:i:s');
        
        // 获取采购人
        $purchaser = $data['purchaser'] ?? '';
        
        // 处理入库明细
        $material_codes = $data['material_code'] ?? [];
        $categories = $data['category'] ?? [];
        $material_names = $data['material_name'] ?? [];
        $specs = $data['spec'] ?? [];
        $units = $data['unit'] ?? [];
        $prices = $data['price'] ?? [];
        $quantities = $data['quantity'] ?? [];
        $remarks = $data['remark'] ?? [];
        
        foreach ($material_codes as $key => $material_code) {
            if (!empty($material_code) && !empty($quantities[$key])) {
                // 获取物料信息
                $material = Material::getByCode($material_code);
                
                // 准备数据
                $detail_data = [
                    'in_no' => $order_no,
                    'material_code' => $material_code,
                    'category' => $categories[$key] ?? '',
                    'material_name' => $material_names[$key] ?? '',
                    'spec' => $specs[$key] ?? '',
                    'unit' => $units[$key] ?? '',
                    'quantity' => $quantities[$key],
                    'in_time' => $in_time,
                    'handler' => $_SESSION['user']['username'] ?? 'admin',
                    'purchaser' => $purchaser,
                    'price' => $prices[$key] ?? 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // 插入到inbound表
                $sql = "INSERT INTO inbound (in_no, material_code, category, material_name, spec, unit, quantity, in_time, handler, purchaser, price, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $result = db_exec($sql, [
                    $detail_data['in_no'],
                    $detail_data['material_code'],
                    $detail_data['category'],
                    $detail_data['material_name'],
                    $detail_data['spec'],
                    $detail_data['unit'],
                    $detail_data['quantity'],
                    $detail_data['in_time'],
                    $detail_data['handler'],
                    $detail_data['purchaser'],
                    $detail_data['price'],
                    $detail_data['created_at']
                ]);
                
                if ($result && $material) {
                    // 更新物料库存
                    $new_stock = $material['stock'] + $quantities[$key];
                    db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $material['id']]);
                }
                
                // 记录到入库历史表
                $sql_history = "INSERT INTO inbound_history (in_no, quantity, in_time, purchaser, remark, created_at, category, name, spec, unit) VALUES (?, ?, NOW(), ?, ?, NOW(), ?, ?, ?, ?)";
                db_exec($sql_history, [
                    $order_no,
                    $quantities[$key],
                    $_SESSION['user']['username'] ?? 'admin',
                    $remarks[$key] ?? '',
                    $categories[$key] ?? '',
                    $material_names[$key] ?? '',
                    $specs[$key] ?? '',
                    $units[$key] ?? ''
                ]);
            }
        }
        
        return true;
    }
    
    // 更新入库单
    public static function update($id, $data)
    {
        // 使用数据库更新
        $sql = "UPDATE inbound SET material_code = ?, category = ?, material_name = ?, spec = ?, unit = ?, quantity = ?, price = ?, in_time = ?, handler = ?, purchaser = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [
            $data['material_code'] ?? '',
            $data['category'] ?? '',
            $data['material_name'] ?? '',
            $data['spec'] ?? '',
            $data['unit'] ?? '',
            $data['quantity'] ?? 0,
            $data['price'] ?? 0,
            $data['in_time'] ?? date('Y-m-d H:i:s'),
            $data['handler'] ?? '',
            $data['supplier'] ?? '',
            $id
        ]);
        
        return $result;
    }
    
    // 删除入库单
    public static function delete($id)
    {
        // 从inbound表中删除
        $sql = "DELETE FROM inbound WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
    
    // 创建入库明细
    public static function createDetail($data)
    {
        // 这个方法现在直接调用create方法，因为我们不再使用分离的订单和明细表
        return self::create($data);
    }
    
    // 更新库存
    public static function updateStock($material_id, $quantity)
    {
        // 使用数据库更新
        $material = Material::getById($material_id);
        if ($material) {
            $new_stock = $material['stock'] + $quantity;
            $result = db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $material_id]);
            return $result;
        }
        
        return false;
    }
}
