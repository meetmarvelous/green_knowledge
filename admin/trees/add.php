<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Add New Tree';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../includes/quick_actions.php';

// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log POST data
        error_log("ADD TREE POST DATA: " . print_r($_POST, true));
        error_log("FILES DATA: " . print_r($_FILES, true));

        // Validate required fields
        $required_fields = ['scientific_name', 'family_id', 'tree_code'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Error: $field is required");
            }
        }

        // Process main tree data
        $data = [
            'scientific_name' => escape_string($_POST['scientific_name']),
            'common_names' => escape_string($_POST['common_names'] ?? ''),
            'family_id' => intval($_POST['family_id']),
            'origin_distribution' => escape_string($_POST['origin_distribution'] ?? ''),
            'physical_description' => escape_string($_POST['physical_description'] ?? ''),
            'ecological_info' => escape_string($_POST['ecological_info'] ?? ''),
            'conservation_status' => escape_string($_POST['conservation_status'] ?? 'Least Concern'),
            'uses_economic' => escape_string($_POST['uses_economic'] ?? ''),
            'geotag_lat' => floatval($_POST['geotag_lat'] ?? 0),
            'geotag_lng' => floatval($_POST['geotag_lng'] ?? 0),
            'tree_code' => escape_string($_POST['tree_code']),
            'health_status' => escape_string($_POST['health_status'] ?? ''),
            'remarks' => escape_string($_POST['remarks'] ?? '')
        ];

        // Insert tree
        $columns = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO trees ($columns) VALUES ($values)";

        error_log("SQL QUERY: $sql");
        if (!query($sql)) {
            throw new Exception("Database error: " . error());
        }

        $tree_id = insert_id();
        error_log("NEW TREE ID: $tree_id");
        $uploaded_photos = 0;

        // Handle photo uploads
        if (!empty($_FILES['photos']['name'][0])) {
            error_log("PHOTOS UPLOAD ATTEMPTED");
            
            // Ensure upload directory exists
            if (!file_exists(TREE_PHOTOS_DIR)) {
                error_log("CREATING PHOTO DIRECTORY: " . TREE_PHOTOS_DIR);
                if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
                    throw new Exception("Failed to create photos directory");
                }
            }

            $photo_count = count($_FILES['photos']['name']);
            error_log("PHOTO COUNT: $photo_count");

            for ($i = 0; $i < $photo_count; $i++) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    error_log("PROCESSING PHOTO $i");

                    // Validate image
                    $check = getimagesize($_FILES['photos']['tmp_name'][$i]);
                    if ($check === false) {
                        throw new Exception("File is not an image");
                    }

                    // Check file size (max 5MB)
                    if ($_FILES['photos']['size'][$i] > 5000000) {
                        throw new Exception("File is too large (max 5MB)");
                    }

                    // Generate unique filename
                    $ext = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($ext, $allowed_exts)) {
                        throw new Exception("Only JPG, PNG, and GIF files are allowed");
                    }

                    $filename = "tree_{$tree_id}_" . uniqid() . '.' . $ext;
                    $target_path = TREE_PHOTOS_DIR . $filename;
                    error_log("TARGET PATH: $target_path");

                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
                        throw new Exception("Failed to move uploaded file");
                    }

                    // Insert into database
                    $caption = escape_string($_POST['photo_captions'][$i] ?? '');
                    $is_primary = ($uploaded_photos === 0) ? 1 : 0; // First image is primary
                    $photo_sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
                                 VALUES ($tree_id, '$filename', '$caption', $is_primary)";
                    
                    error_log("PHOTO SQL: $photo_sql");
                    if (!query($photo_sql)) {
                        throw new Exception("Failed to save photo to database: " . error());
                    }

                    $uploaded_photos++;
                    error_log("PHOTO $i UPLOADED SUCCESSFULLY");
                } else {
                    error_log("PHOTO $i UPLOAD ERROR: " . $_FILES['photos']['error'][$i]);
                }
            }
        } else {
            error_log("NO PHOTOS UPLOADED");
        }

        $_SESSION['message'] = "Tree added successfully with $uploaded_photos photos!";
        $_SESSION['message_type'] = 'success';
        error_log("TREE ADDED SUCCESSFULLY");
        
        // Ensure session is saved before redirect
        session_write_close();
        header("Location: " . BASE_URL . "/admin/trees/index.php");
        exit;

    } catch (Exception $e) {
        error_log("ADD TREE ERROR: " . $e->getMessage());
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}
?>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Add New Tree</h2>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="scientific_name" class="form-label">Scientific Name *</label>
                            <input type="text" class="form-control" id="scientific_name" name="scientific_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="common_names" class="form-label">Common Names (comma separated)</label>
                            <input type="text" class="form-control" id="common_names" name="common_names">
                        </div>
                        
                        <div class="mb-3">
                            <label for="family_id" class="form-label">Family *</label>
                            <select class="form-select" id="family_id" name="family_id" required>
                                <option value="">Select Family</option>
                                <?php 
                                $families = query("SELECT * FROM families ORDER BY family_name");
                                while ($family = fetch_assoc($families)): ?>
                                    <option value="<?= $family['family_id'] ?>"><?= $family['family_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tree_code" class="form-label">Tree Code *</label>
                            <input type="text" class="form-control" id="tree_code" name="tree_code" required>
                            <small class="text-muted">Format: UI-BG-TS-XXX</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="conservation_status" class="form-label">Conservation Status</label>
                            <select class="form-select" id="conservation_status" name="conservation_status">
                                <option value="Least Concern">Least Concern</option>
                                <option value="Vulnerable">Vulnerable</option>
                                <option value="Endangered">Endangered</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="health_status" class="form-label">Health Status</label>
                            <input type="text" class="form-control" id="health_status" name="health_status">
                        </div>
                        
                        <div class="mb-3">
                            <label for="geotag_lat" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="geotag_lat" name="geotag_lat">
                        </div>
                        
                        <div class="mb-3">
                            <label for="geotag_lng" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="geotag_lng" name="geotag_lng">
                        </div>
                        
                        <div class="mb-3">
                            <label for="photos" class="form-label">Tree Photos (Max 5MB each)</label>
                            <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                            <small class="text-muted">First image will be set as primary</small>
                            <div id="photo-preview-container" class="row mt-2"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="origin_distribution" class="form-label">Origin & Distribution</label>
                    <textarea class="form-control" id="origin_distribution" name="origin_distribution" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="physical_description" class="form-label">Physical Description</label>
                    <textarea class="form-control" id="physical_description" name="physical_description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="ecological_info" class="form-label">Ecological Information</label>
                    <textarea class="form-control" id="ecological_info" name="ecological_info" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="uses_economic" class="form-label">Uses & Economic Value</label>
                    <textarea class="form-control" id="uses_economic" name="uses_economic" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>/admin/trees/index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Tree</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side photo preview
document.getElementById('photos').addEventListener('change', function(e) {
    const container = document.getElementById('photo-preview-container');
    container.innerHTML = '';
    
    for (let i = 0; i < this.files.length; i++) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3';
            col.innerHTML = `
                <div class="card">
                    <img src="${event.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                    <div class="card-body p-2">
                        <input type="text" class="form-control form-control-sm" 
                               placeholder="Caption" name="photo_captions[]">
                    </div>
                </div>
            `;
            container.appendChild(col);
        };
        reader.readAsDataURL(this.files[i]);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>