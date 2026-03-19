<?php
namespace app\controller;

class Index
{
    public function index()
    {
        // 检查登录状态
        if (!isset($_COOKIE['user'])) {
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
        
        // 5.1 本月各部门领用金额统计
        $dept_sql = "SELECT dept, SUM(quantity * price) as amount FROM outbound WHERE DATE(out_time) >= ? AND (dept IS NOT NULL AND dept != '') GROUP BY dept ORDER BY amount DESC";
        $dept_data = db_get_all($dept_sql, [$this_month_start]) ?: [];
        
        // 调试输出
        error_log('本月部门数据：' . json_encode($dept_data, JSON_UNESCAPED_UNICODE));
        
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
        
        // 10. 生产辅料类别的物料库存数量统计
        // 注意：materials.category 字段直接存储类别名称，不需要 JOIN categories 表
        $material_stock_sql = "SELECT 
                                m.name as material_name,
                                m.stock as stock_quantity,
                                m.category
                              FROM materials m 
                              WHERE m.category = '生产辅料' 
                              ORDER BY m.stock DESC, m.id DESC";
        $material_stock_data = db_get_all($material_stock_sql) ?: [];
        
        // 调试输出
        error_log('生产辅料数据：' . json_encode($material_stock_data, JSON_UNESCAPED_UNICODE));
        
        ob_start();
        ?>
        <!-- 页面头部信息 -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>欢迎回来，<?php echo json_decode($_COOKIE['user'], true)['username']; ?></h2>
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
                    <div class="bg-white p-4 rounded shadow-sm" style="position: relative;" id="outbound-card">
                        <div class="text-secondary mb-2">本月出库金额</div>
                        <div class="h5 font-bold text-danger">¥<?php echo $month_outbound_formatted; ?></div>
                        <div class="text-danger text-sm mt-2"><i class="fa fa-arrow-down"></i> 本月累计</div>
                        
                        <!-- 悬停提示框 -->
                        <div id="dept-tooltip" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; min-width: 600px; max-width: 800px;">
                            <div class="card shadow-lg" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-danger text-white py-3">
                                    <h5 class="mb-0"><i class="fa fa-bar-chart"></i> 各部门领用金额</h5>
                                </div>
                                <div class="card-body p-4" style="height: 400px;">
                                    <canvas id="dept-chart" width="800" height="400"></canvas>
                                </div>
                            </div>
                        </div>
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

        <div class="mb-4">
            <!-- 生产辅料库存数量统计图表 -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa fa-bar-chart"></i> 生产辅料库存数量</h5>
                </div>
                <div class="card-body">
                    <div style="height: 500px;">
                        <canvas id="material-category-chart"></canvas>
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
                            <li class="mb-2"><strong>开发框架：</strong> ThinkPHP 框架</li>
                            <li class="mb-2"><strong>数据库版本：</strong> MySQL 8.0</li>
                            <li class="mb-2"><strong>PHP 版本：</strong> <?php echo phpversion(); ?></li>
                            <li class="mb-2"><strong>最后更新：</strong> 2026-02-04</li>
                            <li class="mb-2"><strong>技术支持：</strong> 李志阳 18975736605</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
                
        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
                
        <script>
        // 本月出库金额各部门领用图表
        (function() {
            const card = document.getElementById('outbound-card');
            const tooltip = document.getElementById('dept-tooltip');
            let chartInstance = null;
            let hoverTimeout = null;
                    
            // 部门数据
            const deptData = <?php echo json_encode($dept_data, JSON_UNESCAPED_UNICODE); ?>;
                    
            // 调试输出
            console.log('部门数据:', deptData);
                    
            // 显示提示框
            function showTooltip() {
                hoverTimeout = setTimeout(function() {
                    tooltip.style.display = 'block';
                            
                    // 如果图表未创建，则创建图表
                    if (!chartInstance) {
                        initChart();
                    }
                }, 300); // 300ms 延迟显示
            }
                    
            // 隐藏提示框
            function hideTooltip() {
                clearTimeout(hoverTimeout);
                tooltip.style.display = 'none';
            }
                    
            // 初始化图表
            function initChart() {
                const ctx = document.getElementById('dept-chart').getContext('2d');
                            
                // 准备数据
                const labels = deptData.map(item => item.dept || '未分配');
                const data = deptData.map(item => parseFloat(item.amount) || 0);
                            
                // 调试输出
                console.log('图表标签:', labels);
                console.log('图表数据:', data);
                            
                // 如果没有数据，显示提示
                if (data.length === 0 || data.every(d => d === 0)) {
                    ctx.canvas.parentNode.innerHTML = '<div class="text-center text-muted py-5"><i class="fa fa-info-circle fa-3x mb-3"></i><p>暂无部门领用数据</p></div>';
                    return;
                }
                            
                // 生成颜色
                const backgroundColors = [
                    'rgba(239, 83, 80, 0.8)',
                    'rgba(66, 165, 245, 0.8)',
                    'rgba(102, 187, 106, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(156, 39, 176, 0.8)',
                    'rgba(233, 30, 99, 0.8)',
                    'rgba(3, 169, 244, 0.8)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(255, 152, 0, 0.8)',
                    'rgba(121, 85, 72, 0.8)'
                ];
                            
                // 创建图表
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '领用金额 (¥)',
                            data: data,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 50,
                                bottom: 10,
                                left: 10,
                                right: 10
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        return '领用金额：¥' + value.toLocaleString('zh-CN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                }
                            },
                            datalabels: {
                                display: true,
                                anchor: 'end',
                                align: 'top',
                                formatter: function(value, context) {
                                    if (value === 0) return '';
                                    return '¥' + value.toLocaleString('zh-CN', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                },
                                font: {
                                    weight: 'bold',
                                    size: 12
                                },
                                color: '#333',
                                offset: 4,
                                textStrokeColor: '#fff',
                                textStrokeWidth: 2
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    lineWidth: 1
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    },
                                    callback: function(value) {
                                        return '¥' + value.toLocaleString();
                                    },
                                    padding: 8
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    maxRotation: 45,
                                    minRotation: 45,
                                    padding: 8
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
            
            // 绑定事件
            card.addEventListener('mouseenter', showTooltip);
            card.addEventListener('mouseleave', hideTooltip);
            
            // 鼠标移动时隐藏提示框
            document.addEventListener('mousemove', function(e) {
                if (tooltip.style.display === 'block' && !tooltip.contains(e.target) && !card.contains(e.target)) {
                    hideTooltip();
                }
            });
        })();
        
        // 生产辅料库存数量统计图表
        (function() {
            const canvas = document.getElementById('material-category-chart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // 准备数据
            const materialData = <?php echo json_encode($material_stock_data, JSON_UNESCAPED_UNICODE); ?>;
            
            // 调试输出
            console.log('生产辅料数据:', materialData);
            console.log('数据数量:', materialData.length);
            
            // 如果没有数据，显示提示
            if (!materialData || materialData.length === 0) {
                canvas.parentNode.innerHTML = '<div class="text-center text-muted py-5"><i class="fa fa-info-circle fa-3x mb-3"></i><p>暂无生产辅料库存数据</p></div>';
                return;
            }
            
            const labels = materialData.map(item => item.material_name || '未命名');
            // 将 KG 转换为吨（除以 1000）
            const stockData = materialData.map(item => (parseFloat(item.stock_quantity) || 0) / 1000);
            
            console.log('标签:', labels);
            console.log('库存数据:', stockData);
            console.log('最大库存:', Math.max(...stockData));
            console.log('最小库存:', Math.min(...stockData));
            
            // 生成颜色（参考上图的颜色方案）
            const backgroundColors = [
                'rgba(255, 105, 135, 0.8)',  // 粉红
                'rgba(66, 165, 245, 0.8)',   // 蓝色
                'rgba(255, 213, 79, 0.8)',   // 黄色
                'rgba(80, 199, 194, 0.8)',   // 青色
                'rgba(171, 133, 255, 0.8)',  // 紫色
                'rgba(255, 167, 79, 0.8)',   // 橙色
                'rgba(206, 211, 216, 0.8)',  // 灰色
                'rgba(79, 129, 255, 0.8)',   // 深蓝
                'rgba(255, 138, 255, 0.8)',  // 粉色
                'rgba(129, 236, 162, 0.8)',  // 绿色
                'rgba(255, 224, 178, 0.8)',  // 浅橙
                'rgba(128, 222, 255, 0.8)',  // 浅蓝
                'rgba(255, 204, 204, 0.8)',  // 淡粉
                'rgba(204, 255, 204, 0.8)',  // 淡绿
            ];
            
            const borderColors = backgroundColors.map(color => color.replace('0.8', '1'));
            
            // 创建图表
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '库存数量',
                        data: stockData,
                        backgroundColor: backgroundColors.slice(0, labels.length),
                        borderColor: borderColors.slice(0, labels.length),
                        borderWidth: 1,
                        borderRadius: 0,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 60,
                            bottom: 10,
                            left: 10,
                            right: 10
                        }
                    },
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    return '库存数量：' + value + '吨';
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            anchor: 'end',
                            align: 'top',
                            clip: false,
                            overflow: 'visible',
                            formatter: function(value, context) {
                                if (value === 0) return '0.00 吨';
                                return value.toFixed(2) + '吨';
                            },
                            font: {
                                weight: 'bold',
                                size: 13
                            },
                            color: '#333',
                            offset: 10
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                lineWidth: 1,
                                drawBorder: true
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return value;
                                },
                                padding: 8,
                                stepSize: 16
                            },
                            title: {
                                display: true,
                                text: '库存数量（吨）',
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                maxRotation: 45,
                                minRotation: 45,
                                padding: 8
                            },
                            title: {
                                display: true,
                                text: '生产辅料名称',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}
