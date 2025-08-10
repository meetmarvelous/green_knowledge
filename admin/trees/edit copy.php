<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
  header('Location: ' . BASE_URL . '/login.php');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: ' . BASE_URL . '/admin/trees/index.php');
  exit;
}

$tree_id = intval($_GET['id']);
$tree = get_tree_by_id($tree_id);
$photos = get_tree_photos($tree_id);

if (!$tree) {
  header('Location: ' . BASE_URL . '/admin/trees/index.php');
  exit;
}

$page_title = 'Edit Tree';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../includes/quick_actions.php';

// Debugging
error_log("Accessing edit.php for tree ID: $tree_id");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    error_log("POST Data: " . print_r($_POST, true));
    error_log("FILES Data: " . print_r($_FILES, true));

    // Validate required fields
    $required_fields = ['scientific_name', 'family_id', 'tree_code'];
    foreach ($required_fields as $field) {
      if (empty($_POST[$field])) {
        throw new Exception("$field is required");
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

    // Update tree
    $updates = [];
    foreach ($data as $key => $value) {
      $updates[] = "$key = '$value'";
    }
    $sql = "UPDATE trees SET " . implode(', ', $updates) . " WHERE tree_id = $tree_id";

    error_log("Update SQL: $sql");
    if (!query($sql)) {
      throw new Exception("Database error: " . error());
    }

    $uploaded_files = 0;
    $deleted_files = 0;

    // Handle new photo uploads
    if (!empty($_FILES['photos']['name'][0])) {
      error_log("Processing new photo uploads");

      if (!file_exists(TREE_PHOTOS_DIR)) {
        if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
          throw new Exception("Failed to create photos directory");
        }
      }

      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      $max_size = 10 * 1024 * 1024; // 10MB

      foreach ($_FILES['photos']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) {
          error_log("Upload error for file $index: " . $_FILES['photos']['error'][$index]);
          continue;
        }

        // Validate file
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $tmp_name);
        finfo_close($file_info);

        if (!in_array($mime_type, $allowed_types)) {
          error_log("Invalid file type: $mime_type");
          continue;
        }

        if ($_FILES['photos']['size'][$index] > $max_size) {
          error_log("File too large: " . $_FILES['photos']['size'][$index]);
          continue;
        }

        // Generate unique filename
        $ext = pathinfo($_FILES['photos']['name'][$index], PATHINFO_EXTENSION);
        $filename = "tree_{$tree_id}_" . uniqid() . '.' . strtolower($ext);
        $target_path = TREE_PHOTOS_DIR . $filename;

        // Move uploaded file
        if (move_uploaded_file($tmp_name, $target_path)) {
          // Insert into database
          $caption = escape_string($_POST['photo_captions'][$index] ?? '');
          $is_primary = 0; // New photos are not primary by default
          $photo_sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
                                 VALUES ($tree_id, '$filename', '$caption', $is_primary)";

          if (query($photo_sql)) {
            $uploaded_files++;
            error_log("Successfully uploaded: $filename");
          } else {
            error_log("Failed to save photo to DB: " . error());
            unlink($target_path); // Clean up
          }
        } else {
          error_log("Failed to move uploaded file");
        }
      }
    }

    // Handle primary photo selection
    if (!empty($_POST['primary_photo'])) {
      $primary_id = intval($_POST['primary_photo']);
      error_log("Setting primary photo: $primary_id");

      // Reset all to non-primary first
      query("UPDATE tree_photos SET is_primary = 0 WHERE tree_id = $tree_id");

      // Set selected as primary
      query("UPDATE tree_photos SET is_primary = 1 WHERE photo_id = $primary_id AND tree_id = $tree_id");
    }

    // Handle photo deletions
    if (!empty($_POST['delete_photos'])) {
      foreach ($_POST['delete_photos'] as $photo_id) {
        $photo_id = intval($photo_id);
        $photo = fetch_assoc(query("SELECT photo_path FROM tree_photos WHERE photo_id = $photo_id"));

        if ($photo) {
          $file_path = TREE_PHOTOS_DIR . $photo['photo_path'];
          if (file_exists($file_path)) {
            if (unlink($file_path)) {
              $deleted_files++;
              error_log("Deleted file: $file_path");
            } else {
              error_log("Failed to delete file: $file_path");
            }
          }
          query("DELETE FROM tree_photos WHERE photo_id = $photo_id");
        }
      }
    }

    $_SESSION['message'] = "Tree updated successfully! (Added: $uploaded_files, Deleted: $deleted_files)";
    $_SESSION['message_type'] = 'success';

    // Complete processing before redirect
    session_write_close();
    header("Location: " . BASE_URL . "/admin/trees/index.php");
    exit;
  } catch (Exception $e) {
    error_log("Error in edit.php: " . $e->getMessage());
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
      <h2 class="mb-0">Edit Tree: <?= htmlspecialchars($tree['scientific_name']) ?></h2>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
      <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="scientific_name" class="form-label">Scientific Name *</label>
              <input type="text" class="form-control" id="scientific_name" name="scientific_name"
                value="<?= htmlspecialchars($tree['scientific_name']) ?>" required>
            </div>

            <div class="mb-3">
              <label for="common_names" class="form-label">Common Names (comma separated)</label>
              <input type="text" class="form-control" id="common_names" name="common_names"
                value="<?= htmlspecialchars($tree['common_names']) ?>">
            </div>

            <div class="mb-3">
              <label for="family_id" class="form-label">Family *</label>
              <select class="form-select" id="family_id" name="family_id" required>
                <option value="">Select Family</option>
                <?php
                $families = get_families();
                while ($family = fetch_assoc($families)): ?>
                  <option value="<?= $family['family_id'] ?>"
                    <?= $family['family_id'] == $tree['family_id'] ? 'selected' : '' ?>>
                    <?= $family['family_name'] ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="tree_code" class="form-label">Tree Code *</label>
              <input type="text" class="form-control" id="tree_code" name="tree_code"
                value="<?= htmlspecialchars($tree['tree_code']) ?>" required>
            </div>

            <div class="mb-3">
              <label for="conservation_status" class="form-label">Conservation Status</label>
              <select class="form-select" id="conservation_status" name="conservation_status">
                <option value="Least Concern" <?= $tree['conservation_status'] == 'Least Concern' ? 'selected' : '' ?>>Least Concern</option>
                <option value="Vulnerable" <?= $tree['conservation_status'] == 'Vulnerable' ? 'selected' : '' ?>>Vulnerable</option>
                <option value="Endangered" <?= $tree['conservation_status'] == 'Endangered' ? 'selected' : '' ?>>Endangered</option>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              <label for="health_status" class="form-label">Health Status</label>
              <input type="text" class="form-control" id="health_status" name="health_status"
                value="<?= htmlspecialchars($tree['health_status']) ?>">
            </div>

            <div class="mb-3">
              <label for="geotag_lat" class="form-label">Latitude</label>
              <input type="number" step="0.00000001" class="form-control" id="geotag_lat" name="geotag_lat"
                value="<?= htmlspecialchars($tree['geotag_lat']) ?>">
            </div>

            <div class="mb-3">
              <label for="geotag_lng" class="form-label">Longitude</label>
              <input type="number" step="0.00000001" class="form-control" id="geotag_lng" name="geotag_lng"
                value="<?= htmlspecialchars($tree['geotag_lng']) ?>">
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="origin_distribution" class="form-label">Origin & Distribution</label>
          <textarea class="form-control" id="origin_distribution" name="origin_distribution" rows="3"><?= htmlspecialchars($tree['origin_distribution']) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="physical_description" class="form-label">Physical Description</label>
          <textarea class="form-control" id="physical_description" name="physical_description" rows="5"><?= htmlspecialchars($tree['physical_description']) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="ecological_info" class="form-label">Ecological Information</label>
          <textarea class="form-control" id="ecological_info" name="ecological_info" rows="3"><?= htmlspecialchars($tree['ecological_info']) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="uses_economic" class="form-label">Uses & Economic Value</label>
          <textarea class="form-control" id="uses_economic" name="uses_economic" rows="3"><?= htmlspecialchars($tree['uses_economic']) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="remarks" class="form-label">Remarks</label>
          <textarea class="form-control" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($tree['remarks']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Current Photos</label>
          <div class="row">
            <?php while ($photo = fetch_assoc($photos)): ?>
              <div class="col-md-3 mb-3">
                <div class="card">
                  <img src="<?= BASE_URL ?>/assets/images/tree_photos/<?= htmlspecialchars($photo['photo_path']) ?>"
                    class="card-img-top" style="height: 150px; object-fit: cover;">
                  <div class="card-body p-2">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="primary_photo"
                        value="<?= $photo['photo_id'] ?>" <?= $photo['is_primary'] ? 'checked' : '' ?>>
                      <label class="form-check-label">Primary</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="delete_photos[]"
                        value="<?= $photo['photo_id'] ?>">
                      <label class="form-check-label text-danger">Delete</label>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <div class="mb-3">
          <label for="photos" class="form-label">Add More Photos</label>
          <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
          <div id="photo-preview-container" class="row mt-2"></div>
        </div>

        <button type="submit" class="btn btn-primary">Update Tree</button>
      </form>
    </div>
  </div>
</div>

<script>
  // Photo preview for new uploads
  document.getElementById('photos').addEventListener('change', function(e) {
    const container = document.getElementById('photo-preview-container');
    container.innerHTML = '';

    Array.from(this.files).forEach((file, index) => {
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
      reader.readAsDataURL(file);
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>