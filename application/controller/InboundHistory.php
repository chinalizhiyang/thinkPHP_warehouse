<?php
namespace app\controller;

use app\model\InboundHistory as InboundHistoryModel;

class InboundHistory
{
    // 显示入库历史列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
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
            $where[] = "(name LIKE ? OR category LIKE ? OR in_no LIKE ? OR purchaser LIKE ?)";
            $search_value = "%$keyword%";
            $params = [$search_value, $search_value, $search_value, $search_value];
        }
        
        if (!empty($start_date)) {
            $where[] = "in_time >= ?";
            $params[] = $start_date . ' 00:00:00';
        }
        
        if (!empty($end_date)) {
            $where[] = "in_time <= ?";
            $params[] = $end_date . ' 23:59:59';
        }
        
        // 获取入库历史列表
        $result = InboundHistoryModel::getList($where, $params, $page, $page_size);
        
        // 计算总页码
        $total_pages = ceil($result['total'] / $result['page_size']);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染入库历史列表内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-history"></i> 入库历史</h3>
            </div>
            <div class="card-body">
                <!-- 搜索区域 -->
                <form action="/inbound-history" method="get" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">关键词搜索</label>
                                <input type="text" class="form-control" name="keyword" placeholder="物料名称、类别、入库单号或采购人" value="<?php echo htmlspecialchars($keyword); ?>">
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
                                <a href="/inbound-history" class="btn btn-secondary w-100">重置</a>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <a href="/inbound-history/export-csv" class="btn btn-success w-100">导出CSV</a>
                            </div>
                        </div>
                    </div>
                </form>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>入库单号</th>
                            <th>入库时间</th>
                            <th>类别</th>
                            <th>物料名称</th>
                            <th>单位</th>
                            <th>数量</th>
                            <th>采购人</th>
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
                            <td><?php echo $item['in_no']; ?></td>
                            <td><?php echo $item['in_time']; ?></td>
                            <td><?php echo $item['category']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['unit']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['purchaser']; ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="/inbound-history/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> 查看</a>
                                    <?php if (check_permission('inbound_manage')): ?>
                                    <a href="/inbound-history/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i> 编辑</a>
                                    <a href="/inbound-history/delete/<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这条记录吗？');"><i class="fa fa-trash"></i> 删除</a>
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
                            <a class="page-link" href="/inbound-history?page=<?php echo $page - 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="上一页">
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
                            <a class="page-link" href="/inbound-history?page=<?php echo $p; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"><?php echo $p; ?></a>
                        </li>
                        <?php endif; endforeach; ?>
                        
                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="/inbound-history?page=<?php echo $page + 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" aria-label="下一页">
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
        
        // 显示入库历史列表页面
        return view('layout/main', [
            'title' => '入库历史',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'InboundHistory'
        ]);
    }
    
    // 显示入库历史详情
    public function detail($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库历史详情
        $detail = InboundHistoryModel::getById($id);
        
        if (!$detail) {
            redirect('/inbound-history', '入库历史记录不存在');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染入库历史详情内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-eye"></i> 入库历史详情</h3>
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
                            <label>入库单号</label>
                            <p><?php echo $detail['in_no']; ?></p>
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
                            <label>入库时间</label>
                            <p><?php echo $detail['in_time']; ?></p>
                        </div>
                        <div class="form-group">
                            <label>采购员</label>
                            <p><?php echo $detail['purchaser']; ?></p>
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
                <a href="/inbound-history" class="btn btn-secondary mt-3"><i class="fa fa-arrow-left"></i> 返回列表</a>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示入库历史详情页面
        return view('layout/main', [
            'title' => '入库历史详情',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'InboundHistory'
        ]);
    }
    
    // 搜索入库历史
    public function search()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索条件
        $keyword = $_POST['keyword'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        
        // 获取分页参数
        $page = $_GET['page'] ?? 1;
        $page_size = $_GET['page_size'] ?? 25;
        
        // 搜索入库历史
        $result = InboundHistoryModel::search($keyword, $start_date, $end_date, $page, $page_size);
        
        // 计算总页码
        $total_pages = ceil($result['total'] / $result['page_size']);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染入库历史列表内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-search"></i> 入库历史搜索结果</h3>
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
                            <th>入库时间</th>
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
                            <td><?php echo $item['in_time']; ?></td>
                            <td><?php echo $item['created_at']; ?></td>
                            <td>
                                <a href="/inbound-history/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> 查看</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示入库历史搜索结果页面
        return view('layout/main', [
            'title' => '入库历史搜索结果',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'InboundHistory'
        ]);
    }
    
    // 编辑入库历史
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库历史详情
        $detail = InboundHistoryModel::getById($id);
        
        if (!$detail) {
            redirect('/inbound-history', '入库历史记录不存在');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'in_no' => $_POST['in_no'] ?? '',
                'name' => $_POST['name'] ?? '',
                'category' => $_POST['category'] ?? '',
                'quantity' => $_POST['quantity'] ?? '',
                'unit' => $_POST['unit'] ?? '',
                'in_time' => $_POST['in_time'] ?? '',
                'purchaser' => $_POST['purchaser'] ?? '',
                'remark' => $_POST['remark'] ?? ''
            ];
            
            // 更新入库历史
            $result = InboundHistoryModel::update($id, $data);
            
            if ($result) {
                redirect('/inbound-history', '编辑成功');
            } else {
                redirect('/inbound-history/edit/' . $id, '编辑失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染编辑入库历史内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> 编辑入库历史</h3>
            </div>
            <div class="card-body">
                <form action="/inbound-history/edit/<?php echo $id; ?>" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="in_no" class="form-label">入库单号</label>
                                <input type="text" class="form-control" id="in_no" name="in_no" value="<?php echo $detail['in_no']; ?>" required>
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
                                <label for="in_time" class="form-label">入库时间</label>
                                <input type="datetime-local" class="form-control" id="in_time" name="in_time" value="<?php echo date('Y-m-d\TH:i', strtotime($detail['in_time'])); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="purchaser" class="form-label">采购员</label>
                                <input type="text" class="form-control" id="purchaser" name="purchaser" value="<?php echo $detail['purchaser']; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="remark" class="form-label">备注</label>
                        <textarea class="form-control" id="remark" name="remark" rows="3"><?php echo $detail['remark'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/inbound-history" class="btn btn-secondary">取消</a>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // 显示编辑入库历史页面
        return view('layout/main', [
            'title' => '编辑入库历史',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'InboundHistory'
        ]);
    }
    
    // 删除入库历史
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
            redirect('/', '无权限访问');
        }
        
        // 删除入库历史
        $result = InboundHistoryModel::delete($id);
        
        if ($result) {
            redirect('/inbound-history', '删除成功');
        } else {
            redirect('/inbound-history', '删除失败');
        }
    }
    
    // 导出CSV
    public function exportCsv()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('inbound_history')) {
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
            $where[] = "(name LIKE ? OR category LIKE ? OR in_no LIKE ? OR purchaser LIKE ?)";
            $search_value = "%$keyword%";
            $params = [$search_value, $search_value, $search_value, $search_value];
        }
        
        if (!empty($start_date)) {
            $where[] = "in_time >= ?";
            $params[] = $start_date . ' 00:00:00';
        }
        
        if (!empty($end_date)) {
            $where[] = "in_time <= ?";
            $params[] = $end_date . ' 23:59:59';
        }
        
        // 获取所有符合条件的数据
        $result = InboundHistoryModel::getList($where, $params, 1, 10000); // 获取最多10000条记录
        $data = $result['list'];
        
        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="inbound_history_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // 输出BOM以支持Excel正确显示中文
        echo "\xEF\xBB\xBF";
        
        // 创建文件句柄
        $output = fopen('php://output', 'w');
        
        // 写入表头
        fputcsv($output, ['序号', '入库单号', '物料名称', '类别', '数量', '单位', '入库时间', '采购人']);
        
        // 写入数据
        foreach ($data as $index => $item) {
            fputcsv($output, [
                $index + 1,
                $item['in_no'],
                $item['name'],
                $item['category'],
                $item['quantity'],
                $item['unit'],
                $item['in_time'],
                $item['purchaser']
            ]);
        }
        
        // 关闭文件句柄
        fclose($output);
        exit;
    }
}
