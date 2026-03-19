<?php
namespace app\controller;

use think\facade\Db;
use think\facade\View;

class Record
{
    /**
     * 检查登录状态
     */
    private function checkLogin()
    {
        if (!isset($_COOKIE['user'])) {
            redirect('login', '请先登录');
        }
    }
    
    /**
     * 备份页面
     */
    public function backup()
    {
        $this->checkLogin();
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 获取数据库配置
        $dbConfig = require __DIR__ . '/../config/database.php';
        $hostname = $dbConfig['hostname'];
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        
        // 渲染备份内容
        $content = $this->renderBackupContent($hostname, $database, $username);
        
        return view('layout/main', [
            'title' => '数据备份',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Record'
        ]);
    }
    
    /**
     * 渲染备份页面内容
     */
    private function renderBackupContent($hostname, $database, $username)
    {
        ob_start();
        ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-database"></i> 数据备份</h2>
            <hr>
            
            <div id="backupMessage" style="display:none;" class="alert alert-dismissible fade show" role="alert">
                <span id="backupMessageText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-download"></i> 备份数据库</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">备份信息:</p>
                    <ul class="list-group mb-3">
                        <li class="list-group-item">
                            <strong>主机:</strong> <?php echo htmlspecialchars($hostname); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>数据库名:</strong> <?php echo htmlspecialchars($database); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>用户名:</strong> <?php echo htmlspecialchars($username); ?>
                        </li>
                    </ul>
                    
                    <button type="button" class="btn btn-primary" id="backupBtn">
                        <i class="fas fa-save"></i> 立即备份
                    </button>
                    
                    <div id="backupProgress" style="display:none;" class="mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">正在备份...</span>
                        </div>
                        <span class="ms-2">正在备份，请稍候...</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> 备份历史</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">已保存的备份文件将存储在 <code>backups/</code> 目录中。</p>
                    <a href="/record/restore" class="btn btn-success">
                        <i class="fas fa-upload"></i> 查看备份历史并恢复
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backupBtn = document.getElementById('backupBtn');
    const backupProgress = document.getElementById('backupProgress');
    const backupMessage = document.getElementById('backupMessage');
    const backupMessageText = document.getElementById('backupMessageText');
    
    backupBtn.addEventListener('click', function() {
        if (confirm('\u786e\u5b9a\u8981\u5907\u4efd\u5f53\u524d\u6570\u636e\u5e93\u5417\uff1f')) {
            backupBtn.disabled = true;
            backupProgress.style.display = 'block';
            backupMessage.style.display = 'none';
            
            fetch('/record/executeBackup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // 检查是否为 JSON 响应
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // 如果不是 JSON，尝试获取文本
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text.substring(0, 500));
                        throw new Error('服务器返回了非 JSON 响应');
                    });
                }
            })
            .then(data => {
                console.log('Response data:', data);
                backupProgress.style.display = 'none';
                backupBtn.disabled = false;
                
                backupMessageText.textContent = data.message;
                backupMessage.className = 'alert alert-' + (data.success ? 'success' : 'danger') + ' alert-dismissible fade show';
                backupMessage.style.display = 'block';
                
                if (data.success) {
                    setTimeout(() => {
                        backupMessage.style.display = 'none';
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                backupProgress.style.display = 'none';
                backupBtn.disabled = false;
                
                backupMessageText.textContent = '\u5907\u4efd\u5931\u8d25\uff1a' + error.message;
                backupMessage.className = 'alert alert-danger alert-dismissible fade show';
                backupMessage.style.display = 'block';
            });
        }
    });
});
</script>
<?php
        return ob_get_clean();
    }
    
    /**
     * 执行备份
     */
    public function executeBackup()
    {
        try {
            $this->checkLogin();
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '非法请求']);
                return;
            }
            
            // 获取数据库配置
            $dbConfig = require __DIR__ . '/../config/database.php';
            $hostname = $dbConfig['hostname'];
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];
            $hostport = $dbConfig['hostport'] ?? '3306';
            
            // mysqldump 路径
            $mysqldumpPath = 'c:/wamp64/bin/mysql/mysql8.2.0/bin/mysqldump.exe';
            
            // 设置备份文件名
            $backupDir = dirname(dirname(__DIR__)) . '/backups/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filename = $database . '_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . $filename;
            
            // 构建 mysqldump 命令（使用完整路径和正确的端口）
            $command = "\"{$mysqldumpPath}\" -h{$hostname} -P{$hostport} -u{$username} -p\"{$password}\" {$database} > \"{$filepath}\"";
            
            // 执行命令并捕获输出
            exec($command . ' 2>&1', $output, $returnVar);
            
            // 记录调试信息
            error_log("mysqldump command: {$command}");
            error_log("Return code: {$returnVar}");
            error_log("Output: " . print_r($output, true));
            
            if ($returnVar === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => '备份成功',
                    'filename' => $filename,
                    'path' => $filepath
                ]);
                return;
            } else {
                $errorMsg = '备份失败';
                if (!empty($output)) {
                    $errorMsg .= ': ' . implode(' ', $output);
                } else {
                    $errorMsg .= '，请检查数据库配置或 mysqldump 是否可用';
                }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => $errorMsg
                ]);
                return;
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => '备份异常：' . $e->getMessage()
            ]);
            return;
        }
    }
    
    /**
     * 恢复页面
     */
    public function restore()
    {
        $this->checkLogin();
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 获取所有备份文件
        $backupDir = dirname(dirname(__DIR__)) . '/backups/';
        $files = [];
        
        if (is_dir($backupDir)) {
            $handle = opendir($backupDir);
            while (($file = readdir($handle)) !== false) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($backupDir . $file),
                        'time' => filemtime($backupDir . $file)
                    ];
                }
            }
            closedir($handle);
            
            // 按时间倒序排列
            usort($files, function($a, $b) {
                return $b['time'] - $a['time'];
            });
        }
        
        // 渲染恢复内容
        $content = $this->renderRestoreContent($files);
        
        return view('layout/main', [
            'title' => '数据恢复',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Record'
        ]);
    }
    
    /**
     * 渲染恢复页面内容
     */
    private function renderRestoreContent($files)
    {
        ob_start();
        ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-history"></i> 数据恢复</h2>
            <hr>
            
            <div id="restoreMessage" style="display:none;" class="alert alert-dismissible fade show" role="alert">
                <span id="restoreMessageText"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-upload"></i> 选择备份文件</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($files)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 暂无备份文件，请先进行数据备份。
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>文件名</th>
                                        <th>文件大小</th>
                                        <th>备份时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($file['name']); ?></td>
                                        <td><?php echo number_format($file['size'] / 1024, 2); ?> KB</td>
                                        <td><?php echo date('Y-m-d H:i:s', $file['time']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-success restore-btn" data-filename="<?php echo htmlspecialchars($file['name']); ?>">
                                                <i class="fas fa-upload"></i> 恢复
                                            </button>
                                            <a href="/record/download?filename=<?php echo urlencode($file['name']); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> 下载
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-btn" data-filename="<?php echo htmlspecialchars($file['name']); ?>">
                                                <i class="fas fa-trash"></i> 删除
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const restoreMessage = document.getElementById('restoreMessage');
    const restoreMessageText = document.getElementById('restoreMessageText');
    
    // 恢复按钮事件
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            if (confirm('\u786e\u5b9a\u8981\u6062\u590d\u5230\u6b64\u5907\u4efd\u5417\uff1f\u6062\u590d\u8fc7\u7a0b\u5c06\u8986\u76d6\u5f53\u524d\u6570\u636e\u5e93\uff0c\u8bf7\u8c28\u614e\u64cd\u4f5c\uff01')) {
                performRestore(filename);
            }
        });
    });
    
    // 删除按钮事件
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            if (confirm('\u786e\u5b9a\u8981\u5220\u9664\u6b64\u5907\u4efd\u6587\u4ef6\u5417\uff1f\u6b64\u64cd\u4f5c\u4e0d\u53ef\u6062\u590d\uff01')) {
                performDelete(filename, this.closest('tr'));
            }
        });
    });
    
    function performRestore(filename) {
        fetch('/record/executeRestore', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ filename: filename })
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                setTimeout(() => location.reload(), 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('\u6062\u590d\u5931\u8d25\uff1a\u7f51\u7edc\u9519\u8bef', 'danger');
        });
    }
    
    function performDelete(filename, row) {
        fetch('/record/deleteBackup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ filename: filename })
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                row.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('\u5220\u9664\u5931\u8d25\uff1a\u7f51\u7edc\u9519\u8bef', 'danger');
        });
    }
    
    function showMessage(message, type) {
        restoreMessageText.textContent = message;
        restoreMessage.className = 'alert alert-' + type + ' alert-dismissible fade show';
        restoreMessage.style.display = 'block';
        if (type === 'success') {
            setTimeout(() => {
                restoreMessage.style.display = 'none';
            }, 5000);
        }
    }
});
</script>
<?php
        return ob_get_clean();
    }
    
    /**
     * 执行恢复
     */
    public function executeRestore()
    {
        $this->checkLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '非法请求']);
            return;
        }
        
        // 从 JSON 请求体中获取 filename
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $filename = $data['filename'] ?? '';
        
        if (empty($filename)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '请选择要恢复的备份文件']);
            return;
        }
        
        $backupDir = dirname(dirname(__DIR__)) . '/backups/';
        $filepath = $backupDir . $filename;
        
        if (!file_exists($filepath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '备份文件不存在']);
            return;
        }
        
        // 获取数据库配置
        $dbConfig = require __DIR__ . '/../config/database.php';
        $hostname = $dbConfig['hostname'];
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $hostport = $dbConfig['hostport'] ?? '3306';
        
        // 构建 mysql 导入命令（使用正确的端口）
        $command = "mysql -h{$hostname} -P{$hostport} -u{$username} -p\"{$password}\" {$database} < \"{$filepath}\"";
        
        // 执行命令
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => '恢复成功']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '恢复失败，请检查数据库配置']);
        }
    }
    
    /**
     * 删除备份文件
     */
    public function deleteBackup()
    {
        $this->checkLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '非法请求']);
            return;
        }
        
        // 从 JSON 请求体中获取 filename
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $filename = $data['filename'] ?? '';
        
        if (empty($filename)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '请选择要删除的备份文件']);
            return;
        }
        
        $backupDir = dirname(dirname(__DIR__)) . '/backups/';
        $filepath = $backupDir . $filename;
        
        if (!file_exists($filepath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '备份文件不存在']);
            return;
        }
        
        if (unlink($filepath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => '删除成功']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '删除失败']);
        }
    }
    
    /**
     * 下载备份文件
     */
    public function download()
    {
        $this->checkLogin();
        
        $filename = $_GET['filename'] ?? '';
        
        if (empty($filename)) {
            return '文件不存在';
        }
        
        $backupDir = dirname(dirname(__DIR__)) . '/backups/';
        $filepath = $backupDir . $filename;
        
        if (!file_exists($filepath)) {
            return '文件不存在';
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit;
    }
}
