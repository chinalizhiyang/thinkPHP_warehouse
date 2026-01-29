<?php
namespace app\controller;

use app\model\OutboundHistory;

class OutboundHistory
{
    // 显示出库历史列表
    public function index()
    {
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库历史列表
        $list = OutboundHistory::getList();
        
        // 渲染视图
        return view('outbound_history/index', [
            'list' => $list,
            'title' => '出库历史'
        ]);
    }
    
    // 显示出库历史详情
    public function detail($id)
    {
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库历史详情
        $detail = OutboundHistory::getById($id);
        
        if (!$detail) {
            redirect('/outbound-history', '出库历史记录不存在');
        }
        
        // 渲染视图
        return view('outbound_history/detail', [
            'detail' => $detail,
            'title' => '出库历史详情'
        ]);
    }
    
    // 搜索出库历史
    public function search()
    {
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索条件
        $keyword = request('keyword', '');
        $start_date = request('start_date', '');
        $end_date = request('end_date', '');
        
        // 搜索出库历史
        $list = OutboundHistory::search($keyword, $start_date, $end_date);
        
        // 渲染视图
        return view('outbound_history/index', [
            'list' => $list,
            'keyword' => $keyword,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'title' => '出库历史搜索结果'
        ]);
    }
}
