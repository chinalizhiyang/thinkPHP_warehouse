<?php
// 简单测试文件
echo "测试时间: " . date('Y-m-d H:i:s') . "<br>";
echo "导入CSV按钮应该在这里显示：<br>";

// 模拟按钮HTML
echo '<a href="/inbound-history/import-csv" style="background-color: #52c41a; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; margin: 10px 0;">
        <i class="fa fa-file-import"></i> 导入CSV
      </a>';
echo '<br><br>';

// 检查文件是否存在
if (file_exists('application/controller/InboundHistory.php')) {
    echo "控制器文件存在<br>";
    $content = file_get_contents('application/controller/InboundHistory.php');
    if (strpos($content, '导入CSV') !== false) {
        echo "控制器文件包含导入CSV文本 ✓<br>";
    } else {
        echo "控制器文件不包含导入CSV文本 ✗<br>";
    }
} else {
    echo "控制器文件不存在<br>";
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">