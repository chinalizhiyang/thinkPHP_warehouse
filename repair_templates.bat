@echo off
chcp 65001 >nul
title 仓储管理系统 - 模板文件乱码修复工具
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                 模板文件乱码批量修复工具                     ║
echo ║                                                              ║
echo ║  功能：自动扫描并修复 application/view 目录下所有HTML文件    ║
echo ║  的中文乱码问题                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

echo 正在初始化修复环境...
echo.

REM 检查目录是否存在
if not exist "application\view" (
    echo ❌ 错误：未找到 application\view 目录
    echo 请确保在项目根目录下运行此脚本
    pause
    exit /b 1
)

echo 🔍 开始扫描乱码文件...
echo.

powershell -ExecutionPolicy Bypass -Command "
# 完整的乱码映射表
`$encodingMap = @{
    # 系统相关
    '浠撳偍绠＄悊绯荤粺' = '仓储管理系统'
    '棣栭〉' = '首页'
    
    # 入库相关
    '鍏ュ簱绠＄悊' = '入库管理'
    '娣诲姞鍏ュ簱鍗? = '添加入库单'
    '缂栬緫鍏ュ簱鍗? = '编辑入库单'
    '鍏ュ簱鍘嗗彶' = '入库历史'
    '鍏ュ簱鍗曞彿' = '入库单号'
    '渚涘簲鍟? = '供应商'
    '鎿嶄綔鍛? = '操作员'
    '鎬婚噾棰? = '总金额'
    '鐘舵€? = '状态'
    '鍒涘缓鏃堕棿' = '创建时间'
    '宸插叆搴? = '已入库'
    '寰呭叆搴? = '待入库'
    
    # 出库相关
    '鍑哄簱绠＄悊' = '出库管理'
    '娣诲姞鍑哄簱鍗? = '添加出库单'
    '缂栬緫鍑哄簱鍗? = '编辑出库单'
    '鍑哄簱鍘嗗彶' = '出库历史'
    
    # 库存相关
    '搴撳瓨绠＄悊' = '库存管理'
    '搴撳瓨鐩樼偣' = '库存盘点'
    '搴撳瓨鎶ヨ〃' = '库存报表'
    
    # 物料相关
    '鐗╂枡绠＄悊' = '物料管理'
    '娣诲姞鐗╂枡' = '添加物料'
    '缂栬緫鐗╂枡' = '编辑物料'
    '鐗╂枡鍒嗙被' = '物料分类'
    
    # 用户相关
    '鐢ㄦ埛绠＄悊' = '用户管理'
    '娣诲姞鐢ㄦ埛' = '添加用户'
    '缂栬緫鐢ㄦ埛' = '编辑用户'
    '涓汉璧勬枡' = '个人资料'
    '鐢ㄦ埛鍚? = '用户名'
    '閭' = '邮箱'
    '鎵嬫満鍙? = '手机号'
    '淇濆瓨' = '保存'
    '娉ㄩ攢' = '注销'
    '娉ㄥ唽' = '注册'
    
    # 角色权限相关
    '瑙掕壊绠＄悊' = '角色管理'
    '娣诲姞瑙掕壊' = '添加角色'
    '缂栬緫瑙掕壊' = '编辑角色'
    '鍒嗛厤鏉冮檺' = '分配权限'
    
    # 记录相关
    '鎿嶄綔璁板綍' = '操作记录'
    '绯荤粺鏃ュ織' = '系统日志'
    '鏁版嵁澶囦唤' = '数据备份'
    '鍘嗗彶璁板綍' = '历史记录'
    '缁熻鍒嗘瀽' = '统计分析'
    
    # 通用操作
    '缂栬緫' = '编辑'
    '鍒犻櫎' = '删除'
    '淇濆瓨' = '保存'
    '鎿嶄綔' = '操作'
    'ID' = 'ID'
}

# 统计变量
`$totalFiles = 0
`$fixedFiles = 0
`$unchangedFiles = 0

Write-Host '📁 开始处理文件...' -ForegroundColor Cyan
Write-Host ''

# 处理所有HTML文件
Get-ChildItem -Path 'application\view' -Recurse -Filter '*.html' | ForEach-Object {
    `$totalFiles++
    `$filePath = `$_.FullName
    `$fileName = `$_.Name
    `$content = Get-Content `$filePath -Encoding UTF8 -Raw
    
    `$hasChanges = `$false
    # 检查并替换乱码
    foreach (`$badText in `$encodingMap.Keys) {
        if (`$content.Contains(`$badText)) {
            `$content = `$content -replace [regex]::Escape(`$badText), `$encodingMap[`$badText]
            `$hasChanges = `$true
        }
    }
    
    # 如果有修改，保存文件
    if (`$hasChanges) {
        Set-Content `$filePath -Value `$content -Encoding UTF8
        Write-Host '✅ 已修复: ' -NoNewline -ForegroundColor Green
        Write-Host `$fileName -ForegroundColor White
        `$fixedFiles++
    } else {
        `$unchangedFiles++
    }
}

Write-Host ''
Write-Host '🎉 修复完成！' -ForegroundColor Green
Write-Host ''
Write-Host '📊 修复统计:' -ForegroundColor Cyan
Write-Host '   处理文件总数: ' -NoNewline
Write-Host `$totalFiles '个' -ForegroundColor White
Write-Host '   已修复文件数: ' -NoNewline  
Write-Host `$fixedFiles '个' -ForegroundColor Green
Write-Host '   无需修复文件: ' -NoNewline
Write-Host `$unchangedFiles '个' -ForegroundColor Yellow
Write-Host '   修复成功率: ' -NoNewline
Write-Host ([math]::Round((`$fixedFiles/`$totalFiles)*100, 2))'%' -ForegroundColor Green
"

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                    修复工具执行完毕                          ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo 提示：
echo • 如需再次修复，可重新运行此脚本
echo • 建议定期备份重要文件
echo • 保持编辑器使用UTF-8编码
echo.
pause