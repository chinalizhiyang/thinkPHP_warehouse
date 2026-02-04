<?php
namespace app\controller;

use app\model\OutboundHistory as OutboundHistoryModel;

class OutboundHistory
{
    // 显示出库历史列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索参数
        $keyword = $_GET['keyword'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        // 获取分页参数
        $page = $_GET['page'] ?? 1;
        $page_size = $_GET['page_size'] ?? 25;
        
        // 构建搜索条件
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = "(name LIKE ? OR category LIKE ? OR out_no LIKE ? OR receiver LIKE ? OR dept LIKE ?)";
            $search_value = "%$keyword%";
            $params = [$search_value, $search_value, $search_value, $search_value, $search_value];
        }
        
        if (!empty($start_date)) {
            $where[] = "DATE(out_time) >= ?";
            $params[] = $start_date;
        }
        
        if (!empty($end_date)) {
            $where[] = "DATE(out_time) <= ?";
            $params[] = $end_date;
        }
        
        // 获取出库历史列表
        $result = OutboundHistoryModel::getList($where, $params, $page, $page_size);
        
        // 计算总页码
        $total_pages = ceil($result['total'] / $result['page_size']);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染出库历史列表内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-history"></i> 出库历史</h3>
            </div>
            <div class="card-body">
                <!-- 搜索区域 -->
                <form action="/outbound-history" method="get" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">关键词搜索</label>
                                <input type="text" class="form-control" name="keyword" placeholder="物料名称、类别、出库单号、领用人或领用部门" value="<?php echo htmlspecialchars($keyword); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">开始日期</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="width: 100%;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">结束日期</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="width: 100%;">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100">搜索</button>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <a href="/outbound-history" class="btn btn-secondary w-100">重置</a>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <a href="/outbound-history/import-csv" class="btn btn-success w-100">导入CSV</a>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <a href="/outbound-history/export-csv" class="btn btn-primary w-100">导出CSV</a>
                            </div>
                        </div>
                    </div>
                </form>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>出库单号</th>
                            <th>出库时间</th>
                            <th>类别</th>
                            <th>物料名称</th>
                            <th>单位</th>
                            <th>数量</th>
                            <th>领用部门</th>
                            <th>领用人</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 计算起始序号
                        $start_index = ($page - 1) * $page_size + 1;
                        $index = $start_index;
                        foreach ($result['list'] as $item):
                        ?>
                        <tr>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo $item['out_no']; ?></td>
                            <td><?php echo $item['out_time']; ?></td>
                            <td><?php echo $item['category']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['unit']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['dept']; ?></td>
                            <td><?php echo $item['receiver']; ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="/outbound-history/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> 查看</a>
                                    <?php if (check_permission('outbound_manage')): ?>
                                    <a href="/outbound-history/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i> 编辑</a>
                                    <a href="/outbound-history/delete/<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这条记录吗？');"><i class="fa fa-trash"></i> 删除</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- 分页控件 -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="分页">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="/outbound-history?page=<?php echo $page - 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="上一页">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        // 显示页码逻辑
                        $show_pages = [];
                        if ($total_pages <= 7) {
                            $show_pages = range(1, $total_pages);
                        } else {
                            if ($page <= 4) {
                                $show_pages = range(1, 5);
                                $show_pages[] = -1;
                                $show_pages[] = $total_pages;
                            } elseif ($page >= $total_pages - 3) {
                                $show_pages = [1, -1];
                                $show_pages = array_merge($show_pages, range($total_pages - 4, $total_pages));
                            } else {
                                $show_pages = [1, -1];
                                $show_pages = array_merge($show_pages, range($page - 2, $page + 2));
                                $show_pages[] = -1;
                                $show_pages[] = $total_pages;
                            }
                        }
                        
