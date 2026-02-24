# PowerShell script to fix encoding issues in HTML files
# This script will convert garbled Chinese text back to proper UTF-8 encoding

# Define the garbled text to proper Chinese mapping
$encodingMap = @{
    '缁熻鍒嗘瀽' = '统计分析'
    '鍏ュ簱鍘嗗彶' = '入库历史'
    '鍘嗗彶璁板綍' = '历史记录'
    '鍑哄簱鍘嗗彶' = '出库历史'
    '娣诲姞鍏ュ簱鍗?' = '添加入库单'
    '缂栬緫鍏ュ簱鍗?' = '编辑入库单'
    '鍏ュ簱绠＄悊' = '入库管理'
    '棣栭〉' = '首页'
    '搴撳瓨鐩樼偣' = '库存盘点'
    '搴撳瓨绠＄悊' = '库存管理'
    '搴撳瓨鎶ヨ〃' = '库存报表'
    '娣诲姞鍑哄簱鍗?' = '添加出库单'
    '缂栬緫鍑哄簱鍗?' = '编辑出库单'
    '鍑哄簱绠＄悊' = '出库管理'
    '鏁版嵁澶囦唤' = '数据备份'
    '鎿嶄綔璁板綍' = '操作记录'
    '绯荤粺鏃ュ織' = '系统日志'
    '娣诲姞瑙掕壊' = '添加角色'
    '鍒嗛厤鏉冮檺' = '分配权限'
    '缂栬緫瑙掕壊' = '编辑角色'
    '瑙掕壊绠＄悊' = '角色管理'
    '娣诲姞鐢ㄦ埛' = '添加用户'
    '缂栬緫鐢ㄦ埛' = '编辑用户'
    '鐢ㄦ埛绠＄悊' = '用户管理'
    '娉ㄥ唽' = '注册'
    '涓汉璧勬枡' = '个人资料'
    '鐢ㄦ埛鍚?' = '用户名'
    '閭' = '邮箱'
    '鎵嬫満鍙?' = '手机号'
    '淇濆瓨' = '保存'
    '娉ㄩ攢' = '注销'
    '宸插叆搴?' = '已入库'
    '寰呭叆搴?' = '待入库'
    '缂栬緫' = '编辑'
    '鍒犻櫎' = '删除'
    '浠撳偍绠＄悊绯荤粺' = '仓储管理系统'
    '鍏ュ簱鍗曞彿' = '入库单号'
    '渚涘簲鍟?' = '供应商'
    '鎿嶄綔鍛?' = '操作员'
    '鎬婚噾棰?' = '总金额'
    '鐘舵€?' = '状态'
    '鍒涘缓鏃堕棿' = '创建时间'
    '瀹㈡埛' = '客户'
    '鍑哄簱鍗曞彿' = '出库单号'
    '寮€濮嬫棩鏈?' = '开始日期'
    '缁撴潫鏃ユ湡' = '结束日期'
    '鎿嶄綔' = '操作'
    '鐢熸垚鍒嗘瀽' = '生成分析'
    '姹囨€讳俊鎭?' = '汇总信息'
    '鎬诲叆搴?' = '总入库'
    '鎬诲嚭搴?' = '总出库'
    '鍑€閲戦' = '净金额'
    '杩斿洖鍘嗗彶璁板綍' = '返回历史记录'
    '宸叉湁璐﹀彿锛熺珛鍗崇櫥褰?' = '已有账号？立即登录'
}

# Find all HTML files in the application/view directory
$htmlFiles = Get-ChildItem -Path "application/view" -Recurse -Filter "*.html"

Write-Host "Found $($htmlFiles.Count) HTML files to process..." -ForegroundColor Green

$fixedCount = 0

foreach ($file in $htmlFiles) {
    $content = Get-Content -Path $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    $hasGarbled = $false
    
    # Check if file contains any garbled text
    foreach ($garbled in $encodingMap.Keys) {
        if ($content -match [regex]::Escape($garbled)) {
            $hasGarbled = $true
            break
        }
    }
    
    if ($hasGarbled) {
        Write-Host "Fixing encoding in: $($file.FullName)" -ForegroundColor Yellow
        
        # Replace all garbled text with proper Chinese
        foreach ($garbled in $encodingMap.Keys) {
            $proper = $encodingMap[$garbled]
            $content = $content -replace [regex]::Escape($garbled), $proper
        }
        
        # Save the fixed content
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8
        $fixedCount++
        Write-Host "✓ Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Encoding fix completed!" -ForegroundColor Green
Write-Host "Fixed $fixedCount files out of $($htmlFiles.Count) total files." -ForegroundColor Cyan