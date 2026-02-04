<?php
namespace app\controller;

use app\model\Inventory as InventoryModel;
use app\model\Material as MaterialModel;

class Inventory
{
    // 库存列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索参数
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $date = isset($_GET['date']) ? $_GET['date'] : '';
        
        // 获取分页参数
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page_size = 25;
        
        // 构建搜索条件
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(material_code LIKE ? OR name LIKE ? OR spec LIKE ? OR unit LIKE ? OR category LIKE ?)";
            $search_value = "%$search%";
            $params = array_fill(0, 5, $search_value);
        }
        
        if (!empty($date)) {
            $where[] = "DATE(updated_at) = ?";
            $params[] = $date;
        }
        
        // 获取库存列表
        $result = InventoryModel::getList($where, $params, $page, $page_size);
        $inventories = $result['data'];
        $total = $result['total'];
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染库存列表内容
        $content = $this->renderInventoryListContent($inventories, $search, $date, $total, $page, $page_size);
        
        // 显示库存列表页面
        return view('layout/main', [
            'title' => '库存管理',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inventory'
        ]);
    }
    
    // 渲染库存列表内容
    private function renderInventoryListContent($inventories, $search = '', $date = '', $total = 0, $page = 1, $page_size = 25)
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3>库存管理</h3>
            </div>
            <div class="card-body">
                <!-- 搜索区域 -->
                <form action="/inventory" method="get" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="搜索物料编号、名称、规格或单位" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date); ?>">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-secondary w-100">快照日期</button>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">查询</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-secondary w-100" onclick="location.href='/inventory'">重置</button>
                        </div>
                    </div>
                </form>
                
                <!-- 操作按钮 -->
                <div class="mb-4">
                    <a href="/inventory/report" class="btn btn-warning mr-2">查看库存报表</a>
                    <button type="button" class="btn btn-success">导出库存CSV</button>
                </div>
                
                <!-- 库存表格 -->
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>物料编号</th>
                            <th>类别</th>
                            <th>物料名称</th>
                            <th>规格</th>
                            <th>单位</th>
                            <?php if (check_permission('inventory_price_view')): ?><th>单价</th><?php endif; ?>
                            <th>当前库存</th>
                            <th>状态</th>
                            <th>更新时间</th>
                            <th>仓位</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventories as $inventory): ?>
                        <tr <?php if ($inventory['stock'] == 0): ?>class="bg-danger bg-opacity-10"<?php endif; ?>>
                            <td><?php echo $inventory['material_code']; ?></td>
                            <td><?php echo $inventory['category_name']; ?></td>
                            <td><?php echo $inventory['material_name']; ?></td>
                            <td><?php echo $inventory['spec'] ?? 'None'; ?></td>
                            <td><?php echo $inventory['unit']; ?></td>
                            <?php if (check_permission('inventory_price_view')): ?><td><?php echo $inventory['price']; ?></td><?php endif; ?>
                            <td><?php echo $inventory['stock']; ?></td>
                            <td>
                                <?php if ($inventory['stock'] == 0): ?>
                                    <span class="badge bg-danger">库存为0</span>
                                <?php else: ?>
                                    <span class="badge bg-success">正常</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $inventory['updated_at']; ?></td>
                            <td><?php echo $inventory['location'] ?? '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- 分页导航 -->
                <div class="mt-3">
                    <?php $this->renderPagination($total, $page, $page_size, ['search' => $search, 'date' => $date]); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 库存盘点
    public function check()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'total_amount' => $_POST['total_amount'] ?? 0
            ];
            
            // 创建盘点单
            $check = InventoryModel::createCheck($data);
            
            if ($check) {
                // 处理盘点明细
                $material_ids = $_POST['material_id'] ?? [];
                $system_stocks = $_POST['system_stock'] ?? [];
                $actual_stocks = $_POST['actual_stock'] ?? [];
                $prices = $_POST['price'] ?? [];
                
                foreach ($material_ids as $key => $material_id) {
                    if (!empty($material_id)) {
                        // 获取物料信息
                        $material = MaterialModel::getById($material_id);
                        
                        $difference = $actual_stocks[$key] - $system_stocks[$key];
                        $amount = $difference * $prices[$key];
                        
                        $detail_data = [
                            'check_id' => $check['id'],
                            'material_id' => $material_id,
                            'material_name' => $material['name'],
                            'material_code' => $material['code'],
                            'unit' => $material['unit'],
                            'system_stock' => $system_stocks[$key],
                            'actual_stock' => $actual_stocks[$key],
                            'difference' => $difference,
                            'price' => $prices[$key],
                            'amount' => $amount
                        ];
                        
                        // 创建盘点明细
                        InventoryModel::createCheckDetail($detail_data);
                        
                        // 更新库存
                        if ($difference != 0) {
                            InventoryModel::updateStock($material_id, $actual_stocks[$key]);
                        }
                    }
                }
                
                redirect('inventory', '盘点成功');
            } else {
                redirect('inventory/check', '盘点失败');
            }
        }
        
        // 获取物料列表
        $materials = MaterialModel::getList();
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染库存盘点内容
        $content = $this->renderInventoryCheckContent($materials);
        
        // 显示库存盘点页面
        return view('layout/main', [
            'title' => '库存盘点',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inventory'
        ]);
    }
    
    // 渲染库存盘点内容
    private function renderInventoryCheckContent($materials)
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-check-circle"></i> 库存盘点</h3>
            </div>
            <div class="card-body">
                <form action="/inventory/check" method="post">
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">总金额</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01" required>
                    </div>
                    
                    <h4 class="mt-4 mb-3">盘点明细</h4>
                    <div id="check-details">
                        <?php foreach ($materials as $material): ?>
                        <div class="row mb-3 check-detail-item">
                            <div class="col-md-2">
                                <label class="form-label">物料</label>
                                <input type="hidden" name="material_id[]" value="<?php echo $material['id']; ?>">
                                <input type="text" class="form-control" value="<?php echo $material['name']; ?> (<?php echo $material['code']; ?>)" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">系统库存</label>
                                <input type="hidden" name="system_stock[]" value="<?php echo $material['stock']; ?>">
                                <input type="number" class="form-control" value="<?php echo $material['stock']; ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">实际库存</label>
                                <input type="number" class="form-control" name="actual_stock[]" value="<?php echo $material['stock']; ?>" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">单价</label>
                                <input type="hidden" name="price[]" value="<?php echo $material['price']; ?>">
                                <input type="number" class="form-control" value="<?php echo $material['price']; ?>" readonly>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">保存盘点单</button>
                    <a href="/inventory" class="btn btn-secondary">取消</a>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 库存报表
    public function report()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取分页参数
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page_size = 25;
        
        $params = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'page' => $page,
            'page_size' => $page_size
        ];
        
        // 生成库存报表
        $report = InventoryModel::generateReport($params);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染库存报表内容
        $content = $this->renderInventoryReportContent($report, $params);
        
        // 显示库存报表页面
        return view('layout/main', [
            'title' => '库存报表',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inventory'
        ]);
    }
    
    // 渲染库存报表内容
    private function renderInventoryReportContent($report, $params)
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-file-text-o"></i> 库存报表</h3>
            </div>
            <div class="card-body">
                <form action="/inventory/report" method="get">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">开始日期</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $params['start_date']; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">结束日期</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $params['end_date']; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">生成报表</button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-secondary w-100" onclick="location.href='/inventory/report'">重置</button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <a href="/inventory/export-csv?start_date=<?php echo $params['start_date']; ?>&end_date=<?php echo $params['end_date']; ?>" class="btn btn-success w-100">
                                <i class="fa fa-download"></i> 导出CSV
                            </a>
                        </div>
                    </div>
                </form>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>物料编码</th>
                            <th>物料名称</th>
                            <th>规格</th>
                            <th>单位</th>
                            <th>期初库存</th>
                            <th>本期入库</th>
                            <th>本期出库</th>
                            <th>期末库存</th>
                            <th>单价</th>
                            <th>仓位</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['details'] as $item): ?>
                        <tr>
                            <td><?php echo $item['material_code']; ?></td>
                            <td><?php echo $item['material_name']; ?></td>
                            <td><?php echo $item['spec']; ?></td>   
                            <td><?php echo $item['unit']; ?></td>
                            <td><?php echo $item['stock']; ?></td> <!-- 期初库存 -->
                            <td><?php echo $item['inbound']; ?></td> <!-- 本期入库 -->
                            <td><?php echo $item['outbound']; ?></td> <!-- 本期出库 -->
                            <td><?php echo $item['end_stock']; ?></td> <!-- 期末库存 -->
                            <td><?php echo $item['price']; ?></td>
                            <td><?php echo $item['location'] ?? '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                
                <!-- 分页导航 -->
                <div class="mt-3">
                    <?php $this->renderPagination($report['total'], $params['page'], $params['page_size'], $params); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 渲染分页导航
    private function renderPagination($total, $page, $page_size, $params)
    {
        $total_pages = ceil($total / $page_size);
        
        if ($total_pages <= 1) {
            return;
        }
        
        // 构建查询参数
        $query_params = [];
        if (isset($params['start_date'])) {
            $query_params[] = 'start_date=' . urlencode($params['start_date']);
        }
        if (isset($params['end_date'])) {
            $query_params[] = 'end_date=' . urlencode($params['end_date']);
        }
        $query_string = implode('&', $query_params);
        $query_string = $query_string ? '?' . $query_string : '';
        
        echo '<nav aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        
        // 上一页
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . $query_string . ($query_string ? '&' : '?') . 'page=' . ($page - 1) . '">上一页</a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">上一页</a></li>';
        }
        
        // 首页
        if ($page > 3) {
            echo '<li class="page-item"><a class="page-link" href="' . $query_string . ($query_string ? '&' : '?') . 'page=1">1</a></li>';
            if ($page > 4) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // 中间页
        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
            if ($i == $page) {
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="' . $query_string . ($query_string ? '&' : '?') . 'page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        // 末页
        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="' . $query_string . ($query_string ? '&' : '?') . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
        }
        
        // 下一页
        if ($page < $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="' . $query_string . ($query_string ? '&' : '?') . 'page=' . ($page + 1) . '">下一页</a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">下一页</a></li>';
        }
        
        echo '</ul>';
        echo '</nav>';
    }
    
    // 导出库存报表为CSV
    public function exportCsv()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inventory_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取参数
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        // 生成完整报表数据（不分页）
        $params = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'page' => 1,
            'page_size' => 10000  // 设置一个大数值以获取所有数据
        ];
        
        $report = InventoryModel::generateReport($params);
        
        // 设置CSV文件头部
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="库存报表_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // 输出BOM以确保Excel能正确识别UTF-8编码
        echo "\xEF\xBB\xBF";
        
        // 创建输出流
        $output = fopen('php://output', 'w');
        
        // 写入表头
        fputcsv($output, ['物料编码', '物料名称', '规格', '单位', '期初库存', '本期入库', '本期出库', '期末库存', '单价', '仓位']);
        
        // 写入数据行
        foreach ($report['details'] as $item) {
            fputcsv($output, [
                $item['material_code'],
                $item['material_name'],
                $item['spec'] ?? '',
                $item['unit'],
                $item['stock'],
                $item['inbound'],
                $item['outbound'],
                $item['end_stock'],
                $item['price'],
                $item['location'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit;
    }
}