                        foreach ($show_pages as $p):
                            if ($p == -1):
                        ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php else: ?>
                        <li class="page-item <?php echo $page == $p ? 'active' : ''; ?>">
                            <a class="page-link" href="/outbound-history?page=<?php echo $p; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"><?php echo $p; ?></a>
                        </li>
                        <?php endif; endforeach; ?>
                        
                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="/outbound-history?page=<?php echo $page + 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="下一页">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="text-center mt-2 mb-4">
                    <span class="text-muted">共 <?php echo $result['total']; ?> 条记录，每页 <?php echo $page_size; ?> 条，当前第 <?php echo $page; ?> 页/共 <?php echo $total_pages; ?> 页</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示出库历史列表页面
        return view('layout/main', [
            'title' => '出库历史',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'OutboundHistory'
        ]);
    }
    
    // 显示出库历史详情
    public function detail($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库历史详情
        $detail = OutboundHistoryModel::getById($id);
        
        if (!$detail) {
            redirect('/outbound-history', '出库历史记录不存在');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染出库历史详情内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-eye"></i> 出库历史详情</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ID</label>
                            <p><?php echo $detail['id']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>物料名称</label>
                            <p><?php echo $detail['name']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>类别</label>
                            <p><?php echo $detail['category']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>出库单号</label>
                            <p><?php echo $detail['out_no']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>数量</label>
                            <p><?php echo $detail['quantity']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>单位</label>
                            <p><?php echo $detail['unit']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>出库时间</label>
                            <p><?php echo $detail['out_time']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>领用人</label>
                            <p><?php echo $detail['receiver']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>领用部门</label>
                            <p><?php echo $detail['dept']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>备注</label>
                            <p><?php echo $detail['remark'] ?? '无'; ?></p>
                        </div>
                    </div>
                </div>
                <a href="/outbound-history" class="btn btn-secondary mt-3"><i class="fa fa-arrow-left"></i> 返回列表</a>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示出库历史详情页面
        return view('layout/main', [
            'title' => '出库历史详情',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'OutboundHistory'
        ]);
    }
    
    // 搜索出库历史
    public function search()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索条件
        $keyword = $_POST['keyword'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        
        // 获取分页参数
        $page = $_GET['page'] ?? 1;
        $page_size = $_GET['page_size'] ?? 25;
        
        // 搜索出库历史
        $result = OutboundHistoryModel::search($keyword, $start_date, $end_date, $page, $page_size);
        
        // 计算总页码
        $total_pages = ceil($result['total'] / $result['page_size']);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染出库历史搜索结果内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-search"></i> 出库历史搜索结果</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>物料名称</th>
                            <th>类别</th>
                            <th>数量</th>
                            <th>单位</th>
                            <th>出库时间</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['list'] as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['category']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['unit']; ?></td>
                            <td><?php echo $item['out_time']; ?></td>
                            <td><?php echo $item['created_at']; ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="/outbound-history/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> 查看</a>
                                    <?php if (check_permission('outbound_manage')): ?>
                                    <a href="/outbound-history/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i> 编辑</a>
                                    <a href="/outbound-history/delete/<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这条记录吗？');"><i class="fa fa-trash"></i> 删除</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示出库历史搜索结果页面
        return view('layout/main', [
            'title' => '出库历史搜索结果',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'OutboundHistory'
        ]);
    }
    
    // 编辑出库历史
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取出库历史详情
        $detail = OutboundHistoryModel::getById($id);
        
        if (!$detail) {
            redirect('/outbound-history', '出库历史记录不存在');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'out_no' => $_POST['out_no'] ?? '',
                'name' => $_POST['name'] ?? '',
                'category' => $_POST['category'] ?? '',
                'quantity' => $_POST['quantity'] ?? '',
                'unit' => $_POST['unit'] ?? '',
                'out_time' => $_POST['out_time'] ?? '',
                'receiver' => $_POST['receiver'] ?? '',
                'dept' => $_POST['dept'] ?? '',
                'remark' => $_POST['remark'] ?? ''
            ];
            
            // 更新出库历史
            $result = OutboundHistoryModel::update($id, $data);
            
            if ($result) {
                redirect('/outbound-history', '编辑成功');
            } else {
                redirect('/outbound-history/edit/' . $id, '编辑失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染编辑出库历史内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> 编辑出库历史</h3>
            </div>
            <div class="card-body">
                <form action="/outbound-history/edit/<?php echo $id; ?>" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="out_no" class="form-label">出库单号</label>
                                <input type="text" class="form-control" id="out_no" name="out_no" value="<?php echo $detail['out_no']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">物料名称</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $detail['name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">类别</label>
                                <input type="text" class="form-control" id="category" name="category" value="<?php echo $detail['category']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">数量</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $detail['quantity']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit" class="form-label">单位</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?php echo $detail['unit']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="out_time" class="form-label">出库时间</label>
                                <input type="datetime-local" class="form-control" id="out_time" name="out_time" value="<?php echo date('Y-m-d\TH:i', strtotime($detail['out_time'])); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="receiver" class="form-label">领用人</label>
                                <input type="text" class="form-control" id="receiver" name="receiver" value="<?php echo $detail['receiver']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="dept" class="form-label">领用部门</label>
                                <input type="text" class="form-control" id="dept" name="dept" value="<?php echo $detail['dept']; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="remark" class="form-label">备注</label>
                        <textarea class="form-control" id="remark" name="remark" rows="3"><?php echo $detail['remark'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/outbound-history" class="btn btn-secondary">取消</a>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示编辑出库历史页面
        return view('layout/main', [
            'title' => '编辑出库历史',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'OutboundHistory'
        ]);
    }
    
    // 删除出库历史
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 删除出库历史
        $result = OutboundHistoryModel::delete($id);
        
        if ($result) {
            redirect('/outbound-history', '删除成功');
        } else {
            redirect('/outbound-history', '删除失败');
        }
    }
    
    // 导入CSV文件
    public function importCsv()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];
            
            // 检查文件类型
            $allowed_types = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
            if (!in_array($file['type'], $allowed_types)) {
                redirect('/outbound-history', '文件类型不正确，请上传CSV文件');
            }
            
            // 检查文件大小（限制为5MB）
            if ($file['size'] > 5 * 1024 * 1024) {
                redirect('/outbound-history', '文件过大，请上传小于5MB的文件');
            }
            
            // 读取CSV文件
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                redirect('/outbound-history', '无法读取文件');
            }
            
            // 跳过第一行标题
            $header = fgetcsv($handle, 1000, ',');
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            
            // 逐行处理数据
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if (count($data) < 6) {
                    $error_count++;
                    $errors[] = "行数据不完整: " . implode(',', $data);
                    continue;
                }
                
                // 解析CSV数据
                $outbound_data = [
                    'out_no' => trim($data[0]),
                    'name' => trim($data[1]),
                    'category' => trim($data[2]),
                    'quantity' => intval(trim($data[3])),
                    'unit' => trim($data[4]),
                    'out_time' => trim($data[5]),
                    'dept' => isset($data[6]) ? trim($data[6]) : '',
                    'receiver' => isset($data[7]) ? trim($data[7]) : '',
                    'remark' => isset($data[8]) ? trim($data[8]) : ''
                ];
                
                // 验证必要字段
                if (empty($outbound_data['out_no']) || empty($outbound_data['name']) || $outbound_data['quantity'] <= 0) {
                    $error_count++;
                    $errors[] = "出库单号、物料名称和数量不能为空且数量必须大于0";
                    continue;
                }
                
                // 验证日期格式
                if (!empty($outbound_data['out_time']) && !strtotime($outbound_data['out_time'])) {
                    $error_count++;
                    $errors[] = "日期格式不正确: {$outbound_data['out_time']}";
                    continue;
                }
                
                // 检查出库单号是否已存在
                $existing_record = db_get_row("SELECT id FROM outbound_history WHERE out_no = ?", [$outbound_data['out_no']]);
                if ($existing_record) {
                    $error_count++;
                    $errors[] = "出库单号 {$outbound_data['out_no']} 已存在";
                    continue;
                }
                
                // 插入数据到数据库
                $sql = "INSERT INTO outbound_history (out_no, name, category, quantity, unit, out_time, dept, receiver, remark, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $result = db_exec($sql, [
                    $outbound_data['out_no'],
                    $outbound_data['name'],
                    $outbound_data['category'],
                    $outbound_data['quantity'],
                    $outbound_data['unit'],
                    $outbound_data['out_time'] ?: date('Y-m-d H:i:s'),
                    $outbound_data['dept'],
                    $outbound_data['receiver'],
                    $outbound_data['remark']
                ]);
                
                if ($result) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "插入数据失败: " . implode(',', array_slice($data, 0, 4));
                }
            }
            
            fclose($handle);
            
            // 记录操作日志
            \app\model\Record::addOperation([
                'user_id' => $_SESSION['user']['id'],
                'action' => 'import_outbound_history_csv',
                'target' => 'outbound_history',
                'content' => "导入出库历史CSV文件，成功: {$success_count}条，失败: {$error_count}条"
            ]);
            
            // 构建提示信息
            $message = "导入完成！成功: {$success_count}条，失败: {$error_count}条";
            if (!empty($errors)) {
                $message .= "<br>错误详情:<br>" . implode('<br>', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= '<br>...还有' . (count($errors) - 5) . '个错误';
                }
            }
            
            redirect('/outbound-history', $message);
        }
        
        // 显示导入页面
        $menu = get_nav_menu();
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-file-import"></i> 导出库历史CSV文件</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <h5><i class="fa fa-info-circle"></i> CSV文件格式说明：</h5>
                    <p class="mb-1">请按照以下格式准备CSV文件：</p>
                    <pre class="bg-light p-3 mb-0">出库单号,物料名称,物料类别,数量,单位,出库时间,部门,领用人,备注
