# ThinkPHP 项目 IIS 部署指南

## 前置要求

1. IIS 7.0 或更高版本
2. PHP 8.0 或更高版本（已配置为 IIS 的 FastCGI 模块）
3. URL 重写模块（URL Rewrite Module）已安装
4. Composer（用于安装项目依赖）

## 部署步骤

### 1. 安装项目依赖

首先需要通过 Composer 安装项目依赖：

```bash
composer install
```

### 2. 配置 IIS 网站

1. 打开 IIS 管理器
2. 添加新网站或选择现有网站
3. 设置物理路径为 `c:\wwwroot\ThinkPHP_warehouse`
4. 配置端口（例如 80 或 8080）

### 3. 配置 URL 重写

项目已包含 `web.config` 文件，其中配置了 URL 重写规则，无需额外配置。

### 4. 设置目录权限

确保以下目录有写入权限：
- `runtime` 目录（如果存在）
- `public` 目录（如需要上传文件）

### 5. 验证 PHP 配置

确保 IIS 已正确配置 PHP FastCGI 处理程序映射。

## 已创建的配置文件

### web.config
- 配置了 URL 重写规则，将所有非文件、非目录的请求重写到 index.php
- 设置了默认文档为 index.php
- 配置了最大请求内容长度为 50MB

## 常见问题

### URL 重写不工作
- 确认已安装 URL Rewrite Module
- 检查 web.config 文件是否在网站根目录
- 重启 IIS 服务

### 权限错误
- 为 IIS_IUSRS 用户组授予相应目录的写入权限
- 检查应用程序池标识

### 数据库连接问题
- 确认数据库配置正确
- 检查数据库服务是否运行
