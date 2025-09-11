The issue is that `TREE_PHOTOS_DIR` is an absolute server path, but you're using it in a URL context. You need to use the relative path for image URLs. Here's the fix:

### 1. Update `config.php` to add a new constant:

```php
// File paths - Now using absolute server paths for file operations
define('ROOT_PATH', realpath(__DIR__ . '/../') . '/');
define('TREE_PHOTOS_DIR', ROOT_PATH . 'assets/images/tree_photos/');
define('QR_CODES_DIR', ROOT_PATH . 'assets/images/qr_codes/');

// Relative paths for URLs
define('TREE_PHOTOS_URL', 'assets/images/tree_photos/');
define('QR_CODES_URL', 'assets/images/qr_codes/');
```

### 2. Fix all image display URLs:

**In `pages/tree.php`:**
```php
<img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . $photo['photo_path'] ?>" 
     class="d-block w-100" 
     alt="<?= $tree['scientific_name'] ?>">
```

**In `admin/trees/edit.php`:**
```php
<img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . $photo['photo_path'] ?>" 
     class="card-img-top" style="height: 150px; object-fit: cover;">
```

**In `admin/trees/add.php` (preview script):**
```javascript
// This should work as-is since it's client-side data URLs
```

**In `pages/home.php`:**
```php
<img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . $first_photo['photo_path'] ?>" 
     class="card-img-top" 
     alt="<?= $tree['scientific_name'] ?>" 
     style="height: 200px; object-fit: cover;">
```

**In `pages/list.php`:**
```php
<img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . ($first_photo['photo_path'] ?? 'default.jpg') ?>" 
     class="card-img-top" 
     alt="<?= $tree['scientific_name'] ?>" 
     style="height: 200px; object-fit: cover;">
```

### 3. Also fix QR code URLs:

**In `pages/tree.php`:**
```php
<img src="<?= BASE_URL . '/' . $tree['qr_code_path'] ?>" 
     alt="QR Code" class="img-thumbnail" style="width: 100px;">
```

**In `admin/qr_management.php`:**
```php
<img src="<?= BASE_URL . '/' . $tree['qr_code_path'] ?>" 
     class="img-thumbnail" style="width: 80px; height: 80px;" 
     alt="QR Code">
```

### 4. Update the file upload logic to store relative paths:

**In both `add.php` and `edit.php`, update the photo insertion:**
```php
// After moving the uploaded file, use relative path for database
$filename = "tree_{$tree_id}_" . uniqid() . '.' . $ext;
$target_path = TREE_PHOTOS_DIR . $filename; // Absolute path for file operations
$relative_path = $filename; // Relative path for database

// Move uploaded file (using absolute path)
if (!move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
    throw new Exception("Failed to move uploaded file");
}

// Insert into database (using relative path)
$photo_sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
             VALUES ($tree_id, '$relative_path', '$caption', $is_primary)";
```

### Key Points:

1. **`TREE_PHOTOS_DIR`** - Absolute server path (for file operations: `mkdir`, `move_uploaded_file`)
2. **`TREE_PHOTOS_URL`** - Relative URL path (for HTML images: `<img src>`)
3. **Database should store** only the filename or relative path, not absolute paths

This fix ensures that file operations use the correct absolute paths while image URLs use the correct relative paths that work in web browsers.