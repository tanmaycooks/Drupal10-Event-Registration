@echo off
echo ========================================
echo Starting Drupal Development Server
echo ========================================
echo.
echo Server will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

REM Get the directory where this script is located
set "SCRIPT_DIR=%~dp0"

REM Remove trailing backslash if present
if "%SCRIPT_DIR:~-1%"=="\" set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"

REM Check if drupal directory exists
if exist "%SCRIPT_DIR%\drupal\web\index.php" (
    echo Found Drupal installation in: %SCRIPT_DIR%\\drupal
    
    REM Change to the drupal/web directory
    pushd "%SCRIPT_DIR%\\drupal\\web"
    
    echo Starting server from: %CD%
    echo.
    
    REM Start the PHP server
    php -S localhost:8000
    
    REM Return to original directory when done
    popd
) else (
    echo ERROR: Could not find Drupal installation
    echo Expected location: %SCRIPT_DIR%\drupal\web\index.php
    echo.
    pause
    exit /b 1
)
