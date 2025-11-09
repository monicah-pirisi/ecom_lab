# Product Management Setup Guide

## ⚠️ IMPORTANT: Database Setup Required

Before using the product management system, you MUST create the products table in your database.

### Step 1: Create Products Table

Open **phpMyAdmin** (http://localhost/phpmyadmin) and run this SQL:

```sql
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_cat` int(11) NOT NULL,
  `product_brand` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_desc` text,
  `product_image` varchar(255) DEFAULT NULL,
  `product_keywords` varchar(500) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `product_cat` (`product_cat`),
  KEY `product_brand` (`product_brand`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for faster searches
CREATE INDEX idx_product_title ON products(product_title);
CREATE INDEX idx_product_keywords ON products(product_keywords);
```

**Note**: If you get foreign key errors, run this version WITHOUT foreign keys first:

```sql
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_cat` int(11) NOT NULL,
  `product_brand` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_desc` text,
  `product_image` varchar(255) DEFAULT NULL,
  `product_keywords` varchar(500) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 2: Verify uploads Directory

Make sure the `uploads/` directory exists and has proper permissions:

```
ecom_lab/
└── uploads/          <- This folder MUST exist
    └── .htaccess     <- Security file (already created)
```

If the folder doesn't exist, it will be created automatically when you upload your first image.

### Step 3: Test the System

1. **Login as Admin**
2. Navigate to **Products** from the menu
3. You should see "No products found" (not an error)
4. Try adding a product

---

## 🐛 Troubleshooting

### Issue: "Failed to fetch products" Error

**Solution**:
1. Make sure the `products` table exists in your database
2. Clear your browser cache and refresh
3. Check browser console (F12) for detailed error messages
4. The error message will now show the actual PHP error

### Issue: Red X When Uploading Images

**Possible Causes**:

1. **File too large** (Max 5MB per image)
   - Solution: Resize images before uploading

2. **Invalid file type**
   - Solution: Only use JPG, PNG, GIF, or WEBP images

3. **uploads/ directory doesn't exist**
   - Solution: Create the folder manually or it will be created automatically

4. **Permission issues**
   - Solution: Make sure uploads/ folder has write permissions (755)

### Issue: Images Upload But Don't Display

**Check**:
1. Image path should be: `uploads/u{user_id}/p{product_id}/image_1_xxxxx.png`
2. Open browser developer tools (F12) → Network tab → Check if image URLs are correct
3. Make sure you're using relative paths correctly

---

## 📂 Image Upload Structure

When user ID 40 uploads 3 images for product ID 6:

```
uploads/
└── u40/
    └── p6/
        ├── image_1_abc123.png
        ├── image_2_def456.png
        └── image_3_ghi789.png
```

**Security Notes**:
- All uploads MUST go inside the `uploads/` directory
- `.htaccess` prevents PHP file execution in uploads/
- File types are validated (images only)
- File sizes are limited to 5MB each

---

## ✅ Testing Checklist

- [ ] Products table created in database
- [ ] Can access product.php page
- [ ] "No products found" message appears (not error)
- [ ] Can see category and brand dropdowns
- [ ] Can select multiple images
- [ ] Image previews appear when selected
- [ ] Can remove images from preview
- [ ] Can submit form successfully
- [ ] Products appear after creation
- [ ] Images display correctly
- [ ] Can edit products
- [ ] Can delete products

---

## 🔧 Quick Fixes

### Enable Error Display (for debugging)

The fetch action now shows detailed errors. Refresh the product page and check the browser console (F12) to see the exact error message.

### Reset Everything

If things are completely broken:

1. Drop the products table: `DROP TABLE IF EXISTS products;`
2. Re-run the CREATE TABLE SQL above
3. Clear browser cache
4. Refresh the page

---

## 📞 Need Help?

1. Check browser console (F12) for JavaScript errors
2. Check Network tab for failed requests
3. Look at the response from fetch_product_action.php for detailed errors
4. Make sure you're logged in as admin
5. Verify categories and brands exist before adding products
