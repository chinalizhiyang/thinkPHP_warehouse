<?php
namespace app\controller;

use app\model\Material as MaterialModel;

class Material
{
    // 物料列表
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 获取分页参数
        $page = $_GET['page'] ?? 1;
        $page_size = $_GET['page_size'] ?? 25;
        
        // 获取物料列表
        $result = MaterialModel::getList([], $page, $page_size);
        $materials = $result['list'];
        $total = $result['total'];
        $total_pages = ceil($total / $page_size);
        
        // 渲染物料列表内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fa fa-cubes"></i> 物料管理</h3>
                <div class="d-flex gap-2">
                    <a href="/material/add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> 添加物料
                    </a>
                    <a href="/material/import-csv" class="btn btn-success">
                        <i class="fa fa-file-import"></i> 导入CSV
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="mb-3">
                    <form action="/material/search" method="get" class="d-flex flex-wrap gap-2">
                        <input type="text" name="keyword" class="form-control" placeholder="搜索关键词" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>" style="width: 300px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> 搜索
                        </button>
                        <?php if (!empty($_GET['keyword']) || !empty($_GET['search_column'])): ?>
                        <a href="/material" class="btn btn-secondary">清除</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>序号</th>
                                <th>物料编码</th>
                                <th>分类</th>
                                <th>物料名称</th>
                                <th>规格</th>
                                <th>单位</th>
                                <th>单价</th>
                                <th>库存</th>
                                <th>仓位</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materials)): ?>
                                <?php foreach ($materials as $index => $material): ?>
                                <tr <?php echo $material['stock'] > 0 ? '' : 'class="table-warning"'; ?>>
                                    <td><?php echo ($page - 1) * $page_size + $index + 1; ?></td>
                                    <td><?php echo $material['code']; ?></td>
                                    <td><?php echo $material['category_name'] ?? ''; ?></td>
                                    <td><?php echo $material['name']; ?></td>
                                    <td><?php echo $material['spec'] ?? ''; ?></td>
                                    <td><?php echo $material['unit']; ?></td>
                                    <td><?php echo $material['price']; ?></td>
                                    <td <?php echo $material['stock'] > 0 ? '' : 'class="fw-bold text-danger"'; ?>><?php echo $material['stock']; ?><?php echo $material['stock'] == 0 ? ' (缺货)' : ''; ?></td>
                                    <td><?php echo $material['location'] ?? ''; ?></td>
                                    <td><?php echo ($material['status'] ?? 1) ? '<span class="badge bg-success">启用</span>' : '<span class="badge bg-danger">禁用</span>'; ?></td>
                                    <td>
                                        <a href="/material/edit/<?php echo $material['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-edit"></i> 编辑
                                        </a>
                                        <a href="/material/delete/<?php echo $material['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定要删除吗？');">
                                            <i class="fa fa-trash"></i> 删除
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fa fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">暂无物料记录</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- 分页控件 -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="分页">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="/material?page=<?php echo $page - 1; ?>&page_size=<?php echo $page_size; ?>" aria-label="上一页">
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
                        $show_pages[] = -1; // 省略号标记
                        $show_pages[] = $total_pages;
                    } elseif ($page >= $total_pages - 3) {
                        $show_pages = [1, -1];
                        $show_pages = array_merge($show_pages, range($total_pages - 4, $total_pages));
                    } else {
                        $show_pages = [1, -1];
                        $show_pages = array_merge($show_pages, range($page - 2, $page + 2));
                        $show_pages[] = -1; // 省略号标记
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
                    <a class="page-link" href="/material?page=<?php echo $p; ?>&page_size=<?php echo $page_size; ?>"><?php echo $p; ?></a>
                </li>
                <?php endif; endforeach; ?>
                
                <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="/material?page=<?php echo $page + 1; ?>&page_size=<?php echo $page_size; ?>" aria-label="下一页">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- 显示页码信息和跳转 -->
        <div class="text-center mt-2 mb-4">
            <span class="text-muted">共 <?php echo $total; ?> 条记录，每页 <?php echo $page_size; ?> 条，当前第 <?php echo $page; ?> 页/共 <?php echo $total_pages; ?> 页</span>
        </div>
        <?php endif; ?>
        <?php
        $content = ob_get_clean();
        
        // 使用布局模板显示页面
        return view('layout/main', [
            'title' => '物料管理',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Material'
        ]);
    }
    
    // 搜索物料
    public function search()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取搜索条件
        $keyword = $_GET['keyword'] ?? '';
        $search_column = $_GET['search_column'] ?? 'all';
        
        // 获取分页参数
        $page = $_GET['page'] ?? 1;
        $page_size = $_GET['page_size'] ?? 25;
        
        // 构建搜索条件
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            // 数据库列映射
            $column_map = [
                'name' => 'name',
                'code' => 'material_code',
                'category' => 'category',
                'unit' => 'unit',
                'location' => 'location'
            ];
            
            if ($search_column === 'all') {
                // 搜索所有列
                $like_conditions = [];
                foreach ($column_map as $display_name => $db_column) {
                    $like_conditions[] = "$db_column LIKE ?";
                    $params[] = "%$keyword%";
                }
                $where[] = '(' . implode(' OR ', $like_conditions) . ')';
            } elseif (isset($column_map[$search_column])) {
                // 搜索指定列
                $db_column = $column_map[$search_column];
                $where[] = "$db_column LIKE ?";
                $params[] = "%$keyword%";
            }
        }
        
        // 搜索物料
        $result = MaterialModel::getList($where, $page, $page_size, $params);
        
        // 计算总页码
        $total_pages = ceil($result['total'] / $result['page_size']);
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 渲染物料搜索结果内容
        ob_start();
        ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fa fa-cubes"></i> 物料搜索结果</h3>
                <div class="d-flex gap-2">
                    <a href="/material" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> 返回列表
                    </a>
                    <a href="/material/add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> 添加物料
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fa fa-info-circle"></i> 
                    搜索关键词: "<?php echo htmlspecialchars($keyword); ?>"，共找到 <?php echo $result['total']; ?> 条记录
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>序号</th>
                                <th>物料编码</th>
                                <th>分类</th>
                                <th>物料名称</th>
                                <th>规格</th>
                                <th>单位</th>
                                <th>单价</th>
                                <th>库存</th>
                                <th>仓位</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($result['list'])): ?>
                                <?php foreach ($result['list'] as $index => $material): ?>
                                <tr <?php echo $material['stock'] > 0 ? '' : 'class="table-warning"'; ?>>
                                    <td><?php echo ($page - 1) * $page_size + $index + 1; ?></td>
                                    <td><?php echo $material['code']; ?></td>
                                    <td><?php echo $material['category_name'] ?? ''; ?></td>
                                    <td><?php echo $material['name']; ?></td>
                                    <td><?php echo $material['spec'] ?? ''; ?></td>
                                    <td><?php echo $material['unit']; ?></td>
                                    <td><?php echo $material['price']; ?></td>
                                    <td <?php echo $material['stock'] > 0 ? '' : 'class="fw-bold text-danger"'; ?>><?php echo $material['stock']; ?><?php echo $material['stock'] == 0 ? ' (缺货)' : ''; ?></td>
                                    <td><?php echo $material['location'] ?? ''; ?></td>
                                    <td><?php echo ($material['status'] ?? 1) ? '<span class="badge bg-success">启用</span>' : '<span class="badge bg-danger">禁用</span>'; ?></td>
                                    <td>
                                        <a href="/material/edit/<?php echo $material['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-edit"></i> 编辑
                                        </a>
                                        <a href="/material/delete/<?php echo $material['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定要删除吗？');">
                                            <i class="fa fa-trash"></i> 删除
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fa fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">暂无符合条件的物料记录</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- 分页控件 -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="分页">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="/material/search?page=<?php echo $page - 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>" aria-label="上一页">
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
                    <a class="page-link" href="/material/search?page=<?php echo $p; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>"><?php echo $p; ?></a>
                </li>
                <?php endif; endforeach; ?>
                
                <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="/material/search?page=<?php echo $page + 1; ?>&page_size=<?php echo $page_size; ?>&keyword=<?php echo urlencode($keyword); ?>" aria-label="下一页">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="text-center mt-2 mb-4">
            <span class="text-muted">共 <?php echo $result['total']; ?> 条记录，每页 <?php echo $page_size; ?> 条，当前第 <?php echo $page; ?> 页/共 <?php echo $total_pages; ?> 页</span>
        </div>
        <?php endif; ?>
        <?php
        $content = ob_get_clean();
        
        // 使用布局模板显示页面
        return view('layout/main', [
            'title' => '物料搜索结果',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Material'
        ]);
    }
    
    // 添加物料
    public function add()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'unit' => $_POST['unit'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'location' => $_POST['location'] ?? '',
                'supplier' => $_POST['supplier'] ?? '',
                'contact_info' => $_POST['contact_info'] ?? '',
                'spec' => $_POST['spec'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            // 创建物料
            $material = MaterialModel::create($data);
            
            if ($material) {
                redirect('material', '添加成功');
            } else {
                redirect('material/add', '添加失败');
            }
        }
        
        // 渲染添加物料内容
        $content = view('material/add_content', []);
        
        // 使用布局模板显示页面
        return view('layout/main', [
            'title' => '添加物料',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Material'
        ]);
    }
    
    // 编辑物料
    public function edit($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 获取物料信息
        $material = MaterialModel::getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'unit' => $_POST['unit'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'location' => $_POST['location'] ?? '',
                'supplier' => $_POST['supplier'] ?? '',
                'contact_info' => $_POST['contact_info'] ?? '',
                'spec' => $_POST['spec'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            // 更新物料
            $result = MaterialModel::update($id, $data);
            
            if ($result) {
                redirect('/material');
            } else {
                redirect('/material/edit/' . $id);
            }
        }
        
        // 渲染编辑物料内容
        $content = view('material/edit_content', ['material' => $material]);
        
        // 使用布局模板显示页面
        return view('layout/main', [
            'title' => '编辑物料',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Material'
        ]);
    }
    
    // 删除物料
    public function delete($id)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        // 删除物料
        $result = MaterialModel::delete($id);
        
        if ($result) {
            redirect('/material', '删除成功');
        } else {
            redirect('/material', '删除失败');
        }
    }
    
    // 根据物料编号获取物料信息
    public function getByCode($code)
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            $response = [
                'success' => false,
                'message' => '请先登录'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            $response = [
                'success' => false,
                'message' => '无权限访问'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
        
        // 根据物料编号查找物料
        $material = MaterialModel::getByCode($code);
        
        if ($material) {
            $response = [
                'success' => true,
                'material' => $material
            ];
        } else {
            $response = [
                'success' => false,
                'message' => '未找到物料'
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    // 导入CSV文件
    public function importCsv()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 检查权限
        if (!check_permission('material_manage')) {
            redirect('/', '无权限访问');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];
            
            // 检查文件类型
            $allowed_types = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
            if (!in_array($file['type'], $allowed_types)) {
                redirect('/material', '文件类型不正确，请上传CSV文件');
            }
            
            // 检查文件大小（限制为5MB）
            if ($file['size'] > 5 * 1024 * 1024) {
                redirect('/material', '文件过大，请上传小于5MB的文件');
            }
            
            // 读取CSV文件
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                redirect('/material', '无法读取文件');
            }
            
            // 跳过第一行标题
            $header = fgetcsv($handle, 1000, ',');
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            
            // 逐行处理数据
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if (count($data) < 4) {
                    $error_count++;
                    $errors[] = "行数据不完整: " . implode(',', $data);
                    continue;
                }
                
                // 解析CSV数据
                $material_data = [
                    'code' => trim($data[0]),
                    'category' => trim($data[1]),
                    'name' => trim($data[2]),
                    'spec' => isset($data[3]) ? trim($data[3]) : '',
                    'unit' => trim($data[4]),
                    'price' => isset($data[5]) ? floatval(trim($data[5])) : 0,
                    'stock' => isset($data[6]) ? intval(trim($data[6])) : 0,
                    'location' => isset($data[7]) ? trim($data[7]) : '',
                    'description' => isset($data[8]) ? trim($data[8]) : ''
                ];
                
                // 验证必要字段
                if (empty($material_data['name']) || empty($material_data['code'])) {
                    $error_count++;
                    $errors[] = "物料名称和编码不能为空";
                    continue;
                }
                
                // 检查物料编码是否已存在
                $existing_material = MaterialModel::getByCode($material_data['code']);
                if ($existing_material) {
                    // 更新现有物料
                    $result = MaterialModel::update($existing_material['id'], $material_data);
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "更新物料 {$material_data['code']} 失败";
                    }
                } else {
                    // 创建新物料
                    $material = MaterialModel::create($material_data);
                    if ($material) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "创建物料 {$material_data['code']} 失败";
                    }
                }
            }
            
            fclose($handle);
            
            // 记录操作日志
            \app\model\Record::addOperation([
                'user_id' => $_SESSION['user']['id'],
                'action' => 'import_material_csv',
                'target' => 'material',
                'content' => "导入物料CSV文件，成功: {$success_count}条，失败: {$error_count}条"
            ]);
            
            // 构建提示信息
            $message = "导入完成！成功: {$success_count}条，失败: {$error_count}条";
            if (!empty($errors)) {
                $message .= "<br>错误详情:<br>" . implode('<br>', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= '<br>...还有' . (count($errors) - 5) . '个错误';
                }
            }
            
            redirect('/material', $message);
        }
        
        // 显示导入页面
        $menu = get_nav_menu();
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-file-import"></i> 导入物料CSV文件</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <h5><i class="fa fa-info-circle"></i> CSV文件格式说明：</h5>
                    <p class="mb-1">请按照以下格式准备CSV文件：</p>
                    <pre class="bg-light p-3 mb-0">物料编码,类别,物料名称,规格,单位,单价,库存,仓位,描述
A001,原材料,示例物料A,Φ10×50mm,个,10.50,100,A区-01,这是一个示例物料</pre>
                    <small class="text-muted">* 前3列为必填项（物料编码、类别、物料名称），其余为可选项</small>
                </div>
                
                <form action="/material/import-csv" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">选择CSV文件</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
                        <div class="form-text">支持CSV格式文件，最大5MB</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-upload"></i> 开始导入
                        </button>
                        <a href="/material" class="btn btn-secondary">
                            <i class="fa fa-times"></i> 取消
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        return view('layout/main', [
            'title' => '导入物料CSV',
            'content' => $content,
            'menu' => $menu,
            'current_controller' => 'Material'
        ]);
    }
}
