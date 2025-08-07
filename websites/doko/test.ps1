# DOKO E-commerce Unit Testing PowerShell Script
# Provides easy commands to run tests with or without Composer

param(
    [Parameter(Position=0)]
    [string]$Command = "help"
)

Write-Host "====================================" -ForegroundColor Cyan
Write-Host "DOKO E-commerce Testing Framework" -ForegroundColor Cyan  
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

function Test-PHPAvailable {
    try {
        php --version | Out-Null
        return $true
    } catch {
        return $false
    }
}

function Test-ComposerInstalled {
    return Test-Path "vendor/autoload.php"
}

switch ($Command.ToLower()) {
    "install" {
        Write-Host "Installing dependencies..." -ForegroundColor Yellow
        if (Test-PHPAvailable) {
            php composer.phar install --dev
            if ($LASTEXITCODE -eq 0) {
                Write-Host ""
                Write-Host "✅ Dependencies installed successfully!" -ForegroundColor Green
                Write-Host "You can now run tests using: .\test.ps1 test" -ForegroundColor Green
            } else {
                Write-Host ""
                Write-Host "❌ Installation failed." -ForegroundColor Red
                Write-Host "Alternative: Use 'custom' to run tests without PHPUnit" -ForegroundColor Yellow
            }
        } else {
            Write-Host "❌ PHP not found in PATH." -ForegroundColor Red
            Write-Host "Please install PHP or use: .\test.ps1 custom" -ForegroundColor Yellow
        }
    }
    
    "test" {
        Write-Host "Running all tests..." -ForegroundColor Yellow
        if (Test-ComposerInstalled -and Test-PHPAvailable) {
            php vendor/bin/phpunit --configuration tests/phpunit.xml
        } else {
            Write-Host "PHPUnit not available. Using custom runner..." -ForegroundColor Yellow
            php tests/run-tests.php
        }
    }
    
    "unit" {
        Write-Host "Running unit tests only..." -ForegroundColor Yellow
        if (Test-ComposerInstalled -and Test-PHPAvailable) {
            php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Unit
        } else {
            Write-Host "PHPUnit not available. Running custom tests..." -ForegroundColor Yellow
            php tests/run-tests.php
        }
    }
    
    "integration" {
        Write-Host "Running integration tests only..." -ForegroundColor Yellow
        if (Test-ComposerInstalled -and Test-PHPAvailable) {
            php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Integration
        } else {
            Write-Host "PHPUnit not available. Running custom tests..." -ForegroundColor Yellow
            php tests/run-tests.php
        }
    }
    
    "api" {
        Write-Host "Running API tests only..." -ForegroundColor Yellow
        if (Test-ComposerInstalled -and Test-PHPAvailable) {
            php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite API
        } else {
            Write-Host "PHPUnit not available. Running custom tests..." -ForegroundColor Yellow
            php tests/run-tests.php
        }
    }
    
    "coverage" {
        Write-Host "Running tests with coverage report..." -ForegroundColor Yellow
        if (Test-ComposerInstalled -and Test-PHPAvailable) {
            php vendor/bin/phpunit --configuration tests/phpunit.xml --coverage-html coverage/
            Write-Host ""
            Write-Host "Coverage report generated in coverage/ directory" -ForegroundColor Green
        } else {
            Write-Host "❌ Coverage requires PHPUnit. Please run: .\test.ps1 install" -ForegroundColor Red
        }
    }
    
    "custom" {
        Write-Host "Running custom test runner (no PHPUnit required)..." -ForegroundColor Yellow
        if (Test-PHPAvailable) {
            php tests/run-tests.php
        } else {
            Write-Host "❌ PHP is required. Please install PHP first." -ForegroundColor Red
        }
    }
    
    "status" {
        Write-Host "Testing Environment Status:" -ForegroundColor Cyan
        Write-Host "- PHP Available: " -NoNewline
        if (Test-PHPAvailable) {
            Write-Host "✅ Yes" -ForegroundColor Green
            php --version | Select-Object -First 1
        } else {
            Write-Host "❌ No" -ForegroundColor Red
        }
        
        Write-Host "- Composer Dependencies: " -NoNewline
        if (Test-ComposerInstalled) {
            Write-Host "✅ Installed" -ForegroundColor Green
        } else {
            Write-Host "❌ Not Installed" -ForegroundColor Red
        }
        
        Write-Host "- Test Files: " -NoNewline
        $testCount = (Get-ChildItem -Path "tests" -Filter "*Test.php" -Recurse).Count
        Write-Host "✅ $testCount test files found" -ForegroundColor Green
    }
    
    default {
        Write-Host "Usage: .\test.ps1 [command]" -ForegroundColor White
        Write-Host ""
        Write-Host "Commands:" -ForegroundColor White
        Write-Host "  install     - Install PHPUnit and dependencies via Composer" -ForegroundColor Gray
        Write-Host "  test        - Run all tests (PHPUnit if available, custom otherwise)" -ForegroundColor Gray
        Write-Host "  unit        - Run unit tests only" -ForegroundColor Gray
        Write-Host "  integration - Run integration tests only" -ForegroundColor Gray
        Write-Host "  api         - Run API tests only" -ForegroundColor Gray
        Write-Host "  coverage    - Run tests with HTML coverage report (requires PHPUnit)" -ForegroundColor Gray
        Write-Host "  custom      - Run tests using custom runner (no PHPUnit needed)" -ForegroundColor Gray
        Write-Host "  status      - Show testing environment status" -ForegroundColor Gray
        Write-Host "  help        - Show this help message" -ForegroundColor Gray
        Write-Host ""
        Write-Host "Examples:" -ForegroundColor White
        Write-Host "  .\test.ps1 install" -ForegroundColor Gray
        Write-Host "  .\test.ps1 test" -ForegroundColor Gray
        Write-Host "  .\test.ps1 unit" -ForegroundColor Gray
        Write-Host "  .\test.ps1 custom" -ForegroundColor Gray
        Write-Host ""
        Write-Host "Note: Use 'custom' command if PHP is not in your PATH" -ForegroundColor Yellow
    }
}
