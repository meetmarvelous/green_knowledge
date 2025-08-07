<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$families = get_families();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process form data
    $required_fields = ['scientific_name', 'family_id', 'tree_code'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }
    
    if (empty($errors)) {
        // Prepare data
        $data = [
            'scientific_name' => escape_string($_POST['scientific_name']),
            'common_names' => escape_string($_POST['common_names']),
            'family_id' => intval($_POST['family_id']),
            'origin_distribution' => escape_string($_POST['origin_distribution']),
            'physical_description' => escape_string($_POST['physical_description']),
            'ecological_info' => escape_string($_POST['ecological_info']),
            'conservation_status' => escape_string($_POST['conservation_status']),
            'uses_economic' => escape_string($_POST['uses_economic']),
            'geotag_lat' => floatval($_POST['geotag_lat']),
            'geotag_lng' => floatval($_POST['geotag_lng']),
            'tree_code' => escape_string($_POST['tree_code']),
            'health_status' => escape_string($_POST['health_status']),
            'remarks' => escape_string($_POST['remarks'])
        ];
        
        // Insert tree
        $columns = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        
        $sql = "INSERT INTO trees ($columns) VALUES ($values)";
        if (query($sql)) {
            $tree_id = mysqli_insert_id($conn);
            
            // Handle photo uploads
            if (!empty($_FILES['photos']['name'][0])) {
                $photo_count = count($_FILES['photos']['name']);
                
                for ($i = 0; $i < $photo_count; $i++) {
                    if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = uniqid() . '_' . basename($_FILES['photos']['name'][$i]);
                        $target_path = TREE_PHOTOS_DIR . $file_name;
                        
                        if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
                            $caption = escape_string($_POST['photo_captions'][$i] ?? '');
                            $is_primary = ($i === 0) ? 1 : 0;
                            
                            $sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
                                    VALUES ($tree_id, '$file_name', '$caption', $is_primary)";
                            query($sql);
                        }
                    }
                }
            }
            
            $_SESSION['message'] = "Tree added successfully!";
            header("Location: index.php");
            exit;
        } else {
            $errors['database'] = "Error adding tree: " . mysqli_error($conn);
        }
    }
}

$page_title = 'Add New Tree';
require_once '../../includes/header.php';
?>

<div class="card">
    <div class="card-header bg-success text-white">
        <h2 class="mb-0">Add New Tree</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                    </ul>
            </div>
        <?php endif; ?>
        
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
                            <?php while ($family = fetch_assoc($families)): ?>
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
                        <label for="photos" class="form-label">Tree Photos</label>
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
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-success">Save Tree</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>