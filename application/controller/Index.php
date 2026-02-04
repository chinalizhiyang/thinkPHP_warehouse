<?php
namespace app\controller;

class Index
{
    public function index()
    {
        // 检查登录状态
        if (!isset($_SESSION['user'])) {
            redirect('login', '请先登录');
        }
        
        // 获取导航菜单
        $menu = get_nav_menu();
        
        // 直接使用布局模板显示页面，不需要先渲染内容页面
        return view('layout/main', [
            'title' => '首页',
            'content' => $this->renderIndexContent(),
            'menu' => $menu,
            'current_controller' => 'Index'
        ]);
    }
    
    // 渲染首页内容
    private function renderIndexContent()
    {
        // 设置时区为中国上海
        date_default_timezone_set('Asia/Shanghai');
        
        // 计算日期范围
        $today = date('Y-m-d');
        $this_month_start = date('Y-m-01');
        $week_start = date('Y-m-d', strtotime('-6 days'));
        $last_week_start = date('Y-m-d', strtotime('-13 days'));
        $last_week_end = date('Y-m-d', strtotime('-7 days'));
        
        // 1. 物料总数
        $material_count = db_get_row("SELECT COUNT(*) as count FROM materials")['count'] ?? 0;
        
        // 2. 本周新增物料数
        $week_new_materials = db_get_row("SELECT COUNT(*) as count FROM materials WHERE created_at >= ?", [$week_start])['count'] ?? 0;
        
        // 3. 库存参考价值
        $stock_value = db_get_row("SELECT SUM(stock * price) as total FROM materials")['total'] ?? 0;
        $stock_value_formatted = number_format($stock_value, 2, '.', ',');
        
        // 4. 本月入库金额
        $month_inbound = db_get_row("SELECT SUM(quantity * price) as total FROM inbound WHERE DATE(in_time) >= ?", [$this_month_start])['total'] ?? 0;
        $month_inbound_formatted = number_format($month_inbound, 2, '.', ',');
        
        // 5. 本月出库金额
        $month_outbound = db_get_row("SELECT SUM(quantity * price) as total FROM outbound WHERE DATE(out_time) >= ?", [$this_month_start])['total'] ?? 0;
        $month_outbound_formatted = number_format($month_outbound, 2, '.', ',');
        
        // 6. 库存为0的物料数
        $stock_zero = db_get_row("SELECT COUNT(*) as count FROM materials WHERE stock = 0")['count'] ?? 0;
        
        // 7. 库存正常的物料数
        $stock_normal = db_get_row("SELECT COUNT(*) as count FROM materials WHERE stock > 0")['count'] ?? 0;
        
        // 8. 库存为0的周变化
        $last_week_stock_zero = db_get_row("SELECT COUNT(*) as count FROM materials WHERE stock = 0")['count'] ?? 0;
        $stock_zero_change = $stock_zero - $last_week_stock_zero;
        
        // 9. 库存正常的周变化
        $last_week_stock_normal = db_get_row("SELECT COUNT(*) as count FROM materials WHERE stock > 0")['count'] ?? 0;
        $stock_normal_change = $stock_normal - $last_week_stock_normal;
        
        ob_start();
        ?>
        <!-- 页面头部信息 -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>欢迎回来，<?php echo $_SESSION['user']['username']; ?></h2>
            </div>
            <div class="card-body">
                <p>今天是 <span id="current-time" style="font-size: 2em;"><?php echo date('Y年m月d日 H:i:s'); ?></span>，祝您工作愉快！</p>
            </div>
        </div>
        
        <script>
        // 实时更新时间
        function updateCurrentTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const timeString = `${year}年${month}月${day}日 ${hours}:${minutes}:${seconds}`;
            document.getElementById('current-time').textContent = timeString;
        }
        
        // 初始更新一次
        updateCurrentTime();
        
        // 每秒更新一次
        setInterval(updateCurrentTime, 1000);
        </script>

        <!-- 库存统计 -->
        <div class="mb-4">
            <div class="bg-primary text-white p-4 rounded">
                <h3 class="h4 font-bold"><i class="fa fa-bar-chart"></i> 库存统计</h3>
            </div>
            
            <div class="row g-4 mt-4">
                <!-- 物料总数 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">物料总数</div>
                        <div class="h5 font-bold text-primary"><?php echo $material_count; ?></div>
                        <div class="text-success text-sm mt-2"><i class="fa fa-arrow-up"></i> 本周新增 <?php echo $week_new_materials; ?></div>
                    </div>
                </div>
                
                <!-- 库存参考价值 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">库存参考价值</div>
                        <div class="h5 font-bold text-primary">¥<?php echo $stock_value_formatted; ?></div>
                        <div class="text-secondary text-sm mt-2"><i class="fa fa-money"></i> 库存总价值</div>
                    </div>
                </div>
                
                <!-- 本月入库金额 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">本月入库金额</div>
                        <div class="h5 font-bold text-success">¥<?php echo $month_inbound_formatted; ?></div>
                        <div class="text-success text-sm mt-2"><i class="fa fa-arrow-up"></i> 本月累计</div>
                    </div>
                </div>
                
                <!-- 本月出库金额 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">本月出库金额</div>
                        <div class="h5 font-bold text-danger">¥<?php echo $month_outbound_formatted; ?></div>
                        <div class="text-danger text-sm mt-2"><i class="fa fa-arrow-down"></i> 本月累计</div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mt-4">
                <!-- 库存为0 -->
                <div class="col-12 col-md-6">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">库存为0</div>
                        <div class="h5 font-bold text-danger"><?php echo $stock_zero; ?></div>
                        <div class="text-<?php echo $stock_zero_change < 0 ? 'danger' : 'success'; ?> text-sm mt-2">
                            <i class="fa fa-arrow-<?php echo $stock_zero_change < 0 ? 'down' : 'up'; ?>"></i> 
                            <?php echo $stock_zero_change < 0 ? $stock_zero_change : '+' . $stock_zero_change; ?> 相比上周
                        </div>
                    </div>
                </div>
                
                <!-- 库存正常 -->
                <div class="col-12 col-md-6">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <div class="text-secondary mb-2">库存正常</div>
                        <div class="h5 font-bold text-success"><?php echo $stock_normal; ?></div>
                        <div class="text-<?php echo $stock_normal_change > 0 ? 'success' : 'danger'; ?> text-sm mt-2">
                            <i class="fa fa-arrow-<?php echo $stock_normal_change > 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo $stock_normal_change > 0 ? '+' . $stock_normal_change : $stock_normal_change; ?> 相比上周
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统信息 -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-info-circle"></i> 系统信息</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>系统版本：</strong> v2.0.0</li>
                            <li class="mb-2"><strong>开发框架：</strong> ThinkPHP框架</li>
                            <li class="mb-2"><strong>数据库版本：</strong> MySQL 8.0</li>
                            <li class="mb-2"><strong>PHP版本：</strong> <?php echo phpversion(); ?></li>
                            <li class="mb-2"><strong>最后更新：</strong> 2026-02-04</li>
                            <li class="mb-2"><strong>技术支持：</strong> 李志阳 18975736605</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
