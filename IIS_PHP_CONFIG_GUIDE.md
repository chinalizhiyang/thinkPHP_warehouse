# IIS PHP 配置详细指南

## 当前状态

✅ 已完成：
- 网站创建：`ThinkPHPWarehouse` (端口 8081)
- 物理路径：`c:\wwwroot\ThinkPHP_warehouse`
- web.config 基础配置
- PHP 已安装：`C:\php\php.exe` (PHP 8.5.2)
- php-cgi.exe 存在：`C:\php\php-cgi.exe`

⚠️ 需要手动配置：
- FastCGI 应用程序注册
- PHP 处理程序映射

## 手动配置步骤

### 方法一：使用 IIS 管理器（推荐）

#### 1. 打开 IIS 管理器
- 按 `Win + R`，输入 `inetmgr`，回车

#### 2. 添加 FastCGI 应用程序
1. 在左侧连接面板中，选择服务器名称
2. 双击"FastCGI 设置"
3. 点击右侧"添加应用程序"
4. 配置如下：
   - **完整路径**：`C:\php\php-cgi.exe`
   - **最大实例数**：4
   - **实例最大请求数**：10000
   - **监视对文件的更改**：`C:\php\php.ini`
5. 点击"确定"

#### 3. 配置处理程序映射
1. 在左侧连接面板中，选择 `ThinkPHPWarehouse` 网站
2. 双击"处理程序映射"
3. 点击右侧"添加模块映射"
4. 配置如下：
   - **请求路径**：`*.php`
   - **模块**：`FastCgiModule`
   - **可执行文件**：`C:\php\php-cgi.exe`
   - **名称**：`PHP_via_FastCGI`
5. 点击"请求限制"
   - 勾选"仅当请求映射到以下内容时才调用处理程序"
   - 选择"文件或文件夹"
6. 点击"确定"保存

#### 4. 配置默认文档（已完成）
web.config 中已配置，默认文档为 `index.php`

#### 5. 测试配置
- 访问：http://localhost:8081/phpinfo.php
- 如果看到 PHP 信息页面，说明配置成功

### 方法二：使用命令行

#### 1. 以管理员身份打开 PowerShell

#### 2. 添加 FastCGI 应用程序
```powershell
& "C:\Windows\System32\inetsrv\appcmd.exe" set config -section:system.webServer/fastCgi /+"[fullPath='C:\php\php-cgi.exe',maxInstances='4',instanceMaxRequests='10000',monitorChangesTo='C:\php\php.ini']" /commit:apphost
```

#### 3. 添加处理程序映射
```powershell
& "C:\Windows\System32\inetsrv\appcmd.exe" set config "ThinkPHPWarehouse" -section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='C:\php\php-cgi.exe',resourceType='Either',requireAccess='Script']" /commit:apphost
```

#### 4. 重启网站
```powershell
& "C:\Windows\System32\inetsrv\appcmd.exe" stop site "ThinkPHPWarehouse"
& "C:\Windows\System32\inetsrv\appcmd.exe" start site "ThinkPHPWarehouse"
```

## 其他必要配置

### 1. 安装项目依赖
在项目目录运行：
```bash
composer install
```

### 2. 配置数据库
确保：
- 数据库 `warehouse_db` 已创建
- `application/config/database.php` 配置正确

### 3. 设置目录权限
为以下目录授予 IIS_IUSRS 写入权限：
- `runtime` 目录（如果存在）
- `public` 目录（如需要上传文件）

## 访问地址

配置完成后，可以通过以下地址访问：
- 本地：http://localhost:8081
- 局域网：http://10.1.0.7:8081
- 公网：http://43.138.243.204:8081

## 故障排除

### 500.19 错误
- 检查 web.config 语法是否正确
- 确认所有必需的 IIS 模块已安装

### 500 错误
- 检查 PHP FastCGI 配置
- 查看 PHP 错误日志
- 确认 `C:\php\php.ini` 配置正确

### 找不到文件
- 确认默认文档配置
- 检查目录权限
