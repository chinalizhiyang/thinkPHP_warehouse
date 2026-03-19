# Bootstrap CDN 使用说明

## 📋 配置概览

本项目现已全面采用Bootstrap CDN，替代原来的本地文件引用。

## 🔧 技术规格

- **Bootstrap版本**: 5.3.3
- **CDN提供商**: jsDelivr
- **CSS链接**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css
- **JS链接**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js

## 📁 文件更新情况

### 已更新的页面文件：
- ✅ 所有 `application/view/**/*.html` 文件
- ✅ 统一使用CDN引用替代本地文件
- ✅ 保持原有功能和样式不变

### 配置文件：
- 📄 `config/bootstrap_config.php` - Bootstrap CDN统一配置
- 📄 本说明文档

## ⚡ 性能优势

使用CDN的优势：
1. **加载速度更快** - CDN全球分布式节点
2. **减少服务器负担** - 静态资源由CDN提供
3. **自动缓存** - 浏览器可缓存常用资源
4. **版本统一** - 便于维护和升级

## 🔍 验证方法

### 1. 检查页面引用
在浏览器开发者工具中查看：
- Network标签页确认Bootstrap文件来自CDN
- Elements标签页确认link标签指向正确URL

### 2. 测试CDN可用性
访问：`http://localhost:8080/config/bootstrap_config.php`
```json
{
  "version": "5.3.3",
  "css_link": "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
  "js_link": "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
  "cdn_available": true
}
```

## 🛠️ 维护指南

### 升级Bootstrap版本：
1. 修改 `config/bootstrap_config.php` 中的 `VERSION` 常量
2. 所有页面会自动使用新版本（无需逐个修改）

### 回滚到本地文件：
如需回滚，可使用以下PowerShell命令：
```powershell
# 将CDN引用替换回本地文件引用
(Get-ChildItem -Path "application\view" -Recurse -Filter "*.html").FullName | ForEach-Object {
    $content = Get-Content $_ -Raw
    $content = $content -replace 'https://cdn\.jsdelivr\.net/npm/bootstrap@\d+\.\d+\.\d+/dist/css/bootstrap\.min\.css', '/static/css/bootstrap.min.css'
    $content = $content -replace 'https://cdn\.jsdelivr\.net/npm/bootstrap@\d+\.\d+\.\d+/dist/js/bootstrap\.bundle\.min\.js', '/static/js/bootstrap.bundle.min.js'
    Set-Content $_ -Value $content -Encoding UTF8
}
```

## 🚨 注意事项

1. **网络依赖**：需要稳定的网络连接访问CDN
2. **备用方案**：建议保留本地Bootstrap文件作为备份
3. **版本锁定**：使用具体版本号避免意外更新
4. **HTTPS支持**：CDN支持HTTPS，确保安全传输

## 📊 性能监控

可以通过以下方式监控Bootstrap加载性能：
- 浏览器开发者工具的Network面板
- 页面加载时间对比测试
- 移动设备加载速度测试

---
*最后更新：2026年2月*