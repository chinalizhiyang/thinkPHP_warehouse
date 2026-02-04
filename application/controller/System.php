<?php
namespace app\controller;

use app\model\Record as RecordModel;

class System
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
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染操作记录内容
        ob_start();
        ?>
        <style>
        .operation-table td {
            vertical-align: middle;
        }
        .operation-table pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px;
            margin: 0;
        }
        .badge {
            font-size: 0.85em;
        }
        </style>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-list"></i> 操作记录</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="/record/system" class="btn btn-secondary">系统日志</a>
                    <a href="/record/backup" class="btn btn-secondary">数据备份</a>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cleanModal">清理记录</button>
                </div>
                <table class="table table-bordered table-striped operation-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户</th>
                            <th>操作</th>
                            <th>目标</th>
                            <th>内容</th>
                            <th>IP地址</th>
                            <th>操作时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo $record['id']; ?></td>
                            <td><?php echo $record['username']; ?></td>
                            <td>
                                <?php 
                                $action = $record['action'];
                                switch($action) {
                                    case 'login':
                                        echo '<span class="badge bg-success">登录</span>';
                                        break;
                                    case 'logout':
                                        echo '<span class="badge bg-secondary">登出</span>';
                                        break;
                                    case 'create':
                                    case 'add':
                                        echo '<span class="badge bg-primary">新增</span>';
                                        break;
                                    case 'update':
                                    case 'edit':
                                        echo '<span class="badge bg-warning">编辑</span>';
                                        break;
                                    case 'delete':
                                        echo '<span class="badge bg-danger">删除</span>';
                                        break;
                                    case 'import':
                                        echo '<span class="badge bg-info">导入</span>';
                                        break;
                                    case 'export':
                                        echo '<span class="badge bg-dark">导出</span>';
                                        break;
                                    case 'backup':
                                        echo '<span class="badge bg-warning">备份</span>';
                                        break;
                                    case 'restore':
                                        echo '<span class="badge bg-danger">恢复</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-light text-dark">' . htmlspecialchars($action) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                $target = $record['target'];
                                switch($target) {
                                    case 'user':
                                    case 'users':
                                        echo '<span class="badge bg-primary">用户管理</span>';
                                        break;
                                    case 'material':
                                    case 'materials':
                                        echo '<span class="badge bg-success">物料管理</span>';
                                        break;
                                    case 'inbound':
                                        echo '<span class="badge bg-info">入库管理</span>';
                                        break;
                                    case 'outbound':
                                        echo '<span class="badge bg-warning">出库管理</span>';
                                        break;
                                    case 'inventory':
                                        echo '<span class="badge bg-secondary">库存管理</span>';
                                        break;
                                    case 'system':
                                        echo '<span class="badge bg-dark">系统</span>';
                                        break;
                                    case 'backup':
                                        echo '<span class="badge bg-danger">数据备份</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-light text-dark">' . htmlspecialchars($target) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                // 格式化操作内容显示
                                $content = $record['content'];
                                if (strpos($content, '用户') !== false && strpos($content, '登录') !== false) {
                                    echo '<span class="badge bg-success">登录系统</span>';
                                } elseif (strpos($content, '添加') !== false) {
                                    echo '<span class="badge bg-primary">添加操作</span>';
                                } elseif (strpos($content, '修改') !== false || strpos($content, '编辑') !== false) {
                                    echo '<span class="badge bg-warning">修改操作</span>';
                                } elseif (strpos($content, '删除') !== false) {
                                    echo '<span class="badge bg-danger">删除操作</span>';
                                } elseif (strpos($content, '导入') !== false) {
                                    echo '<span class="badge bg-info">导入操作</span>';
                                } elseif (strpos($content, '导出') !== false) {
                                    echo '<span class="badge bg-secondary">导出操作</span>';
                                } else {
                                    // 如果是JSON格式的数据，美化显示
                                    if (substr($content, 0, 1) === '{' || substr($content, 0, 1) === '[') {
                                        $decoded = json_decode($content, true);
                                        if ($decoded) {
                                            echo '<pre class="mb-0" style="font-size: 12px; max-height: 100px; overflow-y: auto;">' . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                                        } else {
                                            echo '<span class="text-muted">复杂数据</span>';
                                        }
                                    } else {
                                        echo htmlspecialchars($content);
                                    }
                                }
                                ?>
                            </td>
                            <td><?php echo $record['ip']; ?></td>
                            <td><?php echo $record['created_at']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- 清理记录模态框 -->
        <div class="modal fade" id="cleanModal" tabindex="-1" aria-labelledby="cleanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/record/clean-operation" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cleanModalLabel">清理操作记录</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="days" class="form-label">保留天数</label>
                                <select class="form-control" id="days" name="days">
                                    <option value="7">7天</option>
                                    <option value="30" selected>30天</option>
                                    <option value="90">90天</option>
                                    <option value="365">1年</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-danger">确认清理</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示操作记录页面
        return view('layout/main', [
            'title' => '操作记录',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'System'
        ]);
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
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染系统日志内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-file-text"></i> 系统日志</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="/record" class="btn btn-secondary">操作记录</a>
                    <a href="/record/backup" class="btn btn-secondary">数据备份</a>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cleanModal">清理日志</button>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>级别</th>
                            <th>消息</th>
                            <th>数据</th>
                            <th>创建时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td>
                                <?php 
                                $level_class = '';
                                switch($log['level']) {
                                    case 'info': $level_class = 'bg-info'; break;
                                    case 'warning': $level_class = 'bg-warning'; break;
                                    case 'error': $level_class = 'bg-danger'; break;
                                    default: $level_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $level_class; ?>"><?php echo $log['level']; ?></span>
                            </td>
                            <td><?php echo $log['message']; ?></td>
                            <td><?php echo is_array($log['data']) ? json_encode($log['data'], JSON_UNESCAPED_UNICODE) : $log['data']; ?></td>
                            <td><?php echo $log['created_at']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- 清理日志模态框 -->
        <div class="modal fade" id="cleanModal" tabindex="-1" aria-labelledby="cleanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/record/clean-system" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cleanModalLabel">清理系统日志</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="days" class="form-label">保留天数</label>
                                <select class="form-control" id="days" name="days">
                                    <option value="7">7天</option>
                                    <option value="30" selected>30天</option>
                                    <option value="90">90天</option>
                                    <option value="365">1年</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-danger">确认清理</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示系统日志页面
        return view('layout/main', [
            'title' => '系统日志',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'System'
        ]);
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
        
        // 设置时区为中国上海
        date_default_timezone_set('Asia/Shanghai');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 执行数据备份
            $backup = RecordModel::backupData();
            
            if ($backup) {
                // 备份成功后重定向并添加时间戳防止缓存
                $timestamp = time();
                redirect("/record/backup?t=$timestamp", '备份成功');
            } else {
                redirect('record/backup', '备份失败');
            }
        }
        
        // 获取备份列表
        $backups = RecordModel::getBackupList();
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染数据备份内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-database"></i> 数据备份</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="/record" class="btn btn-secondary">操作记录</a>
                    <a href="/record/system" class="btn btn-secondary">系统日志</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#backupModal">创建备份</button>
                </div>
                
                <h4 class="mb-3">备份列表</h4>
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>备份文件</th>
                            <th>大小</th>
                            <th>备份时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo $backup['file']; ?></td>
                            <td><?php echo $backup['size']; ?></td>
                            <td class="backup-time" data-timestamp="<?php echo strtotime($backup['time']); ?>">
                                <?php echo $backup['time']; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/record/download/<?php echo $backup['file']; ?>" class="btn btn-sm btn-primary">下载</a>
                                    <a href="/record/restore/<?php echo $backup['file']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('确定要恢复这个备份吗？这将覆盖当前数据库数据！');">恢复</a>
                                    <a href="/record/delete/<?php echo $backup['file']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这个备份吗？');">删除</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        
        <!-- 创建备份模态框 -->
        <div class="modal fade" id="backupModal" tabindex="-1" aria-labelledby="backupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/record/backup" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="backupModalLabel">创建数据备份</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">备份说明</label>
                                <p class="text-muted">点击确认将创建完整的数据库备份文件</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary">确认备份</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- JavaScript增强时间显示 -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 更新备份时间显示
            function updateBackupTimes() {
                const timeElements = document.querySelectorAll('.backup-time');
                timeElements.forEach(element => {
                    const timestamp = parseInt(element.dataset.timestamp);
                    if (timestamp) {
                        // 创建本地时间显示
                        const date = new Date(timestamp * 1000);
                        const formattedTime = date.getFullYear() + '-' + 
                            String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(date.getDate()).padStart(2, '0') + ' ' + 
                            String(date.getHours()).padStart(2, '0') + ':' + 
                            String(date.getMinutes()).padStart(2, '0') + ':' + 
                            String(date.getSeconds()).padStart(2, '0');
                        
                        // 更新显示文本
                        if (element.textContent.trim() !== formattedTime) {
                            element.textContent = formattedTime;
                        }
                    }
                });
            }
            
            // 页面加载时更新一次
            updateBackupTimes();
            
            // 每秒更新时间显示（可选）
            // setInterval(updateBackupTimes, 1000);
            
            // 表单提交后刷新页面确保显示最新时间
            const backupForm = document.querySelector('form[action="/record/backup"]');
            if (backupForm) {
                backupForm.addEventListener('submit', function() {
                    // 在提交前记录当前时间
                    const submitTime = Math.floor(Date.now() / 1000);
                    this.dataset.submitTime = submitTime;
                });
            }
        });
        </script>
        <?php
        $content = ob_get_clean();
        
        // 显示数据备份页面
        return view('layout/main', [
            'title' => '数据备份',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'System'
        ]);
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
            redirect('/record', '清理成功');
        } else {
            redirect('/record', '清理失败');
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
            redirect('/record/system', '清理成功');
        } else {
            redirect('/record/system', '清理失败');
        }
    }
    
    // 下载备份文件
    public function download($filename)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 实际项目中应该从备份目录读取文件并提供下载
        // 这里只是演示
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        echo "备份文件内容: $filename";
        exit;
    }
    
    // 恢复数据库
    public function restore($filename)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 执行数据库恢复
        $result = RecordModel::restoreDatabase($filename);
        
        if ($result) {
            // 记录操作日志
            $log_data = [
                'user_id' => $_SESSION['user']['id'] ?? 0,
                'username' => $_SESSION['user']['username'] ?? 'unknown',
                'action' => '数据库恢复',
                'target' => '系统',
                'content' => "用户 {$_SESSION['user']['username']} 恢复了数据库备份: $filename",
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            RecordModel::addOperation($log_data);
            
            redirect('/record/backup', '数据库恢复成功');
        } else {
            redirect('/record/backup', '数据库恢复失败');
        }
    }
    
    // 删除备份文件
    public function delete($filename)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('record_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除备份文件
        $result = RecordModel::deleteBackup($filename);
        
        if ($result) {
            redirect('/record/backup', '备份文件已删除');
        } else {
            redirect('/record/backup', '删除备份文件失败');
        }
    }
}