OUT20240101001,螺丝,M3螺栓,50,个,2024-01-01 15:30:00,生产部,张三,生产急需</pre>
                    <small class="text-muted">* 前4列为必填项，其余为可选项</small>
                </div>
                
                <form action="/outbound-history/import-csv" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">选择CSV文件</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
                        <div class="form-text">支持CSV格式文件，最大5MB</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-upload"></i> 开始导入
                        </button>
                        <a href="/outbound-history" class="btn btn-secondary">
                            <i class="fa fa-times"></i> 取消
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        return view('layout/main', [
            'title' => '导出库历史CSV',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'OutboundHistory'
        ]);
    }
    
    // 导出CSV
    public function exportCsv()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('outbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索参数
        $keyword = $_GET['keyword'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        // 构建搜索条件
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = "(name LIKE ? OR category LIKE ? OR out_no LIKE ? OR receiver LIKE ? OR dept LIKE ?)";
            $search_value = "%$keyword%";
            $params = [$search_value, $search_value, $search_value, $search_value, $search_value];
        }
        
        if (!empty($start_date)) {
            $where[] = "out_time >= ?";
            $params[] = $start_date . ' 00:00:00';
        }
        
        if (!empty($end_date)) {
            $where[] = "out_time <= ?";
            $params[] = $end_date . ' 23:59:59';
        }
        
        // 获取所有符合条件的数据
        $result = OutboundHistoryModel::getList($where, $params, 1, 10000); // 获取最多10000条记录
        $data = $result['list'];
        
        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="outbound_history_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // 输出BOM以支持Excel正确显示中文
        echo "\xEF\xBB\xBF";
        
        // 创建文件句柄
        $output = fopen('php://output', 'w');
        
        // 写入表头
        fputcsv($output, ['序号', '出库单号', '出库时间', '类别', '物料名称', '单位', '数量', '领用部门', '领用人']);
        
        // 写入数据
        foreach ($data as $index => $item) {
            fputcsv($output, [
                $index + 1,
                $item['out_no'],
                $item['out_time'],
                $item['category'],
                $item['name'],
                $item['unit'],
                $item['quantity'],
                $item['dept'],
                $item['receiver']
            ]);
        }
        
        // 关闭文件句柄
        fclose($output);
        exit;
    }
}
