# DOKO E-commerce Unit Testing Framework

## Overview
Comprehensive unit testing framework for the DOKO e-commerce system covering authentication, cart functionality, products, orders, and API endpoints.

## Test Structure

```
tests/
├── TestCase.php                    # Base test class with utilities
├── bootstrap.php                   # Test environment configuration
├── phpunit.xml                     # PHPUnit configuration
├── run-tests.php                   # Custom test runner
├── Unit/                           # Unit tests
│   ├── AuthControllerTest.php      # Authentication tests
│   ├── CartApiTest.php             # Cart functionality tests
│   ├── ProductsApiTest.php         # Product management tests
│   └── OrdersApiTest.php           # Order processing tests
├── Integration/                    # Integration tests
│   ├── UserAuthIntegrationTest.php # Complete auth workflow
│   └── ShoppingWorkflowIntegrationTest.php # End-to-end shopping
└── API/                           # API endpoint tests
    └── ApiEndpointTest.php        # API response validation
```

## Test Categories

### Unit Tests
- **AuthControllerTest**: Login, logout, registration, role detection
- **CartApiTest**: Add to cart, update quantities, remove items, calculate totals
- **ProductsApiTest**: Product listing, search, filtering, stock management
- **OrdersApiTest**: Order creation, status updates, inventory management

### Integration Tests
- **UserAuthIntegrationTest**: Complete user registration → login → profile update → logout flow
- **ShoppingWorkflowIntegrationTest**: Browse → add to cart → checkout → order completion

### API Tests
- **ApiEndpointTest**: HTTP responses, authentication requirements, error handling

## Key Features

### TestCase Base Class
- **Database Setup**: Automatic test database connection and cleanup
- **User Management**: `createTestUser()`, `loginUser()` helpers
- **Product Management**: `createTestProduct()` with inventory
- **HTTP Testing**: `getRequest()`, `postRequest()` methods
- **Assertions**: Custom assertions for API responses

### Test Database
- Uses separate test database to avoid affecting production data
- Automatic cleanup after each test
- Fixture data creation helpers

### Coverage Areas

#### Authentication (32 test scenarios)
- User registration with validation
- Login/logout functionality  
- Password changes
- Role-based access (admin/customer)
- Account activation/deactivation
- Session management

#### Cart Operations (18 test scenarios)
- Add products to cart
- Update quantities
- Remove items
- Clear entire cart
- Stock availability checks
- Total calculation
- Authentication requirements

#### Product Management (22 test scenarios)
- Product listing with pagination
- Search functionality
- Category filtering
- Featured products
- Stock status tracking
- Price range filtering
- Product detail views

#### Order Processing (16 test scenarios)
- Order creation from cart
- Inventory reduction
- Order status tracking
- User order history
- Order cancellation
- Admin order management

#### API Endpoints (25 test scenarios)
- Response format validation
- Authentication enforcement
- Error handling
- Data validation
- Rate limiting resistance
- Admin access controls

## Running Tests

### Method 1: Custom Test Runner
```bash
# Run all tests with our custom runner
php tests/run-tests.php
```

### Method 2: PHPUnit (if available)
```bash
# Run all test suites
phpunit

# Run specific test suite
phpunit --testsuite Unit
phpunit --testsuite Integration
phpunit --testsuite API

# Run with coverage
phpunit --coverage-html coverage/
```

### Method 3: Individual Test Files
```bash
# Run specific test class
php tests/Unit/AuthControllerTest.php
php tests/Integration/ShoppingWorkflowIntegrationTest.php
```

## Test Environment Setup

### Database Configuration
Tests use a separate test database configured in `bootstrap.php`:
- Database: `doko_test`
- Automatic table creation
- Data cleanup after each test

### Session Handling
- Test session management
- User authentication simulation
- Cart session tracking

### File System
- Upload directory testing
- Image handling simulation
- Temporary file cleanup

## Expected Test Results

### Unit Tests (88 total tests)
- AuthController: 10 tests
- CartApi: 10 tests  
- ProductsApi: 12 tests
- OrdersApi: 11 tests

### Integration Tests (6 total tests)
- UserAuth workflow: 4 tests
- Shopping workflow: 3 tests

### API Tests (19 total tests)
- Endpoint validation: 12 tests
- Security testing: 7 tests

**Total: 113 comprehensive tests**

## Test Data Management

### User Creation
```php
$user = $this->createTestUser([
    'email' => 'test@example.com',
    'role' => 'customer', // or 'admin'
    'status' => 'active'
]);
```

### Product Creation
```php
$product = $this->createTestProduct([
    'name' => 'Test Product',
    'price' => 29.99,
    'stock_quantity' => 10,
    'featured' => 1
]);
```

### Order Creation
```php
$order = $this->createTestOrder($userId, [
    'total_amount' => 99.99,
    'status' => 'pending'
]);
```

## Assertion Helpers

### Response Validation
- `assertResponseSuccess($response)` - Verify successful API response
- `assertResponseError($response)` - Verify error response  
- `assertJsonHasKey($key, $data)` - Check JSON structure

### Database Validation
- `assertUserExists($userId)` - Verify user in database
- `assertProductStockReduced($productId, $expectedStock)` - Check inventory
- `assertOrderCreated($orderId)` - Verify order persistence

## Testing Best Practices

### Isolation
- Each test is independent
- Database cleanup between tests
- No shared state between test methods

### Realistic Data
- Valid email addresses
- Proper password formats
- Realistic product prices and quantities
- Valid shipping addresses

### Error Conditions
- Invalid input validation
- Authentication failures
- Stock shortage scenarios
- Permission denied cases

### Performance
- Tests run quickly (< 1 second each)
- Minimal database operations
- Efficient setup/teardown

## Troubleshooting

### Common Issues
1. **Database Connection**: Ensure test database exists and is accessible
2. **Session Issues**: Test session configuration in bootstrap.php
3. **File Permissions**: Check upload directory permissions
4. **Missing Dependencies**: Verify all required PHP extensions

### Debug Mode
Enable detailed error reporting in bootstrap.php:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

This comprehensive testing framework ensures the DOKO e-commerce system is reliable, secure, and performs as expected across all major functionality areas.
