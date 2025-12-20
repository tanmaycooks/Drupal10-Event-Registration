ss@echo off
echo ========================================
echo Starting Drupal Development Server
echo ========================================
echo.
echo Server will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
echo Current directory: %CD%
echo.

php -S localhost:8000 -t web
