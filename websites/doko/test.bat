@echo off
REM DOKO E-commerce Unit Testing Script
REM This script provides easy commands to run tests using Composer

echo ===================================
echo DOKO E-commerce Testing Framework
echo ===================================
echo.

if "%1"=="install" goto install
if "%1"=="test" goto test
if "%1"=="test-unit" goto test-unit
if "%1"=="test-integration" goto test-integration
if "%1"=="test-api" goto test-api
if "%1"=="test-coverage" goto test-coverage
if "%1"=="test-custom" goto test-custom
if "%1"=="help" goto help
goto help

:install
echo Installing dependencies...
php composer.phar install --dev
if %errorlevel%==0 (
    echo.
    echo ✅ Dependencies installed successfully!
    echo You can now run tests using: test.bat test
) else (
    echo.
    echo ❌ Installation failed. Make sure PHP is installed and in your PATH.
    echo Alternative: Use test-custom to run tests without PHPUnit
)
goto end

:test
echo Running all tests...
php vendor/bin/phpunit --configuration tests/phpunit.xml
goto end

:test-unit
echo Running unit tests only...
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Unit
goto end

:test-integration
echo Running integration tests only...
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Integration
goto end

:test-api
echo Running API tests only...
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite API
goto end

:test-coverage
echo Running tests with coverage report...
php vendor/bin/phpunit --configuration tests/phpunit.xml --coverage-html coverage/
echo.
echo Coverage report generated in coverage/ directory
goto end

:test-custom
echo Running custom test runner (no PHPUnit required)...
php tests/run-tests.php
goto end

:help
echo Usage: test.bat [command]
echo.
echo Commands:
echo   install         - Install PHPUnit and dependencies via Composer
echo   test           - Run all tests using PHPUnit
echo   test-unit      - Run unit tests only
echo   test-integration - Run integration tests only  
echo   test-api       - Run API tests only
echo   test-coverage  - Run tests with HTML coverage report
echo   test-custom    - Run tests using custom runner (no PHPUnit needed)
echo   help           - Show this help message
echo.
echo Examples:
echo   test.bat install
echo   test.bat test
echo   test.bat test-unit
echo   test.bat test-custom
echo.
echo Note: For test-custom, no PHP installation in PATH is required
goto end

:end
