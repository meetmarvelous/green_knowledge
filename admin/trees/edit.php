<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
  header('Location: ' . BASE_URL . '/admin/login.php');
  exit;
}

// Enable debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. HANDLE FORM SUBMISSION (BEFORE ANY OUTPUT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tree_id = intval($_POST['tree_id'] ?? 0);
  $redirect_url = BASE_URL . '/admin/trees/index.php';

  try {
    // Validate tree exists
    if (!tree_exists($tree_id)) {
      throw new Exception("Tree not found");
    }

    // A. Handle File Uploads First
    $uploaded_files = [];
    if (!empty($_FILES['photos']['name'][0])) {
      error_log("[TREE EDIT] Processing file uploads for tree $tree_id");

      // Ensure directory exists
      if (!file_exists(TREE_PHOTOS_DIR)) {
        if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
          throw new Exception("Could not create photos directory");
        }
      }

      // Process each file
      foreach ($_FILES['photos']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
          // Validate file
          $finfo = new finfo(FILEINFO_MIME_TYPE);
          $mime = $finfo->file($tmp_name);
          $allowed = ['image/jpeg', 'image/png', 'image/gif'];

          if (!in_array($mime, $allowed)) {
            throw new Exception("Invalid file type: " . $_FILES['photos']['name'][$index]);
          }

          // Generate unique filename
          $ext = pathinfo($_FILES['photos']['name'][$index], PATHINFO_EXTENSION);
          $filename = "tree_{$tree_id}_" . uniqid() . '.' . strtolower($ext);
          $target_path = TREE_PHOTOS_DIR . $filename;

          // Move file
          if (!move_uploaded_file($tmp_name, $target_path)) {
            throw new Exception("Failed to move uploaded file: " . $_FILES['photos']['name'][$index]);
          }

          $uploaded_files[] = [
            'path' => $filename,
            'caption' => $_POST['photo_captions'][$index] ?? ''
          ];
          error_log("[TREE EDIT] Uploaded file: $filename");
        }
      }
    }

    // B. Update Tree Data
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

    $updates = [];
    foreach ($data as $key => $value) {
      $updates[] = "$key = '$value'";
    }

    $sql = "UPDATE trees SET " . implode(', ', $updates) . " WHERE tree_id = $tree_id";
    if (!query($sql)) {
      throw new Exception("Database update failed: " . error());
    }

    // C. Save Uploaded Files to Database
    foreach ($uploaded_files as $file) {
      $sql = "INSERT INTO tree_photos (tree_id, photo_path, caption) 
                    VALUES ($tree_id, '" . escape_string($file['path']) . "', 
                    '" . escape_string($file['caption']) . "')";
      if (!query($sql)) {
        throw new Exception("Failed to save photo record: " . error());
      }
    }

    // D. Handle Photo Deletions
    if (!empty($_POST['delete_photos'])) {
      foreach ($_POST['delete_photos'] as $photo_id) {
        $photo_id = intval($photo_id);
        $photo = fetch_assoc(query("SELECT photo_path FROM tree_photos WHERE photo_id = $photo_id"));

        if ($photo) {
          $file_path = TREE_PHOTOS_DIR . $photo['photo_path'];
          if (file_exists($file_path)) {
            if (!unlink($file_path)) {
              error_log("[TREE EDIT] Failed to delete file: $file_path");
            }
          }
          query("DELETE FROM tree_photos WHERE photo_id = $photo_id");
        }
      }
    }

    // E. Set Primary Photo
    if (!empty($_POST['primary_photo'])) {
      $primary_id = intval($_POST['primary_photo']);
      query("UPDATE tree_photos SET is_primary = 0 WHERE tree_id = $tree_id");
      query("UPDATE tree_photos SET is_primary = 1 WHERE photo_id = $primary_id");
    }

    // SUCCESS - All operations completed
    $_SESSION['message'] = "Tree updated successfully!";
    $_SESSION['message_type'] = 'success';

    // Ensure session is saved before redirect
    session_write_close();

    // Redirect using absolute URL
    header("Location: $redirect_url");
    exit;
  } catch (Exception $e) {
    // Error handling
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    error_log("[TREE EDIT ERROR] " . $e->getMessage());

    // Stay on edit page to show errors
    $tree_id = intval($_POST['tree_id']);
  }
}

// 3. LOAD TREE DATA FOR DISPLAY
if (!isset($tree_id) && isset($_GET['id'])) {
  $tree_id = intval($_GET['id']);
}

if (!isset($tree_id) || !tree_exists($tree_id)) {
  $_SESSION['message'] = "Tree not found";
  $_SESSION['message_type'] = 'danger';
  header('Location: ' . BASE_URL . '/admin/trees/index.php');
  exit;
}

$tree = get_tree_by_id($tree_id);
$photos = get_tree_photos($tree_id);
$families = get_families();

// 4. HTML OUTPUT
$page_title = 'Edit Tree: ' . $tree['scientific_name'];
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
<?php 
require_once __DIR__ . '/../includes/quick_actions.php';
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
        <input type="hidden" name="tree_id" value="<?= $tree_id ?>">

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
                  <img src="<?= BASE_URL ?>/assets/images/tree_photos/<?= $photo['photo_path'] ?>"
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
          <small class="text-muted">Max 10MB per file (JPEG, PNG, GIF)</small>
          <div id="photo-preview-container" class="row mt-2"></div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
          <a href="<?= BASE_URL ?>/admin/trees/index.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Update Tree</button>
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