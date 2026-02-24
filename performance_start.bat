@echo off
chcp 65001 >nul
echo.
echo =============================================
echo    仓储管理系统 - 性能优化版启动
echo =============================================
echo.

echo 正在启动优化版服务...
echo 访问地址: http://localhost:8080
echo 性能测试: http://localhost:8080/performance_test.html
echo 按 Ctrl+C 停止服务
echo.

REM 启动PHP内置服务器（优化配置）
php -S localhost:8080

pause