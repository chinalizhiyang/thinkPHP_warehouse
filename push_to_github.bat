@echo off
echo.
echo =============================================
echo    Warehouse System - Push to GitHub
echo =============================================
echo.

echo Step 1: Checking if Git is installed...
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Git is not installed. Please download and install Git:
    echo https://git-scm.com/download/win
    echo.
    echo After installation, please run this script again.
    echo.
    pause
    exit /b 1
)

echo [OK] Git is installed

echo.
echo Step 2: Initializing Git repository...
git init
if %errorlevel% neq 0 (
    echo Failed to initialize Git repository
    pause
    exit /b 1
)

echo.
echo Step 3: Adding all files to staging area...
git add .

echo.
echo Step 4: Creating initial commit...
git config user.name "Chinalizhiyang"
git config user.email "chinalizhiyang@163.com"
git commit -m "Initial commit: PHP Warehouse Management System (ThinkPHP)"

echo.
echo Step 5: Adding remote repository and pushing...
set REPO_URL=git@github.com:chinalizhiyang/thinkPHP_warehouse.git

echo Using repository URL: %REPO_URL%
git remote add origin %REPO_URL%
git branch -M main
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo [OK] Project successfully pushed to GitHub!
    echo.
) else (
    echo.
    echo [ERROR] Push failed. Please check repository URL and network connection.
    echo.
)

echo =============================================
echo GitHub push completed!
echo =============================================
pause