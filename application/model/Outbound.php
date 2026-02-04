<?php
namespace app\model;

class Outbound
{
    // 出库单表结构
    // id, order_number, customer, operator, status, total_amount, created_at, updated_at
    
    // 出库明细结构
    // id, order_id, material_id, quantity, unit_price, total_price, created_at
    
    // 获取出库单列表
    public static function getList($where = [], $page = 1, $page_size = 25, $search = '')
    {
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 构建搜索条件
        $search_where = '';
        $params = [];
        if (!empty($search)) {
            $search_where = " WHERE material_code LIKE ? OR material_name LIKE ? OR category LIKE ? OR spec LIKE ? OR receiver LIKE ? OR dept LIKE ?";
            $search_value = "%$search%";
            $params = array_fill(0, 6, $search_value);
        }
        
        // 使用数据库查询，获取所有出库明细，带分页
        $sql = "SELECT id, material_code, category, material_name, spec, unit, quantity, price, out_time, receiver, dept, remark, created_at FROM outbound" . $search_where . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $page_size;
        $params[] = $offset;
        $data = db_get_all($sql, $params);
        
        // 获取总记录数
        $total_sql = "SELECT COUNT(*) as total FROM outbound" . $search_where;
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
    
    // 根据ID获取出库单
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM outbound WHERE id = ?";
        $outbound = db_get_row($sql, [$id]);
        
        return $outbound;
    }
    
    // 创建出库单
    public static function create($data)
    {
        // 获取表单数据
        $out_no = $data['out_no'] ?? '';
        $out_time = $data['out_time'] ?? date('Y-m-d H:i:s');
        $dept = $data['dept'] ?? '';
        $receiver = $data['receiver'] ?? '';
        
        // 处理物料列表
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
                
                if ($material) {
                    // 检查库存是否足够
                    if ($material['stock'] < $quantities[$key]) {
                        continue; // 库存不足，跳过
                    }
                    
                    // 插入到outbound表
                    $sql = "INSERT INTO outbound (material_code, category, material_name, spec, unit, quantity, price, out_time, receiver, dept, remark, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    db_exec($sql, [
                        $material_code,
                        $categories[$key] ?? $material['category'],
                        $material_names[$key] ?? $material['name'],
                        $specs[$key] ?? $material['spec'],
                        $units[$key] ?? $material['unit'],
                        $quantities[$key],
                        $prices[$key] ?? $material['price'],
                        $out_time,
                        $receiver,
                        $dept,
                        $remarks[$key] ?? ''
                    ]);
                    
                    // 更新物料库存
                    if ($material) {
                        $new_stock = $material['stock'] - $quantities[$key];
                        db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $material['id']]);
                    }
                    
                    // 记录到出库历史表
                    $sql_history = "INSERT INTO outbound_history (out_no, quantity, out_time, dept, receiver, remark, created_at, category, name, spec, unit) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
                    db_exec($sql_history, [
                        $out_no,
                        $quantities[$key],
                        $out_time,
                        $dept,
                        $receiver,
                        $remarks[$key] ?? '',
                        $material['category'] ?? '',
                        $material['name'] ?? '',
                        $material['spec'] ?? '',
                        $material['unit'] ?? ''
                    ]);
                }
            }
        }
        
        return true;
    }
    
    // 更新出库单
    public static function update($id, $data)
    {
        // 使用数据库更新
        $sql = "UPDATE outbound SET material_code = ?, category = ?, material_name = ?, spec = ?, unit = ?, quantity = ?, price = ?, out_time = ?, receiver = ?, dept = ?, remark = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [
            $data['material_code'] ?? '',
            $data['category'] ?? '',
            $data['material_name'] ?? '',
            $data['spec'] ?? '',
            $data['unit'] ?? '',
            $data['quantity'] ?? 0,
            $data['price'] ?? 0,
            $data['out_time'] ?? date('Y-m-d H:i:s'),
            $data['receiver'] ?? '',
            $data['dept'] ?? '',
            $data['remark'] ?? '',
            $id
        ]);
        
        return $result;
    }
    
    // 删除出库单
    public static function delete($id)
    {
        // 从outbound表中删除
        $sql = "DELETE FROM outbound WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
    
    // 创建出库明细
    public static function createDetail($data)
    {
        // 使用数据库插入
        $sql = "INSERT INTO outbound_order_items (order_id, material_id, quantity, unit_price, total_price, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $result = db_exec($sql, [$data['outbound_id'] ?? 0, $data['material_id'] ?? 0, $data['quantity'] ?? 0, $data['price'] ?? 0, $data['amount'] ?? 0]);
        
        if ($result) {
            // 更新物料库存
            $material = Material::getById($data['material_id'] ?? 0);
            if ($material) {
                $new_stock = $material['stock'] - ($data['quantity'] ?? 0);
                db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $data['material_id'] ?? 0]);
            }
            
            return true;
        }
        
        return false;
    }
    
    // 更新库存
    public static function updateStock($material_id, $quantity)
    {
        // 使用数据库更新
        $material = Material::getById($material_id);
        if ($material) {
            $new_stock = $material['stock'] - $quantity;
            $result = db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $material_id]);
            return $result;
        }
        
        return false;
    }
}
