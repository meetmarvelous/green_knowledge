<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
  header('Location: ../login.php');
  exit;
}

$page_title = 'QR Code Management';
require_once __DIR__ . '/../includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tree_id = intval($_POST['tree_id']);
  $action = $_POST['action'];

  try {
    if ($action === 'generate') {
      $result = generate_qr_with_record($tree_id);
      if ($result['success']) {
        $_SESSION['success'] = "QR code generated successfully!";
      } else {
        $_SESSION['error'] = $result['message'];
      }
    }
  } catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
  }
  header("Location: qr_management.php");
  exit;
}

// Get all trees with their active QR codes
$trees = query("
    SELECT t.tree_id, t.scientific_name, t.tree_code, q.qr_path, q.created_at
    FROM trees t
    LEFT JOIN qr_codes q ON t.tree_id = q.tree_id AND q.is_active = TRUE
    ORDER BY t.scientific_name
");
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>QR Code Management</h1>
    <a href="dashboard.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <!-- Status Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Generate New QR Codes</h5>
    </div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-8">
          <select name="tree_id" class="form-select" required>
            <option value="">Select a Tree</option>
            <?php while ($tree = fetch_assoc($trees)): ?>
              <option value="<?= $tree['tree_id'] ?>">
                <?= $tree['scientific_name'] ?> (<?= $tree['tree_code'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <input type="hidden" name="action" value="generate">
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-qrcode me-1"></i> Generate QR
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Existing QR Codes</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Tree</th>
              <th>QR Code</th>
              <th>Generated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Reset pointer
            mysqli_data_seek($trees, 0);
            while ($tree = fetch_assoc($trees)):
              if (empty($tree['qr_path'])) continue;
            ?>
              <tr>
                <td>
                  <strong><?= $tree['scientific_name'] ?></strong><br>
                  <small class="text-muted"><?= $tree['tree_code'] ?></small>
                </td>
                <td>
                  <img src="<?= BASE_URL . '/' . $tree['qr_path'] ?>"
                    class="img-thumbnail"
                    style="width: 80px; height: 80px;"
                    alt="QR Code">
                </td>
                <td><?= date('M j, Y', strtotime($tree['created_at'])) ?></td>
                <td>
                  <a href="<?= BASE_URL . '/' . $tree['qr_path'] ?>"
                    download="QR_<?= $tree['tree_code'] ?>.png"
                    class="btn btn-sm btn-success me-2">
                    <i class="fas fa-download"></i> Download
                  </a>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="tree_id" value="<?= $tree['tree_id'] ?>">
                    <input type="hidden" name="action" value="generate">
                    <button type="submit" class="btn btn-sm btn-primary">
                      <i class="fas fa-sync-alt"></i> Regenerate
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>