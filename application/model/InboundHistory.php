<?php
namespace app\model;

class InboundHistory
{
    // 获取入库历史列表
    public static function getList($where = [], $params = [], $page = 1, $page_size = 25)
    {
        // 构建查询条件
        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where_sql = 'WHERE 1=1';
        }
        
        // 获取总记录数
        $count_sql = "SELECT COUNT(*) as count FROM inbound_history $where_sql";
        $count_params = $params;  // 参数副本用于计数查询
        
        // 在计数查询中不需要分页参数
        $count_result = db_get_row($count_sql, $count_params);
        $total = $count_result['count'] ?? 0;
        
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 构建查询条件
        $sql = "SELECT * FROM inbound_history $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $list_params = array_merge($params, [$page_size, $offset]);
        
        $list = db_get_all($sql, $list_params);
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size
        ];
    }
    
    // 根据ID获取入库历史
    public static function getById($id)
    {
        $sql = "SELECT * FROM inbound_history WHERE id = ?";
        return db_get_row($sql, [$id]);
    }
    
    // 搜索入库历史
    public static function search($keyword = '', $start_date = '', $end_date = '', $page = 1, $page_size = 25)
    {
        // 构建查询条件
        $where_sql = "WHERE 1=1";
        $params = [];
        
        // 添加关键词搜索
        if ($keyword) {
            $where_sql .= " AND (in_no LIKE ? OR name LIKE ? OR category LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 添加日期范围搜索
        if ($start_date) {
            $where_sql .= " AND in_time >= ?";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where_sql .= " AND in_time <= ?";
            $params[] = $end_date;
        }
        
        // 获取总记录数
        $count_sql = "SELECT COUNT(*) as count FROM inbound_history $where_sql";
        $count_result = db_get_row($count_sql, $params);
        $total = $count_result['count'] ?? 0;
        
        // 计算偏移量
        $offset = ($page - 1) * $page_size;
        
        // 构建查询条件
        $sql = "SELECT * FROM inbound_history $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $page_size;
        $params[] = $offset;
        
        $list = db_get_all($sql, $params);
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size
        ];
    }
    
    // 更新入库历史
    public static function update($id, $data)
    {
        $sql = "UPDATE inbound_history SET in_no = ?, name = ?, category = ?, quantity = ?, unit = ?, in_time = ?, purchaser = ?, remark = ? WHERE id = ?";
        return db_exec($sql, [$data['in_no'], $data['name'], $data['category'], $data['quantity'], $data['unit'], $data['in_time'], $data['purchaser'], $data['remark'] ?? '', $id]);
    }
    
    // 删除入库历史
    public static function delete($id)
    {
        $sql = "DELETE FROM inbound_history WHERE id = ?";
        return db_exec($sql, [$id]);
    }
}