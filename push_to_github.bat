@echo off
chcp 65001 >nul
echo.
echo =============================================
echo    仓储管理系统 - 推送至GitHub
echo =============================================
echo.

echo 步骤1: 检查Git是否已安装...
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Git未安装，请先下载并安装Git:
    echo https://git-scm.com/download/win
    echo.
    echo 安装完成后，请重新运行此脚本。
    echo.
    pause
    exit /b 1
)

echo ✓ Git已安装

echo.
echo 步骤2: 初始化Git仓库...
git init
if %errorlevel% neq 0 (
    echo 初始化Git仓库失败
    pause
    exit /b 1
)

echo.
echo 步骤3: 添加所有文件到暂存区...
git add .

echo.
echo 步骤4: 创建初始提交...
git config user.name "Your Name"
git config user.email "your.email@example.com"
git commit -m "Initial commit: PHP仓储管理系统(ThinkPHP版)"

echo.
echo 步骤5: 添加远程仓库并推送...
set REPO_URL=git@github.com:chinalizhiyang/thinkPHP_warehouse.git

echo 正在使用仓库地址: %REPO_URL%
git remote add origin %REPO_URL%
git branch -M main
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo ✓ 项目已成功推送到GitHub!
    echo.
) else (
    echo.
    echo ✗ 推送失败，请检查仓库URL和网络连接
    echo.
)

echo =============================================
echo GitHub推送完成！
echo =============================================
pause