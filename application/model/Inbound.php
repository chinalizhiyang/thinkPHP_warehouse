<?php
namespace app\model;

class Inbound
{
    // 入库单表结构
    // id, order_number, supplier, operator, status, total_amount, created_at, updated_at
    
    // 入库明细结构
    // id, order_id, material_id, quantity, unit_price, total_price, created_at
    
    // 获取入库单列表
    public static function getList($where = [])
    {
        // 使用数据库查询
        $sql = "SELECT * FROM inbound_orders";
        $orders = db_get_all($sql);
        
        // 转换字段名，保持与原来的代码兼容
        foreach ($orders as &$order) {
            $order['order_no'] = $order['order_number'];
        }
        
        return $orders;
    }
    
    // 根据ID获取入库单
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM inbound_orders WHERE id = ?";
        $order = db_get_row($sql, [$id]);
        
        if ($order) {
            // 转换字段名，保持与原来的代码兼容
            $order['order_no'] = $order['order_number'];
            
            // 获取入库明细
            $sql_items = "SELECT * FROM inbound_order_items WHERE order_id = ?";
            $items = db_get_all($sql_items, [$id]);
            
            // 转换明细字段名
            $details = [];
            foreach ($items as $item) {
                // 获取物料信息
                $material = Material::getById($item['material_id']);
                
                $details[] = [
                    'id' => $item['id'],
                    'inbound_id' => $id,
                    'material_id' => $item['material_id'],
                    'material_name' => $material['name'] ?? '',
                    'material_code' => $material['code'] ?? '',
                    'unit' => $material['unit'] ?? '',
                    'price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'amount' => $item['total_price']
                ];
            }
            
            $order['details'] = $details;
            
            return $order;
        }
        
        return false;
    }
    
    // 创建入库单
    public static function create($data)
    {
        // 使用数据库插入
        $order_no = 'IN' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO inbound_orders (order_number, supplier, operator, status, total_amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $result = db_exec($sql, [$order_no, $data['supplier'] ?? '', $_SESSION['user']['username'] ?? 'admin', 1, $data['total_amount'] ?? 0]);
        
        if ($result) {
            $conn = db_connect();
            $id = $conn->insert_id;
            $conn->close();
            
            // 创建入库明细
            if (isset($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $detail) {
                    $sql_item = "INSERT INTO inbound_order_items (order_id, material_id, quantity, unit_price, total_price, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                    db_exec($sql_item, [$id, $detail['material_id'], $detail['quantity'], $detail['price'], $detail['amount']]);
                    
                    // 更新物料库存
                    $material = Material::getById($detail['material_id']);
                    if ($material) {
                        $new_stock = $material['stock'] + $detail['quantity'];
                        db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $detail['material_id']]);
                        
                        // 记录到入库历史表
                        $sql_history = "INSERT INTO inbound_history (in_no, quantity, in_time, purchaser, remark, created_at, category, name, spec, unit) VALUES (?, ?, NOW(), ?, ?, NOW(), ?, ?, ?, ?)";
                        db_exec($sql_history, [
                            $order_no,
                            $detail['quantity'],
                            $_SESSION['user']['username'] ?? 'admin',
                            '',
                            $material['category'] ?? '',
                            $material['name'] ?? '',
                            $material['spec'] ?? '',
                            $material['unit'] ?? ''
                        ]);
                    }
                }
            }
            
            return self::getById($id);
        }
        
        return false;
    }
    
    // 更新入库单
    public static function update($id, $data)
    {
        // 使用数据库更新
        $sql = "UPDATE inbound_orders SET supplier = ?, total_amount = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [$data['supplier'] ?? '', $data['total_amount'] ?? 0, $id]);
        
        return $result;
    }
    
    // 删除入库单
    public static function delete($id)
    {
        // 先删除入库明细
        db_exec("DELETE FROM inbound_order_items WHERE order_id = ?", [$id]);
        
        // 再删除入库单
        $sql = "DELETE FROM inbound_orders WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
    
    // 创建入库明细
    public static function createDetail($data)
    {
        // 使用数据库插入
        $sql = "INSERT INTO inbound_order_items (order_id, material_id, quantity, unit_price, total_price, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $result = db_exec($sql, [$data['inbound_id'] ?? 0, $data['material_id'] ?? 0, $data['quantity'] ?? 0, $data['price'] ?? 0, $data['amount'] ?? 0]);
        
        if ($result) {
            // 更新物料库存
            $material = Material::getById($data['material_id'] ?? 0);
            if ($material) {
                $new_stock = $material['stock'] + ($data['quantity'] ?? 0);
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
            $new_stock = $material['stock'] + $quantity;
            $result = db_exec("UPDATE materials SET stock = ? WHERE id = ?", [$new_stock, $material_id]);
            return $result;
        }
        
        return false;
    }
}
