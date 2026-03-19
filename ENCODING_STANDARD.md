# 文件编码规范

## 📋 编码标准

### 文件编码要求
- **统一使用 UTF-8 编码**
- **不含 BOM (Byte Order Mark)**
- **换行符使用 LF (\n)**

### 支持的字符集
- ✅ 中文简体字符
- ✅ 英文字母和数字
- ✅ 常用标点符号
- ✅ HTML/XML特殊字符

## 🚫 禁止使用的编码
- ❌ GBK/GB2312
- ❌ ANSI
- ❌ UTF-8 with BOM
- ❌ 其他地区编码

## 🛠️ 编辑器配置建议

### VS Code 配置
```json
{
    "files.encoding": "utf8",
    "files.autoGuessEncoding": false,
    "files.eol": "\n"
}
```

### Sublime Text 配置
- File -> Save with Encoding -> UTF-8
- View -> Line Endings -> Unix

### Notepad++ 配置
- Encoding -> Convert to UTF-8 without BOM
- Edit -> EOL Conversion -> Unix (LF)

## 🔍 乱码检测和修复

### 检测脚本
```powershell
# 检测乱码文件
Get-ChildItem -Path "application\view" -Recurse -Filter "*.html" | ForEach-Object {
    $content = Get-Content $_.FullName -Encoding UTF8 -Raw
    if ($content -match '[^\x00-\x7F]' -and $content -match '涓€|汉|璧|枡') {
        Write-Host "发现乱码: $($_.Name)"
    }
}
```

### 修复脚本
```powershell
# 批量修复乱码
$encodingMap = @{
    '缁熻鍒嗘瀽' = '统计分析'
    '鍏ュ簱鍘嗗彶' = '入库历史'
    # ... 其他映射关系
}

Get-ChildItem -Path "application\view" -Recurse -Filter "*.html" | ForEach-Object {
    $content = Get-Content $_.FullName -Encoding UTF8 -Raw
    foreach ($bad in $encodingMap.Keys) {
        $content = $content -replace [regex]::Escape($bad), $encodingMap[$bad]
    }
    Set-Content $_.FullName -Value $content -Encoding UTF8
}
```

## 📝 常见乱码对照表

| 乱码 | 正确文本 |
|------|----------|
| 缁熻鍒嗘瀽 | 统计分析 |
| 鍏ュ簱绠＄悊 | 入库管理 |
| 鍑哄簱绠＄悊 | 出库管理 |
| 浠撳偍绠＄悊绯荤粺 | 仓储管理系统 |
| 涓汉璧勬枡 | 个人资料 |
| 鐢ㄦ埛鍚? | 用户名 |
| 閭 | 邮箱 |

## ⚠️ 注意事项

1. **保存前检查**：编辑完成后务必检查中文显示是否正常
2. **版本控制**：Git提交前确认编码正确
3. **团队协作**：确保所有开发人员使用相同编码标准
4. **备份重要**：修改前建议备份原文件

## 🆘 紧急处理

如遇大量文件乱码：
1. 立即停止相关操作
2. 使用备份文件恢复
3. 联系技术支持
4. 分析乱码产生原因

---
*最后更新：2026年2月*