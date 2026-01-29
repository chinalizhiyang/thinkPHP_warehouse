<?php
namespace app\model;

class Material
{
    // 物料表结构
    // id, material_code, category, name, spec, unit, location, stock, created_at, updated_at, contact_info, description, supplier, price
    
    // 获取物料列表
    public static function getList($where = [])
    {
        // 使用数据库查询
        $sql = "SELECT * FROM materials";
        $materials = db_get_all($sql);
        
        // 转换字段名，保持与原来的代码兼容
        foreach ($materials as &$material) {
            $material['code'] = $material['material_code'];
            $material['category_id'] = $material['category'];
            $material['category_name'] = $material['category'];
            $material['min_stock'] = 0; // 兼容旧字段
            $material['status'] = 1; // 兼容旧字段
        }
        
        return $materials;
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
            $material['min_stock'] = 0; // 兼容旧字段
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
    
    // 获取分类列表
    public static function getCategoryList()
    {
        // 使用数据库查询
        $sql = "SELECT DISTINCT category FROM materials";
        $categories = db_get_all($sql);
        
        $result = [];
        $id = 1;
        foreach ($categories as $category) {
            $result[] = [
                'id' => $id++,
                'name' => $category['category'],
                'parent_id' => 0
            ];
        }
        
        return $result;
    }
}
