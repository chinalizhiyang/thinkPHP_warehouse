<?php
// 引入数据库配置
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/application/common.php';

// 测试日期查询
echo "<h2>测试出库日期查询</h2>";

// 1. 先查看所有记录
echo "<h3>1. 所有出库记录：</h3>";
$sql = "SELECT id, material_code, out_time, created_at FROM outbound ORDER BY out_time DESC LIMIT 10";
$data = db_get_all($sql);
if ($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} else {
    echo "无数据";
}

// 2. 测试日期查询
echo "<h3>2. 测试日期范围查询（最近 7 天）：</h3>";
$start_date = date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
$end_date = date('Y-m-d') . ' 23:59:59';
echo "开始日期：$start_date<br>";
echo "结束日期：$end_date<br>";

$sql = "SELECT id, material_code, out_time FROM outbound WHERE out_time >= ? AND out_time <= ? ORDER BY out_time DESC";
$params = [$start_date, $end_date];
$data = db_get_all($sql, $params);
if ($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} else {
    echo "无数据";
}

// 3. 测试单个日期查询
echo "<h3>3. 测试单个日期查询（今天）：</h3>";
$today = date('Y-m-d') . ' 00:00:00';
echo "日期：$today<br>";

$sql = "SELECT id, material_code, out_time FROM outbound WHERE out_time >= ? ORDER BY out_time DESC";
$params = [$today];
$data = db_get_all($sql, $params);
if ($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} else {
    echo "无数据";
}

// 4. 检查表结构
echo "<h3>4. 表结构：</h3>";
$sql = "DESCRIBE outbound";
$structure = db_get_all($sql);
echo "<pre>";
print_r($structure);
echo "</pre>";
