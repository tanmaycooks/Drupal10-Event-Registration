@echo off
setlocal EnableDelayedExpansion

echo ========================================
echo Drupal 10 Installation Script
echo ========================================
echo.

REM Step 0: Check Prerequisites
echo Checking prerequisites...

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in your PATH.
    echo Please install PHP 8.1+ before running this script.
    echo.
    echo Tip: If you just installed PHP, try restarting your terminal.
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('php -r "echo PHP_VERSION;"') do set PHP_VER=%%i
echo [OK] PHP found: %PHP_VER%

set COMPOSER_CMD=composer
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo Composer not found globally. Checking for local composer.phar...
    if exist composer.phar (
        set COMPOSER_CMD=php composer.phar
        echo [OK] Using local composer.phar
    ) else (
        echo Downloading composer.phar...
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        if exist composer-setup.php (
            php composer-setup.php
            del composer-setup.php
            set COMPOSER_CMD=php composer.phar
            echo [OK] Downloaded composer.phar
        ) else (
            echo ERROR: Failed to download Composer.
            pause
            exit /b 1
        )
    )
) else (
    echo [OK] Composer found globally
)
echo.

REM Step 1: Navigate to user directory
cd /d "%USERPROFILE%"
echo Current directory: %CD%
echo.

REM Step 2: Create Drupal 10 project
echo [Step 1/5] Creating Drupal 10 project...
echo This will take 3-5 minutes. Please wait...
echo Running: %COMPOSER_CMD% create-project drupal/recommended-project drupal10-event-site --no-interaction

call %COMPOSER_CMD% create-project drupal/recommended-project drupal10-event-site --no-interaction
if %errorlevel% neq 0 (
    echo ERROR: Failed to create Drupal project
    pause
    exit /b 1
)
echo Drupal project created successfully
echo.

REM Step 3: Navigate into project
cd drupal10-event-site
echo.

REM Step 4: Create custom modules directory
echo [Step 2/5] Creating custom modules directory...
if not exist "web\modules\custom" mkdir "web\modules\custom"
echo Custom modules directory created
echo.

REM Step 5: Copy Event Registration module
echo [Step 3/5] Copying Event Registration module...
REM Use %~dp0 to get the directory where this script is located (the module dir)
set "SOURCE_DIR=%~dp0"
REM Remove trailing backslash if present
if "%SOURCE_DIR:~-1%"=="\" set "SOURCE_DIR=%SOURCE_DIR:~0,-1%"

echo Copying from: "%SOURCE_DIR%"
xcopy /E /I /Y "%SOURCE_DIR%" "web\modules\custom\event_registration" >nul
if %errorlevel% neq 0 (
    echo ERROR: Failed to copy module
    pause
    exit /b 1
)
echo Module copied successfully
echo.

REM Step 6: Install Drush
echo [Step 4/5] Installing Drush...
call %COMPOSER_CMD% require drush/drush --no-interaction
echo Drush installed
echo.

REM Step 7: Display next steps
echo [Step 5/5] Setup complete!
echo.
echo ========================================
echo NEXT STEPS:
echo ========================================
echo.
echo 1. Install Drupal (choose one option):
echo.
echo    Option A - Browser Installation:
echo    Run: php -S localhost:8000 -t web
echo    Then visit: http://localhost:8000
echo.
echo    Option B - Command Line (SQLite):
echo    Run: vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.ht.sqlite --site-name="Event Site" --account-name=admin --account-pass=admin123 -y
echo.
echo 2. Enable the module:
echo    vendor\bin\drush en event_registration -y
echo.
echo 3. Open in VS Code:
echo    code .
echo.
echo ========================================
echo Installation directory: %CD%
echo ========================================
echo.
pause
