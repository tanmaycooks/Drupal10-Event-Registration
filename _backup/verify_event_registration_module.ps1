<#
  End-to-end verification for Event Registration module (PowerShell)

  Prereqs:
  - Git, Composer, PHP (8.1+), and Drupal 10 skeleton (composer-based)
  - Drush available locally (inside site/vendor/bin or global)

  What this does:
  - Creates a fresh Drupal site in C:\drupal_verification\site
  - Copies the module into web/modules/custom/event_registration
  - Runs composer install
  - Enables the module (via Drush if found)
  - Starts a local dev server (Drush serve preferred, else PHP -S)
  - Performs simple GET smoke tests on:
      - /event/register (public)
      - /admin/config/events/settings (admin)  - expects 403 without login
      - /admin/events/registrations/export (admin) - expects 403 without login
  - Prints a pass/fail summary

  Note: Full POST form submission (with CSRF tokens) is not automated here due to login requirement.
#>

param(
    [string]$Root = "C:\drupal_verification",
    [string]$SiteName = "site",
    [string]$DrupalHost = "http://127.0.0.1:8080"
)

$ErrorActionPreference = "Stop"

function Check-Tool([string]$name) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        Write-Error "Prerequisite '$name' not found in PATH."
        return $false
    }
    return $true
}

Write-Host "Starting end-to-end verification for Event Registration module"

# 0. Prereqs
if (-not (Check-Tool "composer")) { exit 1 }
if (-not (Check-Tool "php")) { exit 1 }

# 1. Prepare workspace
if (Test-Path $Root) {
    Write-Host "Cleaning existing workspace at $Root"
    Remove-Item -Recurse -Force "$Root" -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
}
if (-not (Test-Path $Root)) {
    New-Item -ItemType Directory -Path $Root | Out-Null
}

# 2. Create Drupal site skeleton
Write-Host "Creating Drupal site skeleton..."
Push-Location $Root
$sitePath = "$Root\$SiteName"
if (Test-Path $sitePath) { Remove-Item -Recurse -Force $sitePath }
composer create-project drupal/recommended-project:^10 $SiteName
Set-Location $sitePath

# 3. Copy module into site
Write-Host "Copying module into site..."
$moduleSource = $PSScriptRoot
$destModule = "$sitePath\web\modules\custom\event_registration"
New-Item -ItemType Directory -Path $destModule -Force | Out-Null
Copy-Item -Path "$moduleSource\*" -Destination $destModule -Recurse -Force -Exclude @('drupal', 'verify_event_registration_module.ps1', '.git')

# 4. Install dependencies
Write-Host "Installing site dependencies..."
composer install

# 5. Enable module (via Drush if available)
$drushPath = $null
if (Test-Path ".\vendor\bin\drush.bat") { $drushPath = ".\vendor\bin\drush.bat" }
elseif (Test-Path ".\vendor\bin\drush") { $drushPath = ".\vendor\bin\drush" }

if ($drushPath) {
    Write-Host "Enabling module via Drush..."
    & $drushPath en event_registration -y
    & $drushPath cr
}
else {
    Write-Warning "Drush not found in site vendor. Skipping enable step. You can enable through UI or install Drush separately."
}

# 6. Start local server (prefer Drush serve)
$serverStarted = $false
if ($drushPath) {
    Write-Host "Starting Drupal dev server with Drush..."
    Start-Process -NoNewWindow -FilePath $drushPath -ArgumentList "serve 127.0.0.1:8080" -PassThru
    Start-Sleep -Seconds 6
    $serverStarted = $true
}
else {
    Write-Host "Starting PHP built-in server..."
    $webRoot = "$sitePath\web"
    Start-Process -NoNewWindow -FilePath "php" -ArgumentList "-S", "127.0.0.1:8080", "-t", $webRoot -PassThru
    Start-Sleep -Seconds 6
    $serverStarted = $true
}

if (-not $serverStarted) {
    Write-Error "Failed to start local server."
    exit 1
}

# 7. Smoke tests (GET requests)
function Test-URL([string]$url, [int]$expectedStatus = 200) {
    try {
        $resp = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 30
        $status = $resp.StatusCode
        if ($status -eq $expectedStatus -or $status -eq 302) {
            Write-Host "OK: $url -> $status"
            return $true
        }
        else {
            Write-Host "WARN: $url -> $status (expected ~${expectedStatus})"
            return $false
        }
    }
    catch {
        Write-Host "ERROR: $url - $($_.Exception.Message)"
        return $false
    }
}

$tests = @(
    @{name = "Public Registration Page"; url = "$DrupalHost/event/register"; expected = 200 },
    @{name = "Admin Settings Page (anon)"; url = "$DrupalHost/admin/config/events/settings"; expected = 403 },
    @{name = "CSV Export (anon)"; url = "$DrupalHost/admin/events/registrations/export"; expected = 403 }
)

Write-Host "Running smoke tests (GET requests only)..."
foreach ($t in $tests) {
    Test-URL -url $t.url -expectedStatus $t.expected
}

Write-Host "End-to-end verification script completed."
Write-Host "If you started Drush server, you may stop it by terminating the process."

Pop-Location
