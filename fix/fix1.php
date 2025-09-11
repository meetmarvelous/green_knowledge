You're absolutely right. Let me correct the table structure and update all necessary code. Here's the updated SQL and code:

### 1. Corrected Trees Table SQL

```sql
-- Drop existing trees table if it exists
DROP TABLE IF EXISTS trees;

-- Create the trees table with correct structure
CREATE TABLE trees (
    tree_id INT AUTO_INCREMENT PRIMARY KEY,
    scientific_name VARCHAR(255) NOT NULL,
    common_name VARCHAR(255),
    family_id INT NOT NULL,
    description TEXT,
    ecological_info TEXT,
    conservation_status ENUM('Least Concern', 'Vulnerable', 'Endangered', 'Critically Endangered') DEFAULT 'Least Concern',
    uses_importance TEXT,
    origin_distribution TEXT,
    gps_coordinates VARCHAR(255),
    qr_code_path VARCHAR(255), -- Changed from pictures to qr_code_path
    tree_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (family_id) REFERENCES families(family_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert the tree data (without qr_code_path initially)
INSERT INTO trees (scientific_name, common_name, family_id, description, ecological_info, conservation_status, uses_importance, origin_distribution, tree_code) VALUES
(
    'Enterolobium cyclocarpum (Jacq.) Griseb.',
    'Ear Pod Tree',
    (SELECT family_id FROM families WHERE family_name = 'Fabaceae'),
    'A Large deciduous tree that can reach 20–30 meters in height with a thick trunk which is often buttressed at the base; bark grey to light brown, rough with fissures. Leaves are bipinnately compound, with small leaflets that close at night or during drought. Produces distinctive, large, dark brown, ear-shaped pods (up to 12 cm wide) containing several glossy brown seeds. Canopy is broad and spreading, providing extensive shade.',
    'Thrives in well-drained soils, tolerates a variety of soil types including sandy and clay soils. Adapted to both wet and dry tropical climates. Nitrogen-fixing species, improving soil fertility. Flowering occurs between December and March; fruits mature between April and June in Nigeria.',
    'Least Concern',
    'Ornamental and shade tree in parks, gardens, and avenues. Timber is moderately heavy and used for furniture, carpentry, and light construction. Pods are sometimes used as livestock fodder. Seeds have been used in traditional crafts and jewelry. Plays a role in soil enrichment through nitrogen fixation.',
    'Native to tropical regions of Central and South America, particularly Mexico, Costa Rica, and Venezuela. Widely introduced and naturalised in tropical Africa, including Nigeria, for ornamental and shade purposes.',
    'UI-BG-TS-001'
),
-- ... (other tree inserts remain the same)
```

### 2. Updated Tree Photos Table SQL

```sql
-- Create tree_photos table (separate from QR codes)
CREATE TABLE IF NOT EXISTS tree_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    tree_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Updated `pages/tree.php`

```php
<!-- Update the tree details section -->
<div class="card-body">
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Common Name</h5>
            <p><?= $tree['common_name'] ?></p>
            
            <h5>Family</h5>
            <p><?= $tree['family_name'] ?></p>
            
            <h5>Origin & Distribution</h5>
            <p><?= $tree['origin_distribution'] ?></p>
            
            <h5>Conservation Status</h5>
            <p class="badge bg-<?= 
                $tree['conservation_status'] == 'Endangered' ? 'danger' : 
                ($tree['conservation_status'] == 'Vulnerable' ? 'warning' : 'success') 
            ?>">
                <?= $tree['conservation_status'] ?>
            </p>
        </div>
        <div class="col-md-6">
            <h5>Tree Code</h5>
            <p><?= $tree['tree_code'] ?></p>
            
            <?php if (!empty($tree['gps_coordinates'])): ?>
            <h5>GPS Coordinates</h5>
            <p><?= $tree['gps_coordinates'] ?></p>
            <?php endif; ?>
            
            <?php if (!empty($tree['qr_code_path'])): ?>
            <h5>QR Code</h5>
            <img src="<?= BASE_URL . '/' . $tree['qr_code_path'] ?>" 
                 alt="QR Code" class="img-thumbnail" style="width: 100px;">
            <a href="<?= BASE_URL . '/' . $tree['qr_code_path'] ?>" 
               download class="btn btn-sm btn-outline-success ms-2">
                <i class="fas fa-download"></i> Download
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Rest of the content remains the same -->
</div>
```

### 4. Updated `admin/trees/add.php` and `edit.php`

Remove the pictures field and add QR code field:

```php
<!-- Remove this field -->
<!-- <div class="mb-3">
    <label for="pictures" class="form-label">Pictures</label>
    <input type="text" class="form-control" id="pictures" name="pictures">
</div> -->

