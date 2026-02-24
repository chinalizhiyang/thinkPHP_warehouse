<?php
// 性能监控和诊断工具
class PerformanceMonitor {
    private $startTime;
    private $checkpoints = [];
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    public function checkpoint($name) {
        $this->checkpoints[$name] = microtime(true);
    }
    
    public function getReport() {
        $endTime = microtime(true);
        $totalTime = $endTime - $this->startTime;
        
        $report = [
            'total_time' => round($totalTime * 1000, 2),
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_current' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'included_files' => count(get_included_files()),
            'checkpoints' => []
        ];
        
        $lastTime = $this->startTime;
        foreach ($this->checkpoints as $name => $time) {
            $report['checkpoints'][$name] = round(($time - $lastTime) * 1000, 2);
            $lastTime = $time;
        }
        
        return $report;
    }
    
    public static function quickCheck() {
        $monitor = new self();
        
        // 模拟一些操作
        usleep(10000); // 10ms
        $monitor->checkpoint('database_connect');
        
        usleep(5000); // 5ms
        $monitor->checkpoint('data_processing');
        
        usleep(3000); // 3ms
        $monitor->checkpoint('template_rendering');
        
        return $monitor->getReport();
    }
}

// 如果直接访问此文件
if (basename($_SERVER['SCRIPT_NAME']) === 'performance_monitor.php') {
    header('Content-Type: application/json');
    
    if (isset($_GET['quick'])) {
        echo json_encode(PerformanceMonitor::quickCheck(), JSON_PRETTY_PRINT);
    } else {
        $monitor = new PerformanceMonitor();
        
        // 模拟页面加载过程
        sleep(1);
        $monitor->checkpoint('page_load');
        
        echo json_encode($monitor->getReport(), JSON_PRETTY_PRINT);
    }
}
?>