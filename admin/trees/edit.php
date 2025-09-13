<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
  header('Location: ../../login.php');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$tree_id = intval($_GET['id']);
$tree = get_tree_by_id($tree_id);
$photos = get_tree_photos($tree_id);

if (!$tree) {
  header('Location: index.php');
  exit;
}

$page_title = 'Edit Tree';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
  <?php
  // require_once __DIR__ . '/../includes/quick_actions.php';

  // Enable debugging
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
      error_log("EDIT TREE POST DATA: " . print_r($_POST, true));
      error_log("FILES DATA: " . print_r($_FILES, true));

      // A. Validate required fields
      $required_fields = ['scientific_name', 'family_id', 'tree_code'];
      foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
          throw new Exception("Error: $field is required");
        }
      }

      // B. Update Tree Data Structure
      $data = [
        'scientific_name' => escape_string($_POST['scientific_name']),
        'common_name' => escape_string($_POST['common_name'] ?? ''),
        'family_id' => intval($_POST['family_id']),
        'description' => escape_string($_POST['description'] ?? ''),
        'ecological_info' => escape_string($_POST['ecological_info'] ?? ''),
        'conservation_status' => escape_string($_POST['conservation_status'] ?? 'Least Concern'),
        'uses_importance' => escape_string($_POST['uses_importance'] ?? ''),
        'origin_distribution' => escape_string($_POST['origin_distribution'] ?? ''),
        'gps_coordinates' => escape_string($_POST['gps_coordinates'] ?? ''),
        'qr_code_path' => escape_string($_POST['qr_code_path'] ?? ''),
        'tree_code' => escape_string($_POST['tree_code'])
      ];

      // Update tree
      $updates = [];
      foreach ($data as $key => $value) {
        $updates[] = "$key = '$value'";
      }
      $sql = "UPDATE trees SET " . implode(', ', $updates) . " WHERE tree_id = $tree_id";

      error_log("UPDATE SQL: $sql");
      if (!query($sql)) {
        throw new Exception("Database error: " . error());
      }

      // C. Handle new photo uploads
      if (!empty($_FILES['photos']['name'][0])) {
        error_log("NEW PHOTOS UPLOAD ATTEMPTED");

        if (!file_exists(TREE_PHOTOS_DIR)) {
          error_log("CREATING PHOTO DIRECTORY: " . TREE_PHOTOS_DIR);
          if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
            throw new Exception("Failed to create photos directory");
          }
        }

        $photo_count = count($_FILES['photos']['name']);
        error_log("NEW PHOTO COUNT: $photo_count");

        for ($i = 0; $i < $photo_count; $i++) {
          if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
            error_log("PROCESSING NEW PHOTO $i");

            // Validate image
            $check = getimagesize($_FILES['photos']['tmp_name'][$i]);
            if ($check === false) {
              throw new Exception("File is not an image");
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
            $is_primary = 0; // New photos are not primary by default
            $photo_sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
                                 VALUES ($tree_id, '$filename', '$caption', $is_primary)";

            error_log("PHOTO SQL: $photo_sql");
            if (!query($photo_sql)) {
              throw new Exception("Failed to save photo to database: " . error());
            }

            error_log("NEW PHOTO $i UPLOADED SUCCESSFULLY");
          } else {
            error_log("NEW PHOTO $i UPLOAD ERROR: " . $_FILES['photos']['error'][$i]);
          }
        }
      }

      // Handle primary photo selection
      if (!empty($_POST['primary_photo'])) {
        $primary_id = intval($_POST['primary_photo']);
        error_log("SETTING PRIMARY PHOTO: $primary_id");

        // Reset all to non-primary first
        query("UPDATE tree_photos SET is_primary = 0 WHERE tree_id = $tree_id");

        // Set selected as primary
        query("UPDATE tree_photos SET is_primary = 1 WHERE photo_id = $primary_id AND tree_id = $tree_id");
      }

      // Handle photo deletions
      if (!empty($_POST['delete_photos'])) {
        foreach ($_POST['delete_photos'] as $photo_id) {
          $photo_id = intval($photo_id);
          error_log("DELETING PHOTO ID: $photo_id");

          $photo = fetch_assoc(query("SELECT photo_path FROM tree_photos WHERE photo_id = $photo_id"));
          if ($photo) {
            // Delete file
            $file_path = TREE_PHOTOS_DIR . $photo['photo_path'];
            if (file_exists($file_path)) {
              unlink($file_path);
            }
            // Delete record
            query("DELETE FROM tree_photos WHERE photo_id = $photo_id");
          }
        }
      }

      $_SESSION['message'] = "Tree updated successfully!";
      $_SESSION['message_type'] = 'success';
      error_log("TREE UPDATED SUCCESSFULLY");

      session_write_close();
      header("Location: index.php");
      exit;
    } catch (Exception $e) {
      error_log("EDIT TREE ERROR: " . $e->getMessage());
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
        <h2 class="mb-0">Edit Tree: <?= $tree['scientific_name'] ?></h2>
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
                <label for="common_name" class="form-label">Common Name</label>
                <input type="text" class="form-control" id="common_name" name="common_name"
                  value="<?= htmlspecialchars($tree['common_name']) ?>">
              </div>

              <div class="mb-3">
                <label for="family_id" class="form-label">Family *</label>
                <select class="form-select" id="family_id" name="family_id" required>
                  <option value="">Select Family</option>
                  <?php
                  $families = get_families();
                  while ($family = fetch_assoc($families)):
                  ?>
                    <option value="<?= $family['family_id'] ?>" <?= $tree['family_id'] == $family['family_id'] ? 'selected' : '' ?>>
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
                  <option value="Critically Endangered" <?= $tree['conservation_status'] == 'Critically Endangered' ? 'selected' : '' ?>>Critically Endangered</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="gps_coordinates" class="form-label">GPS Coordinates</label>
                <input type="text" class="form-control" id="gps_coordinates" name="gps_coordinates"
                  value="<?= htmlspecialchars($tree['gps_coordinates']) ?>"
                  placeholder="Format: 7.4456, 3.8945">
              </div>

              <div class="mb-3">
                <label for="qr_code_path" class="form-label">QR Code Path</label>
                <input type="text" class="form-control" id="qr_code_path" name="qr_code_path"
                  value="<?= htmlspecialchars($tree['qr_code_path']) ?>"
                  placeholder="e.g., assets/images/qr_codes/tree_1.png">
                <small class="text-muted">Auto-generated when QR codes are created</small>
              </div>

              <div class="mb-3">
                <label for="photos" class="form-label">Additional Photos</label>
                <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                <small class="text-muted">Upload additional photos</small>
                <div id="photo-preview-container" class="row mt-2"></div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($tree['description']) ?></textarea>
          </div>

          <div class="mb-3">
            <label for="ecological_info" class="form-label">Ecological Information</label>
            <textarea class="form-control" id="ecological_info" name="ecological_info" rows="3"><?= htmlspecialchars($tree['ecological_info']) ?></textarea>
          </div>

          <div class="mb-3">
            <label for="uses_importance" class="form-label">Uses & Importance</label>
            <textarea class="form-control" id="uses_importance" name="uses_importance" rows="3"><?= htmlspecialchars($tree['uses_importance']) ?></textarea>
          </div>

          <div class="mb-3">
            <label for="origin_distribution" class="form-label">Origin & Distribution</label>
            <textarea class="form-control" id="origin_distribution" name="origin_distribution" rows="2"><?= htmlspecialchars($tree['origin_distribution']) ?></textarea>
          </div>

          <!-- Current Photos Section -->
          <div class="mb-3">
            <label class="form-label">Current Photos</label>
            <div class="row">
              <?php while ($photo = fetch_assoc($photos)): ?>
                <div class="col-md-3 mb-3">
                  <div class="card">
                    <img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . $photo['photo_path'] ?>"
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

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Tree</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
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