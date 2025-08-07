# Troubleshooting Guide for "Add New Product" Not Working

## Quick Test Steps:

### 1. **Open the test page I created**
Navigate to: `http://your-domain/doko/public/test-product-api.html`
This will test the API directly and show you exactly what's happening.

### 2. **Check Browser Console**
1. Open your admin panel
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Try to add a product
5. Look for any error messages (they will be red)

### 3. **Check Server Logs**
The API now has detailed logging. Check your web server error logs for messages starting with:
- `ADMIN-PRODUCTS:`
- `CREATE PRODUCT:`

## Common Issues and Solutions:

### **Issue 1: Authentication Problems**
**Symptoms:** Error message "Admin access required" or "User not logged in"
**Solution:** Make sure you're logged in as an admin user

### **Issue 2: Database Connection Issues**
**Symptoms:** 500 Internal Server Error, no response
**Solution:** Check if:
- Database server is running
- Database `doko_ecommerce` exists
- Tables are created from schema file

### **Issue 3: Missing Database Tables**
**Symptoms:** SQL errors about missing tables
**Solution:** Run the database schema file:
```sql
-- Import the schema file:
SOURCE /path/to/doko_schema.sql;
```

### **Issue 4: Form Field Issues**
**Symptoms:** "Product name and price are required" even when filled
**Solution:** Check if form fields have correct `name` attributes

### **Issue 5: JavaScript Errors**
**Symptoms:** Nothing happens when clicking "Add Product"
**Solution:** Check browser console for JavaScript errors

## Manual Testing:

You can test the API directly using curl or similar:

```bash
curl -X POST "http://your-domain/doko/public/api/admin-products.php" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "price": 99.99,
    "stock": 10,
    "category_id": 1
  }'
```

## Debug Information Added:

I've added extensive logging to the API. Check your error logs for these messages:
- Authentication status
- Input data received
- Generated SKU and slug
- Database operation results

## Next Steps:

1. Try the test page first
2. Check browser console
3. Tell me what specific error messages you see
4. I can provide more targeted fixes based on the exact error
