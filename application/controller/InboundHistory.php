<?php
namespace app\controller;

use app\model\InboundHistory;

class InboundHistory
{
    // 显示入库历史列表
    public function index()
    {
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库历史列表
        $list = InboundHistory::getList();
        
        // 渲染视图
        return view('inbound_history/index', [
            'list' => $list,
            'title' => '入库历史'
        ]);
    }
    
    // 显示入库历史详情
    public function detail($id)
    {
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库历史详情
        $detail = InboundHistory::getById($id);
        
        if (!$detail) {
            redirect('/inbound-history', '入库历史记录不存在');
        }
        
        // 渲染视图
        return view('inbound_history/detail', [
            'detail' => $detail,
            'title' => '入库历史详情'
        ]);
    }
    
    // 搜索入库历史
    public function search()
    {
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索条件
        $keyword = request('keyword', '');
        $start_date = request('start_date', '');
        $end_date = request('end_date', '');
        
        // 搜索入库历史
        $list = InboundHistory::search($keyword, $start_date, $end_date);
        
        // 渲染视图
        return view('inbound_history/index', [
            'list' => $list,
            'keyword' => $keyword,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'title' => '入库历史搜索结果'
        ]);
    }
}
