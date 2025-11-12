# 🧪 Testing Guide - Cart & Checkout System

This guide will help you thoroughly test all cart and checkout functions before building the frontend.

---

## 📋 Prerequisites

Before testing, ensure:

- ✅ Your server/hosting is running (Apache/Nginx + MySQL)
- ✅ Database exists and is properly configured
- ✅ All files are uploaded to your server
- ✅ At least one product exists in the `products` table
- ✅ At least one customer exists in the `customer` table
- ✅ You can login to the system

---

## 🎯 Testing Methods

We've provided **3 testing methods** to verify everything works:

### **Method 1: Database Verification** (Start Here!)
**File:** `verify_database.php`
**URL:** `https://your-domain.com/verify_database.php`

**What it does:**
- ✅ Checks if all required tables exist
- ✅ Verifies all required columns exist
- ✅ Counts rows in each table
- ✅ Provides recommendations if issues found

**How to use:**
1. Open the URL in your browser (replace `your-domain.com` with your actual domain)
2. Review the summary at the top
3. Check the detailed table analysis
4. Follow any recommendations if tables/columns are missing

**Expected Result:** All tables should exist with "✓ EXISTS" status

---

### **Method 2: Direct Function Testing** (Backend Test)
**File:** `test_cart_functions.php`
**URL:** `https://your-domain.com/test_cart_functions.php`

**What it does:**
- ✅ Tests Cart Class methods directly
- ✅ Tests Cart Controller functions
- ✅ Tests Order Class methods
- ✅ Tests Order Controller functions
- ✅ Runs complete workflow from add to checkout

**How to use:**
1. Open `test_cart_functions.php` in a text editor
2. Update these lines with valid IDs from your database:
   ```php
   $test_customer_id = 1;  // Valid customer ID
   $test_product_id = 1;   // Valid product ID
   ```
3. Save the file
4. Open the URL in your browser
5. Review all test results

**Expected Result:** All tests should show "✅ PASS" with green background

**Common Issues:**
- ❌ **Connection Failed:** Check database credentials in `settings/db_cred.php`
- ❌ **Invalid IDs:** Make sure customer and product IDs exist
- ❌ **SQL Errors:** Check error logs for detailed messages

---

### **Method 3: AJAX Action Testing** (Frontend Simulation)
**File:** `test_actions.php`
**URL:** `https://your-domain.com/test_actions.php`

**What it does:**
- ✅ Tests all action scripts via AJAX (simulates frontend calls)
- ✅ Tests CSRF token validation
- ✅ Tests JSON responses
- ✅ Provides interactive console output
- ✅ Supports both logged-in and guest users

**How to use:**
1. **First, login to your system** (some tests require login)
2. Open the URL in your browser
3. Enter a valid Product ID in the configuration
4. Click individual test buttons OR run full workflow
5. Check the console output for results

**Test Buttons:**
- **➕ Test Add to Cart** - Adds product to cart
- **✏️ Test Update Quantity** - Updates cart item quantity
- **🗑️ Test Remove from Cart** - Removes item from cart
- **🧹 Test Empty Cart** - Clears entire cart
- **💳 Test Process Checkout** - Complete checkout (requires login)
- **🎯 Run Full Workflow Test** - Runs complete flow automatically
- **🚀 Run All Tests** - Runs every test sequentially

**Expected Result:** All tests should return JSON with `"success": true`

**Console Output Example:**
```
[10:30:45] 🧪 Testing: Add to Cart
[10:30:45] Response: {
  "success": true,
  "message": "Product added to cart successfully!",
  "cart_count": 1,
  "cart_total": "99.99"
}
[10:30:45] ✅ Cart Count: 1, Total: $99.99
```

---

## 🔍 Testing Checklist

Use this checklist to ensure everything works:

### Database Setup
- [ ] All tables exist (cart, orders, orderdetails, payment, products, customer)
- [ ] At least 3 products exist in products table
- [ ] At least 1 customer account exists
- [ ] Can login successfully

### Cart Functions
- [ ] Add to cart (new item)
- [ ] Add to cart (existing item - should update quantity)
- [ ] Get cart items (returns array with products)
- [ ] Get cart count (returns correct number)
- [ ] Get cart total (returns correct sum)
- [ ] Update cart quantity
- [ ] Remove from cart
- [ ] Clear cart

