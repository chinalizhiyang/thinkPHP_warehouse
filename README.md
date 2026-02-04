# 仓储管理系统 (Warehouse Management System)

基于PHP的仓储管理系统，采用ThinkPHP框架开发。

## 📋 系统特性

- **物料管理**：物料信息维护、分类管理
- **入库管理**：入库单创建、审核、历史记录
- **出库管理**：出库申请、审批、发货跟踪
- **库存管理**：实时库存查询、预警设置、盘点功能
- **系统管理**：用户权限、操作日志、数据备份恢复

## 🛠 技术栈

- **后端框架**：ThinkPHP 6.0
- **数据库**：MySQL 8.0
- **前端框架**：Bootstrap 5
- **编程语言**：PHP 8.0+

## 📁 项目结构

```
warehouse-system/
├── application/           # 应用目录
│   ├── controller/       # 控制器
│   ├── model/           # 模型
│   ├── view/            # 视图模板
│   ├── config/          # 配置文件
│   └── common.php       # 公共函数
├── public/              # 公共资源目录
├── backups/             # 数据库备份目录
└── vendor/              # 第三方依赖
```

## 🚀 部署说明

### 环境要求
- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx Web服务器

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/您的用户名/仓库名.git
cd 仓库名
```

2. **配置数据库**
```bash
# 创建数据库
mysql -u root -p -e "CREATE DATABASE warehouse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 导入初始数据
mysql -u root -p warehouse_db < backup_initial.sql
```

3. **配置文件**
```php
# 编辑 application/config/database.php
return [
    'hostname' => 'localhost',
    'database' => 'warehouse_db',
    'username' => '您的用户名',
    'password' => '您的密码',
    'hostport' => '3306',
];
```

4. **设置权限**
```bash
chmod -R 755 backups/
chmod -R 777 runtime/
```

5. **访问系统**
- 前台地址：http://localhost
- 默认管理员：admin / 123456

## 🔧 主要功能模块

### 物料管理
- 物料信息录入和编辑
- 物料分类管理
- 物料状态监控

### 入库管理
- 入库单创建和审批
- 入库历史查询
- 双表记录机制（inbound + inbound_history）

### 出库管理
- 出库申请和审批
- 出库历史追踪
- 领用部门管理

### 库存管理
- 实时库存查询
- 库存预警设置
- 库存盘点功能

### 系统管理
- 用户权限管理
- 操作日志记录
- 数据备份恢复
- 系统日志查看

## 📊 数据库设计

主要数据表：
- `materials` - 物料信息表
- `inbound` - 入库单表
- `inbound_history` - 入库历史表
- `outbound` - 出库单表
- `outbound_history` - 出库历史表
- `stock` - 库存表
- `users` - 用户表
- `auth_*` - 权限相关表
- `operation_records` - 操作记录表
- `system_logs` - 系统日志表

## 🔐 安全特性

- RBAC权限控制系统
- 数据库备份恢复功能
- 操作日志审计
- SQL注入防护
- XSS攻击防护

## 🤝 贡献指南

1. Fork 本仓库
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📝 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

## 📞 技术支持

如有问题请联系：李志阳 18975736605

## 🔄 更新日志

### v2.0.0 (2026-02-04)
- 重构系统管理模块
- 实现MySQL原生备份恢复功能
- 完善操作记录和系统日志功能
- 优化用户界面和交互体验
- 增强系统安全性和稳定性