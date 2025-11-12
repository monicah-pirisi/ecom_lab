# 🚀 Server Testing - Quick Start Guide

Since you're deploying on a server (not localhost), follow these steps to test your cart and checkout system.

---

## ⚡ Quick Testing Steps

### Step 1: Upload Test Files
Make sure these test files are uploaded to your server root:
- ✅ `verify_database.php`
- ✅ `test_cart_functions.php`
- ✅ `test_actions.php`

### Step 2: Update Database Configuration
Edit `settings/db_cred.php` with your server database credentials:
```php
<?php
// Server Configuration
SERVER: "your-mysql-host"      // Often "localhost" on shared hosting
USERNAME: "your-db-username"
PASSWORD: "your-db-password"
DATABASE: "your-db-name"
?>
```

### Step 3: Test Database Connection
**Visit:** `https://your-domain.com/verify_database.php`

✅ **Expected:** Green summary showing all tables exist
❌ **If failed:** Check database credentials and ensure tables are imported

---

## 🧪 Testing URLs (Replace with Your Domain)

| Test Type | URL | Purpose |
|-----------|-----|---------|
| **Database Check** | `https://your-domain.com/verify_database.php` | Verify all tables exist |
| **Backend Functions** | `https://your-domain.com/test_cart_functions.php` | Test PHP classes directly |
| **Action Scripts** | `https://your-domain.com/test_actions.php` | Test AJAX endpoints |

---

## 📝 Before Testing, Edit These Values

### In `test_cart_functions.php` (Line 26-28):
```php
$test_customer_id = 1;  // ← Change to a valid customer ID from YOUR database
$test_product_id = 1;   // ← Change to a valid product ID from YOUR database
```

**How to find valid IDs:**
1. Login to phpMyAdmin on your server
2. Run these queries:
   ```sql
   SELECT customer_id FROM customer LIMIT 1;
   SELECT product_id FROM products LIMIT 1;
   ```
3. Use those IDs in the test file

---

## ✅ Testing Checklist

Follow this order:

1. **Database Verification**
   - [ ] Visit `verify_database.php`
   - [ ] All tables show "✓ EXISTS"
   - [ ] Products table has at least 1 row
   - [ ] Customer table has at least 1 row

2. **Backend Functions Test**
   - [ ] Update customer_id and product_id in `test_cart_functions.php`
   - [ ] Visit `test_cart_functions.php`
   - [ ] All tests show "✅ PASS"
   - [ ] Success rate shows 100%

3. **Action Scripts Test**
   - [ ] Login to your system first
   - [ ] Visit `test_actions.php`
   - [ ] Update Product ID in the form
   - [ ] Click "➕ Test Add to Cart"
   - [ ] Check console shows success
   - [ ] Click "🚀 Test Process Checkout"
   - [ ] Verify order created

---

## 🔧 Common Server Issues & Fixes

### Issue: 500 Internal Server Error
**Causes:**
- File permissions incorrect
- PHP version incompatible
- Syntax errors

**Fix:**
1. Set file permissions: `644` for PHP files, `755` for directories
2. Check PHP version is 7.4+ (run `phpinfo()`)
3. Check server error logs

### Issue: Database Connection Failed
**Fix:**
1. Verify database host (might not be "localhost" on some hosts)
2. Check if database user has correct permissions
3. Ensure database exists on the server
4. Try different host: `localhost` vs `127.0.0.1` vs `mysql.yourdomain.com`

### Issue: CSRF Token Validation Failed
**Fix:**
1. Clear browser cache and cookies
2. Ensure sessions are enabled on server
3. Check `session.save_path` is writable
4. Try different browser

### Issue: "Headers already sent" error
**Fix:**
1. Remove any whitespace before `<?php` in files
2. Save files with UTF-8 without BOM encoding
3. Check for `echo` statements in included files

---

## 🎯 Quick Test (5 Minutes)

**Fastest way to verify everything works:**

1. **Login to your site**
2. **Visit:** `https://your-domain.com/test_actions.php`
3. **Enter a valid Product ID** (e.g., 1)
4. **Click:** "🎯 Run Full Workflow Test"
5. **Check console output** - should show all steps passing

**Expected Console Output:**
```
======================================
🎯 STARTING FULL WORKFLOW TEST
======================================
Step 1: Adding product to cart...
[timestamp] Response: {"success":true, "message":"Product added..."}
✅ Cart Count: 1, Total: $99.99

Step 2: Updating quantity...
[timestamp] Response: {"success":true, ...}

Step 3: Processing checkout...
[timestamp] Response: {"success":true, "order_id":123, ...}
✅ Order Created! ID: 123, Ref: ORD-20251112-00042

======================================
✅ WORKFLOW TEST COMPLETED
======================================
```

---

## 🔐 Security Note

**IMPORTANT:** After testing is complete:

1. **Delete or rename test files** for security:
   - `verify_database.php` → rename to something obscure
   - `test_cart_functions.php` → DELETE
   - `test_actions.php` → DELETE

2. **Or restrict access via .htaccess:**
   ```apache
   <Files "test_*.php">
       Order Allow,Deny
       Deny from all
   </Files>
   ```

3. **Or password-protect test directory**

---

## 📞 Need Help?

If tests are failing:

1. **Check server error logs** (cPanel → Error Log or contact hosting support)
2. **Enable error display temporarily:**
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. **Check database connection:**
   - Login to phpMyAdmin
   - Verify tables exist
   - Check user permissions

4. **Verify PHP version:**
   - Create file: `info.php`
   - Content: `<?php phpinfo(); ?>`
   - Visit: `https://your-domain.com/info.php`
   - Check PHP version is 7.4+
   - Delete file after checking

---

## ✅ Success = Ready to Build Frontend

Once all tests pass:
- ✅ Backend is working perfectly
- ✅ Database structure is correct
- ✅ All cart functions operational
- ✅ Checkout workflow complete
- ➡️ **Ready to build the frontend views!**

---

**Testing time:** ~10 minutes for complete verification
**Deployment:** Production-ready backend ✨