<!-- Keep the photo upload section for tree_photos -->
<div class="mb-3">
    <label for="photos" class="form-label">Tree Photos</label>
    <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
    <small class="text-muted">Upload multiple photos (first will be primary)</small>
    <div id="photo-preview-container" class="row mt-2"></div>
</div>

<!-- Add QR code path field (optional) -->
<div class="mb-3">
    <label for="qr_code_path" class="form-label">QR Code Path</label>
    <input type="text" class="form-control" id="qr_code_path" name="qr_code_path" 
           placeholder="e.g., assets/images/qr_codes/tree_1.png">
    <small class="text-muted">Auto-generated when QR codes are created</small>
</div>
```

### 5. Updated `includes/functions.php`

```php
// Update generate_qr_with_record function
function generate_qr_with_record($tree_id) {
    try {
        $tree = get_tree_by_id($tree_id);
        if (!$tree) {
            throw new Exception("Tree not found");
        }

        // Generate unique filename
        $qr_filename = "tree_{$tree_id}_" . time() . ".png";
        $qr_path = QR_CODES_DIR . $qr_filename;
        
        // Ensure directory exists
        if (!file_exists(QR_CODES_DIR)) {
            mkdir(QR_CODES_DIR, 0755, true);
        }
        
        // Generate QR code
        require_once 'phpqrcode/qrlib.php';
        $url = BASE_URL . "/pages/tree.php?id=$tree_id";
        QRcode::png($url, $qr_path, QR_ECLEVEL_L, 10);
        
        if (!file_exists($qr_path)) {
            throw new Exception("Failed to generate QR code image");
        }
        
        // Update tree record with QR code path
        $qr_path_relative = str_replace(ROOT_PATH, '', $qr_path);
        $sql = "UPDATE trees SET qr_code_path = '" . escape_string($qr_path_relative) . "' WHERE tree_id = $tree_id";
        
        if (!query($sql)) {
            throw new Exception("Failed to update tree with QR code path: " . error());
        }
        
        return [
            'success' => true,
            'path' => $qr_path,
            'url' => BASE_URL . '/' . $qr_path_relative,
            'tree_code' => $tree['tree_code'],
            'scientific_name' => $tree['scientific_name']
        ];
        
    } catch (Exception $e) {
        error_log("QR Generation Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
```

### 6. Updated `admin/qr_management.php`

```php
// Update the display to use qr_code_path from trees table
$trees = query("
    SELECT t.tree_id, t.scientific_name, t.common_name, t.tree_code, t.qr_code_path, 
           f.family_name, q.created_at
    FROM trees t
    LEFT JOIN families f ON t.family_id = f.family_id
    LEFT JOIN qr_codes q ON t.tree_id = q.tree_id AND q.is_active = TRUE
    ORDER BY t.scientific_name
");

// In the table display
<td>
    <?php if (!empty($tree['qr_code_path'])): ?>
        <img src="<?= BASE_URL . '/' . $tree['qr_code_path'] ?>" 
             class="img-thumbnail" style="width: 80px; height: 80px;" 
             alt="QR Code">
    <?php else: ?>
        <span class="badge bg-warning">No QR Code</span>
    <?php endif; ?>
</td>
```

### 7. Updated `admin/trees/index.php` (Table View)

```php
<!-- Update the table to show QR code status -->
<thead>
    <tr>
        <th>Code</th>
        <th>Scientific Name</th>
        <th>Common Name</th>
        <th>Family</th>
        <th>Conservation</th>
        <th>QR Code</th>
        <th>Actions</th>
    </tr>
</thead>

<tbody>
    <?php while ($tree = fetch_assoc($trees)): ?>
        <tr>
            <td><?= $tree['tree_code'] ?></td>
            <td><?= $tree['scientific_name'] ?></td>
            <td><?= $tree['common_name'] ?></td>
            <td><?= $tree['family_name'] ?></td>
            <td>
                <span class="badge bg-<?= 
                    $tree['conservation_status'] == 'Endangered' ? 'danger' : 
                    ($tree['conservation_status'] == 'Vulnerable' ? 'warning' : 'success') 
                ?>">
                    <?= $tree['conservation_status'] ?>
                </span>
            </td>
            <td>
                <?php if (!empty($tree['qr_code_path'])): ?>
                    <span class="badge bg-success">Yes</span>
                <?php else: ?>
                    <span class="badge bg-secondary">No</span>
                <?php endif; ?>
            </td>
            <td>
                <!-- Actions remain the same -->
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
```

### Key Changes:

1. **Table Structure**: Replaced `pictures` with `qr_code_path` in trees table
2. **Separation of Concerns**: 
   - `tree_photos` table handles actual tree photos
   - `qr_code_path` stores the QR code image path
3. **Function Updates**: All functions now use the correct field names
4. **Display Logic**: Properly shows QR codes and photos in their respective sections

This structure maintains the proper separation between tree photos (multiple images) and QR codes (single image per tree), which is the correct approach for your application.