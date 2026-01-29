<?php
namespace app\controller;

use app\model\History as HistoryModel;

class History
{
    // 历史记录首页
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('history_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 显示历史记录首页
        return view('history/index');
    }
    
    // 入库历史记录
    public function inbound()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('history_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库历史记录列表
        $inbounds = HistoryModel::getInboundList();
        
        // 显示入库历史记录页面
        return view('history/inbound', ['inbounds' => $inbounds]);
    }
    
    // 出库历史记录
    public function outbound()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('history_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库历史记录列表
        $outbounds = HistoryModel::getOutboundList();
        
        // 显示出库历史记录页面
        return view('history/outbound', ['outbounds' => $outbounds]);
    }
    
    // 统计分析
    public function analysis()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('history_manage')) {
            redirect('/', '无权限访问');
        }
        
        $params = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d')
        ];
        
        // 获取统计分析数据
        $analysis = HistoryModel::getAnalysisData($params);
        
        // 显示统计分析页面
        return view('history/analysis', ['analysis' => $analysis]);
    }
}
