<?php
namespace app\controller;

use app\model\Inbound as InboundModel;
use app\model\Material as MaterialModel;

class Inbound
{
    // 入库单列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取分页参数
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page_size = 25;
        
        // 获取搜索参数
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // 获取入库单列表
        $result = InboundModel::getList([], $page, $page_size, $search);
        $inbounds = $result['data'];
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染入库单列表内容
        $content = $this->renderInboundListContent($inbounds, $result['total'], $page, $page_size, $search);
        
        // 显示入库单列表页面
        return view('layout/main', [
            'title' => '入库单列表',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inbound'
        ]);
    }
    
    // 渲染入库单列表内容
    private function renderInboundListContent($inbounds, $total, $page, $page_size, $search = '')
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3 class="mb-3"><i class="fa fa-sign-in"></i> 入库单列表</h3>
                <div class="d-flex justify-content-between align-items-center">
                    <form class="form-inline" method="get" action="/inbound">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="搜索物料编码、名称、类别等" value="<?php echo htmlspecialchars($search); ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                                <button type="button" class="btn btn-secondary" onclick="location.href='/inbound'" title="重置搜索"><i class="fa fa-refresh"></i> 重置</button>
                            </div>
                        </div>
                    </form>
                    <a href="/inbound/add" class="btn btn-primary ml-2"><i class="fa fa-plus"></i> 添加入库单</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>物料编码</th>
                            <th>类别</th>
                            <th>物料名称</th>
                            <th>规格</th>
                            <th>单位</th>
                            <th>数量</th>
                            <th>单价</th>
                            <th>入库日期</th>
                            <th>采购人</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inbounds as $inbound): ?>
                        <tr>
                            <td><?php echo $inbound['id']; ?></td>
                            <td><?php echo $inbound['material_code']; ?></td>
                            <td><?php echo $inbound['category']; ?></td>
                            <td><?php echo $inbound['material_name']; ?></td>
                            <td><?php echo $inbound['spec']; ?></td>
                            <td><?php echo $inbound['unit']; ?></td>
                            <td><?php echo $inbound['quantity']; ?></td>
                            <td><?php echo $inbound['price']; ?></td>
                            <td><?php echo $inbound['in_time']; ?></td>
                            <td><?php echo $inbound['supplier']; ?></td>
                            <td>
                                <a href="/inbound/edit/<?php echo $inbound['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i> 编辑</a>
                                <a href="/inbound/delete/<?php echo $inbound['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗？');"><i class="fa fa-trash"></i> 删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- 分页导航 -->
                <div class="mt-3">
                    <?php $this->renderPagination($total, $page, $page_size, $search); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 渲染分页导航
    private function renderPagination($total, $page, $page_size, $search = '')
    {
        $total_pages = ceil($total / $page_size);
        
        if ($total_pages <= 1) {
            return;
        }
        
        // 构建搜索参数
        $search_param = $search ? '&search=' . urlencode($search) : '';
        
        echo '<nav aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        
        // 上一页
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . $search_param . '">上一页</a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">上一页</a></li>';
        }
        
        // 首页
        if ($page > 3) {
            echo '<li class="page-item"><a class="page-link" href="?page=1' . $search_param . '">1</a></li>';
            if ($page > 4) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // 中间页
        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
            if ($i == $page) {
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                echo '<li class="page-item"><a class="page-link" href="?page=' . $i . $search_param . '">' . $i . '</a></li>';
            }
        }
        
        // 末页
        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $search_param . '">' . $total_pages . '</a></li>';
        }
        
        // 下一页
        if ($page < $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . $search_param . '">下一页</a></li>';
        } else {
            echo '<li class="page-item disabled"><a class="page-link" href="#">下一页</a></li>';
        }
        
        echo '</ul>';
        echo '</nav>';
    }
    
    // 添加入库单
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 直接将所有POST数据传递给模型
            $result = InboundModel::create($_POST);
            
            if ($result) {
                redirect('inbound', '添加成功');
            } else {
                redirect('inbound/add', '添加失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染添加入库单内容
        $content = $this->renderAddInboundContent();
        
        // 显示添加入库单页面
        return view('layout/main', [
            'title' => '添加入库单',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inbound'
        ]);
    }
    
    // 渲染添加入库单内容
    private function renderAddInboundContent()
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-plus"></i> 添加入库单</h3>
            </div>
            <div class="card-body">
                <form action="/inbound/add" method="post">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="in_no" class="form-label">入库单号</label>
                            <input type="text" class="form-control" id="in_no" name="in_no" required>
                        </div>
                        <div class="col-md-4">
                            <label for="in_time" class="form-label">入库日期</label>
                            <input type="date" class="form-control" id="in_time" name="in_time" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="purchaser" class="form-label">采购人</label>
                            <input type="text" class="form-control" id="purchaser" name="purchaser" required>
                        </div>
                    </div>
                    
                    <h4 class="mb-2">物料列表</h4>
                    <div id="inbound-details">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>物料编号</th>
                                    <th>类别</th>
                                    <th>物料名称</th>
                                    <th>规格</th>
                                    <th>单位</th>
                                    <th>单价</th>
                                    <th>数量</th>
                                    <th>备注</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="inbound-detail-item">
                                    <td>1</td>
                                    <td>
                                        <input type="text" class="form-control material_code" name="material_code[]" placeholder="请输入物料编号" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control category" name="category[]" required readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control material_name" name="material_name[]" required readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control spec" name="spec[]" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control unit" name="unit[]" required readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control price" name="price[]" step="0.01" required readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity" name="quantity[]" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control remark" name="remark[]">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-detail">删除</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-2 mb-3">
                        <button type="button" id="add-detail" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> 添加行</button>
                    </div>
                    
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm">保存</button>
                        <a href="/inbound" class="btn btn-secondary btn-sm">取消</a>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            // 添加明细行
            document.getElementById('add-detail').addEventListener('click', function() {
                const tbody = document.querySelector('#inbound-details tbody');
                const lastRow = tbody.querySelector('.inbound-detail-item:last-child');
                const newRow = lastRow.cloneNode(true);
                
                // 清空新行的输入值
                const inputs = newRow.querySelectorAll('input');
                inputs.forEach(input => {
                    input.value = '';
                });
                
                // 更新序号
                const rowCount = tbody.querySelectorAll('.inbound-detail-item').length + 1;
                newRow.querySelector('td:first-child').textContent = rowCount;
                
                tbody.appendChild(newRow);
            });
            
            // 删除明细行
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-detail')) {
                    const row = e.target.closest('.inbound-detail-item');
                    const tbody = row.closest('tbody');
                    const rows = tbody.querySelectorAll('.inbound-detail-item');
                    
                    if (rows.length > 1) {
                        row.remove();
                        
                        // 更新序号
                        const updatedRows = tbody.querySelectorAll('.inbound-detail-item');
                        updatedRows.forEach((r, index) => {
                            r.querySelector('td:first-child').textContent = index + 1;
                        });
                    }
                }
            });
            
            // 自动填充物料信息
            document.addEventListener('blur', function(e) {
                if (e.target.classList.contains('material_code')) {
                    const materialCodeInput = e.target;
                    const row = materialCodeInput.closest('.inbound-detail-item');
                    
                    if (materialCodeInput.value.trim() !== '') {
                        // 发送AJAX请求获取物料信息
                        fetch('/material/get-by-code/' + encodeURIComponent(materialCodeInput.value))
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.material) {
                                    // 填充物料信息
                                    row.querySelector('.category').value = data.material.category || '';
                                    row.querySelector('.material_name').value = data.material.name || '';
                                    row.querySelector('.spec').value = data.material.spec || '';
                                    row.querySelector('.unit').value = data.material.unit || '';
                                    row.querySelector('.price').value = data.material.price || '';
                                } else {
                                    alert('未找到物料编号为 ' + materialCodeInput.value + ' 的物料信息');
                                    
                                    // 清空已填充的字段
                                    row.querySelector('.category').value = '';
                                    row.querySelector('.material_name').value = '';
                                    row.querySelector('.spec').value = '';
                                    row.querySelector('.unit').value = '';
                                    row.querySelector('.price').value = '';
                                }
                            })
                            .catch(error => {
                                console.error('获取物料信息失败:', error);
                                alert('获取物料信息失败');
                            });
                    } else {
                        // 如果物料编号为空，清空相关字段
                        row.querySelector('.category').value = '';
                        row.querySelector('.material_name').value = '';
                        row.querySelector('.spec').value = '';
                        row.querySelector('.unit').value = '';
                        row.querySelector('.price').value = '';
                    }
                }
            }, true); // 使用捕获阶段来处理动态添加的元素
        </script>
        <?php
        return ob_get_clean();
    }
    
    // 编辑入库单
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取入库单信息
        $inbound = InboundModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'material_code' => $_POST['material_code'] ?? '',
                'category' => $_POST['category'] ?? '',
                'material_name' => $_POST['material_name'] ?? '',
                'spec' => $_POST['spec'] ?? '',
                'unit' => $_POST['unit'] ?? '',
                'quantity' => $_POST['quantity'] ?? 0,
                'price' => $_POST['price'] ?? 0,
                'in_time' => $_POST['in_time'] ?? date('Y-m-d H:i:s'),
                'handler' => $_POST['handler'] ?? '',
                'supplier' => $_POST['supplier'] ?? ''
            ];
            
            // 更新入库单
            $result = InboundModel::update($id, $data);
            
            if ($result) {
                redirect('inbound', '编辑成功');
            } else {
                redirect('inbound/edit/' . $id, '编辑失败');
            }
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染编辑入库单内容
        $content = $this->renderEditInboundContent($inbound);
        
        // 显示编辑入库单页面
        return view('layout/main', [
            'title' => '编辑入库单',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Inbound'
        ]);
    }
    
    // 渲染编辑入库单内容
    private function renderEditInboundContent($inbound)
    {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> 编辑入库单</h3>
            </div>
            <div class="card-body">
                <form action="/inbound/edit/<?php echo $inbound['id']; ?>" method="post">
                    <div class="bg-light p-4 rounded mb-4">                         
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="material_code" class="form-label">物料编码</label>
                                <input type="text" class="form-control" id="material_code" name="material_code" value="<?php echo $inbound['material_code']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">类别</label>
                                <input type="text" class="form-control" id="category" name="category" value="<?php echo $inbound['category']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <label for="material_name" class="form-label">物料名称</label>
                                <input type="text" class="form-control" id="material_name" name="material_name" value="<?php echo $inbound['material_name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="spec" class="form-label">规格</label>
                                <input type="text" class="form-control" id="spec" name="spec" value="<?php echo $inbound['spec']; ?>">
                            </div>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <label for="unit" class="form-label">单位</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?php echo $inbound['unit']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">数量</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" value="<?php echo $inbound['quantity']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">单价</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $inbound['price']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="in_time" class="form-label">入库时间</label>
                                <input type="datetime-local" class="form-control" id="in_time" name="in_time" value="<?php echo date('Y-m-d\TH:i', strtotime($inbound['in_time'])); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row g-4 mt-3">
                            <div class="col-md-6">
                                <label for="supplier" class="form-label">采购人</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" value="<?php echo $inbound['purchaser']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">保存</button>
                            <a href="/inbound" class="btn btn-secondary">返回列表</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 删除入库单
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('inbound_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除入库单
        $result = InboundModel::delete($id);
        
        if ($result) {
            redirect('inbound', '删除成功');
        } else {
            redirect('inbound', '删除失败');
        }
    }
}
