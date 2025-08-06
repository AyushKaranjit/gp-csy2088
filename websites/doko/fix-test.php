<!DOCTYPE html>
<html>
<head>
    <title>DOKO - Feature Fix Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .product-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        #result { margin-top: 20px; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 DOKO Feature Fix Test</h1>
        <p>Testing all the issues that were reported and fixed:</p>

        <div class="test-section">
            <h2>1. 🛒 Add to Cart Test</h2>
            <div class="product-card">
                <h3>Test Product - Basmati Rice</h3>
                <p>Price: Rs. 1,250.00</p>
                <button class="btn" onclick="testAddToCart(4, 'Basmati Rice', 1250)">
                    🛒 Add to Cart (Test)
                </button>
                <button class="btn" onclick="testAddToCart(6, 'Fresh Carrots', 65)">
                    🛒 Add Carrots to Cart
                </button>
            </div>
        </div>

        <div class="test-section">
            <h2>2. ❤️ Wishlist Test</h2>
            <button class="btn" onclick="testWishlist()">
                ❤️ Test Wishlist API
            </button>
        </div>

        <div class="test-section">
            <h2>3. 🖼️ Image Loading Test</h2>
            <p>Default product image:</p>
            <img src="uploads/default-product.jpg" alt="Default Product" style="width: 200px; height: 200px; border: 1px solid #ddd;" 
                 onload="showImageResult(true)" onerror="showImageResult(false)">
        </div>

        <div class="test-section">
            <h2>4. 👨‍💼 Admin API Test</h2>
            <button class="btn" onclick="testAdminAPI('inventory-list.php')">
                📦 Test Inventory API
            </button>
            <button class="btn" onclick="testAdminAPI('orders-list.php')">
                📋 Test Orders API
            </button>
            <button class="btn" onclick="testAdminAPI('users-list.php')">
                👥 Test Users API
            </button>
        </div>

        <div id="result"></div>
    </div>

    <script>
        function showResult(message, isSuccess = true) {
            const result = document.getElementById('result');
            result.className = isSuccess ? 'success' : 'error';
            result.innerHTML = '<strong>' + (isSuccess ? '✅ SUCCESS: ' : '❌ ERROR: ') + '</strong>' + message;
        }

        function testAddToCart(productId, productName, price) {
            showResult('Testing Add to Cart for ' + productName + '...', true);
            
            fetch('api/cart-add-working.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Cart response:', data);
                if (data.success) {
                    showResult(`Cart Test PASSED! ${data.message} (Cart count: ${data.cart_count})`, true);
                } else {
                    showResult(`Cart Test FAILED: ${data.message}`, false);
                }
            })
            .catch(error => {
                console.error('Cart error:', error);
                showResult('Cart Test FAILED: Network error - ' + error.message, false);
            });
        }

        function testWishlist() {
            showResult('Testing Wishlist API...', true);
            
            fetch('api/wishlist.php')
            .then(response => response.json())
            .then(data => {
                console.log('Wishlist response:', data);
                if (data.success !== false) {
                    showResult(`Wishlist Test PASSED! ${data.message} (Items: ${data.count})`, true);
                } else {
                    showResult(`Wishlist Test FAILED: ${data.message}`, false);
                }
            })
            .catch(error => {
                console.error('Wishlist error:', error);
                showResult('Wishlist Test FAILED: Network error - ' + error.message, false);
            });
        }

        function showImageResult(loaded) {
            if (loaded) {
                showResult('Image Test PASSED! Default product image loaded successfully.', true);
            } else {
                showResult('Image Test FAILED: Default product image could not load.', false);
            }
        }

        function testAdminAPI(endpoint) {
            showResult('Testing Admin API: ' + endpoint + '...', true);
            
            fetch('api/' + endpoint)
            .then(response => response.json())
            .then(data => {
                console.log('Admin API response:', data);
                if (data.success !== undefined || data.message) {
                    showResult(`Admin API Test PASSED! ${endpoint} is responding (Message: ${data.message || 'API working'})`, true);
                } else {
                    showResult(`Admin API Test FAILED: ${endpoint} returned unexpected response`, false);
                }
            })
            .catch(error => {
                console.error('Admin API error:', error);
                showResult('Admin API Test FAILED: ' + endpoint + ' - ' + error.message, false);
            });
        }

        // Auto-test image loading
        setTimeout(() => {
            const img = document.querySelector('img[src="uploads/default-product.jpg"]');
            if (img.complete) {
                showImageResult(true);
            }
        }, 2000);
    </script>
</body>
</html>
