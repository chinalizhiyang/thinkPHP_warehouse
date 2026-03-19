<?php
require 'application/common.php';

// 查询所有类别
echo "=== 所有类别 ===\n";
$categories = db_get_all('SELECT * FROM categories');
echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 查询 materials 表前 20 条数据
echo "=== 物料数据（前 20 条）===\n";
$materials = db_get_all('SELECT m.id, m.name, m.material_code, m.category, m.stock, c.name as category_name FROM materials m LEFT JOIN categories c ON m.category = c.id LIMIT 20');
echo json_encode($materials, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 查询生产辅料类别的物料
echo "=== 生产辅料类别的物料 ===\n";
$production_materials = db_get_all("SELECT m.id, m.name, m.material_code, m.category, m.stock, c.name as category_name FROM materials m LEFT JOIN categories c ON m.category = c.id WHERE c.name = '生产辅料'");
echo json_encode($production_materials, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 统计每个类别的物料数量
echo "=== 各类别物料数量 ===\n";
$category_stats = db_get_all('SELECT c.name, COUNT(m.id) as count FROM categories c LEFT JOIN materials m ON m.category = c.id GROUP BY c.id, c.name ORDER BY count DESC');
echo json_encode($category_stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
