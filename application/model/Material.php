<?php
namespace app\model;

class Material
{
    // 物料表结构
    // id, material_code, category, name, spec, unit, location, stock, created_at, updated_at, contact_info, description, supplier, price
    
    // 获取物料列表
    public static function getList($where = [], $page = 1, $page_size = 25, $params = [])
    {
        // 构建WHERE条件
        $where_sql = '';
        if (!empty($where) && is_array($where)) {
            $where_sql = "WHERE " . implode(' AND ', $where);
        }
        
        // 获取总记录数
        $count_sql = "SELECT COUNT(*) as count FROM materials $where_sql";
        $total = db_get_row($count_sql, $params)['count'] ?? 0;
        
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 使用数据库查询，添加分页
        $sql = "SELECT * FROM materials $where_sql ORDER BY updated_at DESC, id DESC LIMIT ? OFFSET ?";
        $all_params = array_merge($params, [$page_size, $offset]);
        $materials = db_get_all($sql, $all_params);
        
        // 转换字段名，保持与原来的代码兼容
        foreach ($materials as &$material) {
            $material['code'] = $material['material_code'];
            $material['category_id'] = $material['category'];
            $material['category_name'] = $material['category'];
            $material['status'] = 1; // 兼容旧字段
        }
        
        return ['list' => $materials, 'total' => $total, 'page' => $page, 'page_size' => $page_size];
    }
    
    // 根据ID获取物料
    public static function getById($id)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM materials WHERE id = ?";
        $material = db_get_row($sql, [$id]);
        
        if ($material) {
            // 转换字段名，保持与原来的代码兼容
            $material['code'] = $material['material_code'];
            $material['category_id'] = $material['category'];
            $material['category_name'] = $material['category'];
            $material['status'] = 1; // 兼容旧字段
            
            return $material;
        }
        
        return false;
    }
    
    // 根据物料编码获取物料
    public static function getByCode($code)
    {
        // 使用数据库查询
        $sql = "SELECT * FROM materials WHERE material_code = ?";
        $material = db_get_row($sql, [$code]);
        
        if ($material) {
            // 转换字段名，保持与原来的代码兼容
            $material['code'] = $material['material_code'];
            $material['category_id'] = $material['category'];
            $material['category_name'] = $material['category'];
            $material['status'] = 1; // 兼容旧字段
            
            return $material;
        }
        
        return false;
    }
    
    // 创建物料
    public static function create($data)
    {
        // 使用数据库插入
        $sql = "INSERT INTO materials (material_code, category, name, spec, unit, location, stock, created_at, updated_at, contact_info, description, supplier, price) VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW(), ?, ?, ?, ?)";
        $result = db_exec($sql, [$data['code'], $data['category_id'], $data['name'], $data['spec'] ?? '', $data['unit'], $data['location'] ?? '', $data['contact_info'] ?? '', $data['description'] ?? '', $data['supplier'] ?? '', $data['price']]);
        
        if ($result) {
            $conn = db_connect();
            $id = $conn->insert_id;
            $conn->close();
            return self::getById($id);
        }
        
        return false;
    }
    
    // 更新物料
    public static function update($id, $data)
    {
        // 使用数据库更新
        $sql = "UPDATE materials SET material_code = ?, category = ?, name = ?, spec = ?, unit = ?, location = ?, contact_info = ?, description = ?, supplier = ?, price = ?, updated_at = NOW() WHERE id = ?";
        $result = db_exec($sql, [$data['code'], $data['category_id'], $data['name'], $data['spec'] ?? '', $data['unit'], $data['location'] ?? '', $data['contact_info'] ?? '', $data['description'] ?? '', $data['supplier'] ?? '', $data['price'], $id]);
        
        return $result;
    }
    
    // 删除物料
    public static function delete($id)
    {
        // 使用数据库删除
        $sql = "DELETE FROM materials WHERE id = ?";
        $result = db_exec($sql, [$id]);
        
        return $result;
    }
}
