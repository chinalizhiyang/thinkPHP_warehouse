@echo off
chcp 65001 >nul
echo.
echo =============================================
echo    HTML文件乱码快速修复工具
echo =============================================
echo.

echo 正在扫描并修复乱码文件...

powershell -ExecutionPolicy Bypass -Command "
# 定义乱码映射表
$encodingMap = @{
    '缁熻鍒嗘瀽' = '统计分析'
    '鍏ュ簱鍘嗗彶' = '入库历史'
    '鍘嗗彶璁板綍' = '历史记录'
    '鍑哄簱鍘嗗彶' = '出库历史'
    '娣诲姞鍏ュ簱鍗? = '添加入库单'
    '缂栬緫鍏ュ簱鍗? = '编辑入库单'
    '鍏ュ簱绠＄悊' = '入库管理'
    '棣栭〉' = '首页'
    '搴撳瓨鐩樼偣' = '库存盘点'
    '搴撳瓨绠＄悊' = '库存管理'
    '搴撳瓨鎶ヨ〃' = '库存报表'
    '娣诲姞鍑哄簱鍗? = '添加出库单'
    '缂栬緫鍑哄簱鍗? = '编辑出库单'
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
    '鐢ㄦ埛鍚? = '用户名'
    '閭' = '邮箱'
    '鎵嬫満鍙? = '手机号'
    '淇濆瓨' = '保存'
    '娉ㄩ攢' = '注销'
    '宸插叆搴? = '已入库'
    '寰呭叆搴? = '待入库'
    '缂栬緫' = '编辑'
    '鍒犻櫎' = '删除'
    '浠撳偍绠＄悊绯荤粺' = '仓储管理系统'
    '鍏ュ簱鍗曞彿' = '入库单号'
    '渚涘簲鍟? = '供应商'
    '鎿嶄綔鍛? = '操作员'
    '鎬婚噾棰? = '总金额'
    '鐘舵€? = '状态'
    '鍒涘缓鏃堕棿' = '创建时间'
}

# 获取所有HTML文件
$htmlFiles = Get-ChildItem -Path 'application\view' -Recurse -Filter '*.html'

foreach ($file in $htmlFiles) {
    $content = Get-Content $file.FullName -Encoding UTF8 -Raw
    
    # 检查是否存在乱码
    $hasGarbled = $false
    foreach ($badText in $encodingMap.Keys) {
        if ($content.Contains($badText)) {
            $hasGarbled = $true
            break
        }
    }
    
    if ($hasGarbled) {
        Write-Host '修复文件:' $file.Name
        # 执行替换
        foreach ($badText in $encodingMap.Keys) {
            $goodText = $encodingMap[$badText]
            $content = $content -replace [regex]::Escape($badText), $goodText
        }
        # 保存修复后的内容
        Set-Content $file.FullName -Value $content -Encoding UTF8
    }
}

Write-Host '修复完成！'
"

echo.
echo =============================================
echo    修复完成！
echo =============================================
pause