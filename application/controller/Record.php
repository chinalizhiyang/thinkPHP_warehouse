<?php
namespace app\controller;

use app\model\Record as RecordModel;

class Record
{
    // 操作记录列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取操作记录列表
        $records = RecordModel::getOperationList();
        
        // 显示操作记录页面
        return view('record/index', ['records' => $records]);
    }
    
    // 系统日志列表
    public function system()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取系统日志列表
        $logs = RecordModel::getSystemList();
        
        // 显示系统日志页面
        return view('record/system', ['logs' => $logs]);
    }
    
    // 数据备份
    public function backup()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 执行数据备份
            $backup = RecordModel::backupData();
            
            if ($backup) {
                redirect('record/backup', '备份成功');
            } else {
                redirect('record/backup', '备份失败');
            }
        }
        
        // 获取备份列表
        $backups = RecordModel::getBackupList();
        
        // 显示数据备份页面
        return view('record/backup', ['backups' => $backups]);
    }
    
    // 清理操作记录
    public function cleanOperation()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        $days = $_POST['days'] ?? 30;
        
        // 清理操作记录
        $result = RecordModel::cleanOperation($days);
        
        if ($result) {
            redirect('record', '清理成功');
        } else {
            redirect('record', '清理失败');
        }
    }
    
    // 清理系统日志
    public function cleanSystem()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        $days = $_POST['days'] ?? 30;
        
        // 清理系统日志
        $result = RecordModel::cleanSystem($days);
        
        if ($result) {
            redirect('record/system', '清理成功');
        } else {
            redirect('record/system', '清理失败');
        }
    }
}
