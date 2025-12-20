# Drupal 10 Setup Script for Event Registration Module
# Run this script in PowerShell

Write-Host '========================================' -ForegroundColor Cyan
Write-Host 'Drupal 10 Installation Script' -ForegroundColor Cyan
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ''

# Step 0: Check Prerequisites
Write-Host 'Checking prerequisites...' -ForegroundColor Yellow

# Check PHP by running it (more reliable than Get-Command)
php --version | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host 'ERROR: PHP is not installed or not in your PATH.' -ForegroundColor Red
    Write-Host 'Please install PHP 8.1+ before running this script.' -ForegroundColor Red
    Read-Host 'Press Enter to exit'
    exit 1
}
# Safe way to get PHP version
$phpVer = php -r 'echo PHP_VERSION;'
Write-Host "[OK] PHP found: $phpVer" -ForegroundColor Green

$composerCmd = 'composer'

# Check Composer by running it
composer --version | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host 'Composer not found globally. Checking for local composer.phar...' -ForegroundColor Yellow
    
    if (-not (Test-Path 'composer.phar')) {
        Write-Host 'Downloading composer.phar...' -ForegroundColor Yellow
        try {
            # Use native PowerShell download
            $url = 'https://getcomposer.org/installer'
            $setupFile = 'composer-setup.php'
            Invoke-WebRequest -Uri $url -OutFile $setupFile
            
            # Run setup
            php $setupFile
            
            # Cleanup
            if (Test-Path $setupFile) {
                Remove-Item $setupFile
            }
        }
        catch {
            Write-Host "ERROR: Failed to download Composer. Details: $_" -ForegroundColor Red
            Read-Host 'Press Enter to exit'
            exit 1
        }
    }
    
    if (Test-Path 'composer.phar') {
        $composerCmd = 'php composer.phar'
        Write-Host '[OK] Using local composer.phar' -ForegroundColor Green
    }
    else {
        Write-Host 'ERROR: Could not find or download composer.' -ForegroundColor Red
        Read-Host 'Press Enter to exit'
        exit 1
    }
}
else {
    Write-Host '[OK] Composer found globally' -ForegroundColor Green
}
Write-Host ''

# Step 1: Navigate to user directory
# Store current module location before changing directory
$modulePath = Get-Location
Set-Location $env:USERPROFILE
Write-Host "Current directory: $(Get-Location)" -ForegroundColor Yellow
Write-Host ''

# Step 2: Create Drupal 10 project
Write-Host '[Step 1/5] Creating Drupal 10 project...' -ForegroundColor Green
Write-Host 'This will take 3-5 minutes. Please wait...' -ForegroundColor Yellow

# Execute composer create-project
if ($composerCmd -eq 'composer') {
    composer create-project drupal/recommended-project drupal10-event-site --no-interaction
}
else {
    php "$modulePath\composer.phar" create-project drupal/recommended-project drupal10-event-site --no-interaction
}

if ($LASTEXITCODE -ne 0) {
    Write-Host 'ERROR: Failed to create Drupal project' -ForegroundColor Red
    Read-Host 'Press Enter to exit'
    exit 1
}
Write-Host 'Drupal project created successfully' -ForegroundColor Green
Write-Host ''

# Step 3: Navigate into project
Set-Location drupal10-event-site
Write-Host ''

# Step 4: Create custom modules directory
Write-Host '[Step 2/5] Creating custom modules directory...' -ForegroundColor Green
New-Item -ItemType Directory -Force -Path 'web\modules\custom' | Out-Null
Write-Host 'Custom modules directory created' -ForegroundColor Green
Write-Host ''

# Step 5: Copy Event Registration module
Write-Host '[Step 3/5] Copying Event Registration module...' -ForegroundColor Green
$dest = 'web\modules\custom\event_registration'
Copy-Item -Recurse $modulePath $dest

if ($LASTEXITCODE -ne 0) {
    Write-Host 'ERROR: Failed to copy module' -ForegroundColor Red
    Read-Host 'Press Enter to exit'
    exit 1
}
Write-Host 'Module copied successfully' -ForegroundColor Green
Write-Host ''

# Step 6: Install Drush
Write-Host '[Step 4/5] Installing Drush...' -ForegroundColor Green
if ($composerCmd -eq 'composer') {
    composer require drush/drush --no-interaction
}
else {
    php "$modulePath\composer.phar" require drush/drush --no-interaction
}

Write-Host 'Drush installed' -ForegroundColor Green
Write-Host ''

# Step 7: Display next steps
Write-Host '[Step 5/5] Setup complete!' -ForegroundColor Green
Write-Host ''
Write-Host '========================================' -ForegroundColor Cyan
Write-Host 'NEXT STEPS:' -ForegroundColor Cyan
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ''
Write-Host '1. Install Drupal (choose one option):' -ForegroundColor Yellow
Write-Host ''
Write-Host '   Option A - Browser Installation:' -ForegroundColor White
Write-Host '   Run: php -S localhost:8000 -t web' -ForegroundColor Gray
Write-Host '   Then visit: http://localhost:8000' -ForegroundColor Gray
Write-Host ''
Write-Host '   Option B - Command Line (SQLite):' -ForegroundColor White
Write-Host '   Run: vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.ht.sqlite --site-name=EventSite --account-name=admin --account-pass=admin123 -y' -ForegroundColor Gray
Write-Host ''
Write-Host '2. Enable the module:' -ForegroundColor Yellow
Write-Host '   vendor\bin\drush en event_registration -y' -ForegroundColor Gray
Write-Host ''
Write-Host '3. Open in VS Code:' -ForegroundColor Yellow
Write-Host '   code .' -ForegroundColor Gray
Write-Host ''
Write-Host '========================================' -ForegroundColor Cyan
Write-Host "Installation directory: $(Get-Location)" -ForegroundColor Cyan
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ''
Read-Host 'Press Enter to exit'