### Order Functions
- [ ] Generate invoice number (unique)
- [ ] Generate order reference (format: ORD-YYYYMMDD-XXXXX)
- [ ] Create order
- [ ] Add order details
- [ ] Create payment
- [ ] Get order by ID
- [ ] Get order details
- [ ] Get payment by order
- [ ] Update order status

### Checkout Workflow
- [ ] Validate cart before checkout
- [ ] Process complete checkout
- [ ] Move items from cart to orderdetails
- [ ] Create payment record
- [ ] Clear cart after checkout
- [ ] Return proper order reference

### Security
- [ ] CSRF token validation works
- [ ] Guest users can add to cart (via IP)
- [ ] Logged-in users can add to cart (via customer_id)
- [ ] Checkout requires login
- [ ] Invalid inputs are rejected

---

## 🐛 Troubleshooting

### Issue: "Failed to connect to database"
**Solution:**
1. Check your server's MySQL is running
2. Verify credentials in `settings/db_cred.php` match your server settings
3. Ensure database exists on your server
4. Check if your hosting allows remote database connections

### Issue: "Invalid product ID" or "Invalid customer ID"
**Solution:**
1. Run `verify_database.php` to check if tables have data
2. Query your database to get valid IDs:
   ```sql
   SELECT product_id FROM products LIMIT 1;
   SELECT customer_id FROM customer LIMIT 1;
   ```
3. Update test files with these IDs

### Issue: "CSRF token validation failed"
**Solution:**
1. Clear browser cache and cookies
2. Start a new session
3. Refresh the test page

### Issue: "Checkout requires login"
**Solution:**
1. Login to your system first
2. Then access `test_actions.php`
3. Your session should be active

### Issue: Tests show "SQL Error"
**Solution:**
1. Check PHP error logs (location varies by hosting provider)
2. Check MySQL error logs (contact hosting support if needed)
3. Verify table structure matches schema
4. Ensure database user has proper permissions (SELECT, INSERT, UPDATE, DELETE)

---

## 📊 Testing with Postman (Optional)

You can also test action scripts using Postman:

### Example: Add to Cart

**Request:**
```
POST https://your-domain.com/actions/add_to_cart_action.php
Content-Type: application/x-www-form-urlencoded

product_id=1
quantity=2
csrf_token=YOUR_CSRF_TOKEN
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Product added to cart successfully!",
  "cart_count": 1,
  "cart_total": "199.98"
}
```

**Note:** You'll need to get the CSRF token from your session first.

---

## 🎯 Full Workflow Test Scenario

Here's a complete test scenario to validate everything:

1. **Start Fresh**
   - Empty cart: `empty_cart_action.php`
   - Verify cart count is 0

2. **Add Products**
   - Add Product A (ID: 1, Qty: 2)
   - Add Product B (ID: 2, Qty: 1)
   - Verify cart count is 2

3. **Update Quantity**
   - Update Product A to qty 5
   - Verify cart total updated

4. **Remove Item**
   - Remove Product B
   - Verify cart count is 1

5. **Checkout**
   - Process checkout
   - Verify order created
   - Verify cart cleared
   - Verify order appears in orders table

6. **Verify Database**
   - Check `orders` table for new order
   - Check `orderdetails` table for items
   - Check `payment` table for payment record
   - Check `cart` table is empty

---

## ✅ Success Criteria

Your system is ready when:

- ✅ All tests in `test_cart_functions.php` pass (100% success rate)
- ✅ All actions in `test_actions.php` return `"success": true`
- ✅ Database verification shows all tables exist
- ✅ Full workflow test completes without errors
- ✅ Orders appear correctly in database after checkout

---

## 📞 Need Help?

If you encounter persistent issues:

1. Check PHP error logs
2. Check browser console for JavaScript errors
3. Verify database structure matches schema
4. Ensure all files are in correct directories
5. Review file permissions (especially uploads folder)

---

## 🚀 Next Steps

Once all tests pass:

1. ✅ Backend is verified and working
2. ➡️ Proceed to create frontend views (cart.php, checkout.php)
3. ➡️ Create JavaScript files (cart.js, checkout.js)
4. ➡️ Integrate with your existing product pages

---

**Happy Testing! 🎉**
