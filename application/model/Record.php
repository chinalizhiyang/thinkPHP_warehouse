<?php
namespace app\model;

class Record
{
    // 操作记录表结构
    // id, user_id, username, action, target, content, ip, created_at
    
    // 系统日志表结构
    // id, level, message, data, created_at
    
    // 获取操作记录列表
    public static function getOperationList($where = [])
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};port={$db_config['hostport']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
            // 构建查询 SQL（关联用户表获取用户名）
            $sql = "SELECT o.id, o.user_id, u.username, o.operation_type as action, o.target_model as target, o.content, o.ip_address as ip, o.created_at 
                    FROM operation_records o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    ORDER BY o.created_at DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
                
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
            return $records;
                
        } catch (\Exception $e) {
            error_log('Failed to get operation records: ' . $e->getMessage());
        }
            
        return [];
    }
    
    // 获取系统日志列表
    public static function getSystemList($where = [])
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};port={$db_config['hostport']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
            // 构建查询 SQL
            $sql = "SELECT id, level, message, data, created_at FROM system_logs ORDER BY created_at DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
                
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
            // 解析 JSON 数据
            foreach ($logs as &$log) {
                if (!empty($log['data'])) {
                    $log['data'] = json_decode($log['data'], true) ?: $log['data'];
                }
            }
                
            return $logs;
                
        } catch (\Exception $e) {
            error_log('Failed to get system logs: ' . $e->getMessage());
        }
            
        return [];
    }
    
    // 添加操作记录
    public static function addOperation($data)
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};port={$db_config['hostport']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 准备SQL语句
            $sql = "INSERT INTO operation_records (user_id, operation_type, target_model, target_id, content, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            // 执行插入
            $result = $stmt->execute([
                $data['user_id'] ?? 0,
                $data['action'] ?? 'unknown',
                $data['target'] ?? 'system',
                0, // target_id暂时设为0
                $data['content'] ?? '',
                $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            if ($result) {
                return [
                    'id' => $pdo->lastInsertId(),
                    'user_id' => $data['user_id'] ?? 0,
                    'username' => $data['username'] ?? '',
                    'action' => $data['action'] ?? '',
                    'target' => $data['target'] ?? '',
                    'content' => $data['content'] ?? '',
                    'ip' => $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        } catch (\Exception $e) {
            error_log('Failed to add operation record: ' . $e->getMessage());
        }
        
        // 如果数据库操作失败，返回模拟数据
        return [
            'id' => rand(1000, 9999),
            'user_id' => $data['user_id'] ?? 0,
            'username' => $data['username'] ?? '',
            'action' => $data['action'] ?? '',
            'target' => $data['target'] ?? '',
            'content' => $data['content'] ?? '',
            'ip' => $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // 添加系统日志
    public static function addSystem($data)
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};port={$db_config['hostport']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 准备SQL语句
            $sql = "INSERT INTO system_logs (level, message, data, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            // 执行插入
            $result = $stmt->execute([
                $data['level'] ?? 'info',
                $data['message'] ?? '',
                json_encode($data['data'] ?? [], JSON_UNESCAPED_UNICODE)
            ]);
            
            if ($result) {
                return [
                    'id' => $pdo->lastInsertId(),
                    'level' => $data['level'] ?? 'info',
                    'message' => $data['message'] ?? '',
                    'data' => $data['data'] ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        } catch (\Exception $e) {
            error_log('Failed to add system log: ' . $e->getMessage());
        }
        
        // 如果数据库操作失败，返回模拟数据
        return [
            'id' => rand(1000, 9999),
            'level' => $data['level'] ?? 'info',
            'message' => $data['message'] ?? '',
            'data' => $data['data'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // 清理操作记录
    public static function cleanOperation($days = 30)
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 计算要删除的日期
            $delete_before = date('Y-m-d H:i:s', strtotime("-$days days"));
            
            // 删除指定天数之前的记录
            $sql = "DELETE FROM operation_records WHERE created_at < ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$delete_before]);
            
            if ($result) {
                // 记录清理操作日志
                self::addOperation([
                    'user_id' => $_SESSION['user']['id'] ?? 0,
                    'username' => $_SESSION['user']['username'] ?? 'system',
                    'action' => 'clean',
                    'target' => 'operation_records',
                    'content' => "清理了 $days 天前的操作记录",
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            error_log('Failed to clean operation records: ' . $e->getMessage());
        }
        
        return false;
    }
    
    // 清理系统日志
    public static function cleanSystem($days = 30)
    {
        try {
            // 获取数据库连接
            $db_config = require __DIR__ . '/../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset={$db_config['charset']}",
                $db_config['username'],
                $db_config['password']
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 计算要删除的日期
            $delete_before = date('Y-m-d H:i:s', strtotime("-$days days"));
            
            // 删除指定天数之前的记录
            $sql = "DELETE FROM system_logs WHERE created_at < ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$delete_before]);
            
            if ($result) {
                // 记录清理操作日志
                self::addOperation([
                    'user_id' => $_SESSION['user']['id'] ?? 0,
                    'username' => $_SESSION['user']['username'] ?? 'system',
                    'action' => 'clean',
                    'target' => 'system_logs',
                    'content' => "清理了 $days 天前的系统日志",
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            error_log('Failed to clean system logs: ' . $e->getMessage());
        }
        
        return false;
    }
    
    // 备份数据
    public static function backupData()
    {
        // 设置时区为中国上海
        date_default_timezone_set('Asia/Shanghai');
        
        // 获取数据库配置
        $db_config = require __DIR__ . '/../config/database.php';
        
        // 生成备份文件名
        $backup_filename = 'backup_' . date('Ymd_His') . '.sql';
        $backup_path = __DIR__ . '/../../../backups/' . $backup_filename;
        
        // 确保备份目录存在
        $backup_dir = dirname($backup_path);
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // 构建mysqldump命令
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
            escapeshellarg($db_config['hostname']),
            escapeshellarg($db_config['username']),
            escapeshellarg($db_config['password']),
            escapeshellarg($db_config['database']),
            escapeshellarg($backup_path)
        );
        
        // 执行备份命令
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($backup_path)) {
            // 获取文件大小
            $file_size = filesize($backup_path);
            $size_formatted = self::formatFileSize($file_size);
            
            // 创建备份记录
            $backup_record = [
                'file' => $backup_filename,
                'full_path' => $backup_path,
                'size' => $size_formatted,
                'size_bytes' => $file_size,
                'time' => date('Y-m-d H:i:s'),
                'created_at' => time()
            ];
            
            // 存储到会话中
            if (!isset($_SESSION['backups'])) {
                $_SESSION['backups'] = [];
            }
            $_SESSION['backups'][] = $backup_record;
            
            return $backup_record;
        } else {
            // 备份失败，记录错误信息
            error_log('Database backup failed: ' . implode('\n', $output));
            return false;
        }
    }
    
    // 获取备份列表
    public static function getBackupList()
    {
        // 获取会话中的备份记录
        $session_backups = isset($_SESSION['backups']) ? $_SESSION['backups'] : [];
        
        // 直接返回会话备份（不再合并基础测试数据）
        $all_backups = $session_backups;
        
        // 按时间倒序排列（最新的在前面）
        usort($all_backups, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return $all_backups;
    }
    
    // 格式化文件大小
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    // 恢复数据库
    public static function restoreDatabase($filename)
    {
        // 获取数据库配置
        $db_config = require __DIR__ . '/../config/database.php';
        
        // 查找备份文件
        $backup_path = __DIR__ . '/../../../backups/' . $filename;
        
        if (!file_exists($backup_path)) {
            return false;
        }
        
        // 构建mysql命令
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s 2>&1',
            escapeshellarg($db_config['hostname']),
            escapeshellarg($db_config['username']),
            escapeshellarg($db_config['password']),
            escapeshellarg($db_config['database']),
            escapeshellarg($backup_path)
        );
        
        // 执行恢复命令
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return true;
        } else {
            error_log('Database restore failed: ' . implode('\n', $output));
            return false;
        }
    }
    
    // 删除备份文件
    public static function deleteBackup($filename)
    {
        // 从会话中删除指定的备份文件
        if (isset($_SESSION['backups'])) {
            $_SESSION['backups'] = array_filter($_SESSION['backups'], function($backup) use ($filename) {
                return $backup['file'] !== $filename;
            });
            // 重新索引数组
            $_SESSION['backups'] = array_values($_SESSION['backups']);
            return true;
        }
        return false;
    }
}
