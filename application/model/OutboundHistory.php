<?php
namespace app\model;

class OutboundHistory
{
    // 获取出库历史列表
    public static function getList($where = [])
    {
        // 构建查询条件
        $sql = "SELECT * FROM outbound_history ORDER BY created_at DESC";
        $list = db_get_all($sql);
        
        return $list;
    }
    
    // 根据ID获取出库历史
    public static function getById($id)
    {
        $sql = "SELECT * FROM outbound_history WHERE id = ?";
        return db_get_row($sql, [$id]);
    }
    
    // 搜索出库历史
    public static function search($keyword = '', $start_date = '', $end_date = '')
    {
        // 构建查询条件
        $sql = "SELECT * FROM outbound_history WHERE 1=1";
        $params = [];
        
        // 添加关键词搜索
        if ($keyword) {
            $sql .= " AND (out_no LIKE ? OR name LIKE ? OR category LIKE ? OR receiver LIKE ? OR dept LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        // 添加日期范围搜索
        if ($start_date) {
            $sql .= " AND out_time >= ?";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $sql .= " AND out_time <= ?";
            $params[] = $end_date;
        }
        
        // 按创建时间倒序排序
        $sql .= " ORDER BY created_at DESC";
        
        return db_get_all($sql, $params);
    }
}