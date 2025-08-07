# DOKO E-commerce Unit Testing Guide

## 🎯 Quick Start

This guide helps you run comprehensive unit tests for the DOKO e-commerce system using multiple methods.

## 📋 Prerequisites

Before running tests, ensure you have:
- ✅ PHP installed (any version 7.4+)
- ✅ MySQL/MariaDB running (for database tests)
- ✅ Web server (Apache/Nginx) running locally

## 🚀 Installation & Setup

### Method 1: Composer Setup (Recommended)

1. **Install Composer Dependencies**
   ```bash
   # Windows Command Prompt
   php composer.phar install --dev
   
   # Or if php is in PATH
   composer install --dev
   ```

2. **Verify Installation**
   ```bash
   # Check if vendor directory exists
   dir vendor
   
   # Test PHPUnit installation
   php vendor/bin/phpunit --version
   ```

### Method 2: Manual Setup (Fallback)

If Composer fails, you can still run tests using our custom test runner:

```bash
# Run custom test runner (no dependencies needed)
php tests/run-tests.php
```

## 🧪 Running Tests

### Using Composer Scripts (After composer install)

```bash
# Run all tests
composer test

# Run specific test suites
composer test-unit          # Unit tests only
composer test-integration   # Integration tests only  
composer test-api          # API tests only

# Generate coverage report
composer test-coverage      # Creates coverage/ directory
```

### Using PHPUnit Directly

```bash
# Run all tests
php vendor/bin/phpunit --configuration tests/phpunit.xml

# Run specific test suites
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Unit
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite Integration
php vendor/bin/phpunit --configuration tests/phpunit.xml --testsuite API

# Run with verbose output
php vendor/bin/phpunit --configuration tests/phpunit.xml --verbose

# Generate HTML coverage report
php vendor/bin/phpunit --configuration tests/phpunit.xml --coverage-html coverage/
```

### Using Batch Scripts (Windows)

```cmd
REM Install dependencies
test.bat install

REM Run all tests
test.bat test

REM Run specific test suites
test.bat test-unit
test.bat test-integration  
test.bat test-api

REM Generate coverage report
test.bat test-coverage

REM Run custom test runner (no PHPUnit needed)
test.bat test-custom
```

### Using PowerShell Scripts (Windows)

```powershell
# Install dependencies
.\test.ps1 install

# Run all tests
.\test.ps1 test

# Run specific test suites
.\test.ps1 unit
.\test.ps1 integration
.\test.ps1 api

# Generate coverage report
.\test.ps1 coverage

# Run custom test runner
.\test.ps1 custom

# Check environment status
.\test.ps1 status
```

## 📊 Test Suites Overview

### Unit Tests (43 tests)
- **AuthControllerTest**: 10 tests - Login, logout, registration, roles
- **CartApiTest**: 10 tests - Cart operations, stock validation
- **ProductsApiTest**: 12 tests - Product listing, search, filtering  
- **OrdersApiTest**: 11 tests - Order processing, status updates

### Integration Tests (7 tests)
- **UserAuthIntegrationTest**: Complete authentication workflows
- **ShoppingWorkflowIntegrationTest**: End-to-end shopping experience

### API Tests (19 tests)
- **ApiEndpointTest**: HTTP responses, security, error handling

**Total: 69 comprehensive tests**

## 🔧 Configuration Files

### composer.json
```json
{
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "mockery/mockery": "^1.4"
    },
    "scripts": {
        "test": "phpunit --configuration tests/phpunit.xml",
        "test-unit": "phpunit --configuration tests/phpunit.xml --testsuite Unit",
        "test-integration": "phpunit --configuration tests/phpunit.xml --testsuite Integration",
        "test-api": "phpunit --configuration tests/phpunit.xml --testsuite API",
        "test-coverage": "phpunit --configuration tests/phpunit.xml --coverage-html coverage/"
    }
}
```

### phpunit.xml
Located at `tests/phpunit.xml` - configures test suites, coverage, and environment variables.

## 🎯 Running Specific Tests

### Run Individual Test Files
```bash
# Run specific test class
php vendor/bin/phpunit tests/Unit/AuthControllerTest.php
php vendor/bin/phpunit tests/Unit/CartApiTest.php
php vendor/bin/phpunit tests/Integration/ShoppingWorkflowIntegrationTest.php
```

### Run Single Test Method
```bash
# Run specific test method
php vendor/bin/phpunit --filter testUserCanLogin tests/Unit/AuthControllerTest.php
php vendor/bin/phpunit --filter testCompleteShoppingWorkflow tests/Integration/
```

## 📈 Coverage Reports

After running tests with coverage:

```bash
# Generate HTML coverage report
composer test-coverage

# View coverage report
start coverage/index.html    # Windows
open coverage/index.html     # macOS  
xdg-open coverage/index.html # Linux
```

Coverage reports show:
- ✅ Line coverage percentage
- ✅ Method coverage details
- ✅ Class coverage analysis
- ✅ Uncovered code highlighting

## 🛠️ Troubleshooting

### Common Issues & Solutions

#### 1. "composer: command not found"
```bash
# Use composer.phar directly
php composer.phar install --dev
php composer.phar test
```

#### 2. "php: command not found"
- Install PHP from https://php.net/downloads.php
- Add PHP to your system PATH
- Or use XAMPP/WAMP/MAMP which includes PHP

#### 3. "vendor/bin/phpunit: No such file"
```bash
# Install dependencies first
php composer.phar install --dev

# Or use custom test runner
php tests/run-tests.php
```

#### 4. Database Connection Errors
```bash
# Check database configuration in tests/bootstrap.php
# Ensure MySQL is running
# Create test database: doko_test
```

#### 5. Permission Errors
```bash
# On Windows, run as Administrator
# On Unix/Linux, check file permissions
chmod +x test.ps1
chmod +x vendor/bin/phpunit
```

## 🎨 Test Output Examples

### Successful Test Run
```
PHPUnit 9.5.28 by Sebastian Bergmann and contributors.

...............................................................  63 / 69 ( 91%)
......                                                           69 / 69 (100%)

Time: 00:02.123, Memory: 18.00 MB

OK (69 tests, 156 assertions)
```

### Failed Test Example
```
PHPUnit 9.5.28 by Sebastian Bergmann and contributors.

...............F.....................

FAILURES!
Tests: 69, Assertions: 155, Failures: 1.

There was 1 failure:

1) AuthControllerTest::testUserCanLogin
Expected true but got false
```

### Coverage Summary
```
Code Coverage Report:
  2023-08-07 21:30:45

 Summary:
  Classes: 85.71% (12/14)
  Methods: 92.31% (48/52)
  Lines:   89.47% (272/304)
```

## 🏆 Best Practices

### Before Running Tests
1. ✅ Start your web server (Apache/Nginx)
2. ✅ Start MySQL/MariaDB service
3. ✅ Create `doko_test` database
4. ✅ Run `composer install --dev`

### During Development
1. ✅ Run tests after each code change
2. ✅ Focus on failing tests first
3. ✅ Check coverage reports regularly
4. ✅ Write tests for new features

### Continuous Integration
1. ✅ Run full test suite before commits
2. ✅ Check coverage doesn't drop below 80%
3. ✅ Run different test suites in parallel
4. ✅ Use `composer test-coverage` for reports

## 📚 Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Composer Documentation](https://getcomposer.org/doc/)
- [DOKO System Architecture](README.md)
- [API Documentation](public/api/)

---

**Happy Testing! 🎉**

The DOKO e-commerce system is now equipped with comprehensive unit testing covering authentication, cart operations, product management, order processing, and API endpoints.
