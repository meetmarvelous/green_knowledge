<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$page_title = 'QR Code Management';
require_once '../includes/header.php';

// Get all trees
$trees = get_all_trees();
?>

<div class="container">
  <h1 class="mb-4">QR Code Management</h1>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Generate Missing QR Codes</h5>
    </div>
    <div class="card-body">
      <p>Generate QR codes for all trees that don't have one yet.</p>
      <a href="qr_generator.php?action=generate_all" class="btn btn-success">
        <i class="fas fa-qrcode me-1"></i> Generate All Missing QR Codes
      </a>
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
              <th>Tree Code</th>
              <th>Scientific Name</th>
              <th>QR Code</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($tree = fetch_assoc($trees)): ?>
              <?php
              $qr_file = QR_CODES_DIR . "tree_{$tree['tree_id']}.png";
              $has_qr = file_exists($qr_file);
              ?>
              <tr>
                <td><?= $tree['tree_code'] ?></td>
                <td><?= $tree['scientific_name'] ?></td>
                <td>
                  <?php if ($has_qr): ?>
                    <img src="<?= BASE_URL . str_replace(__DIR__ . '/../', '', $qr_file) ?>"
                      style="height: 50px;" alt="QR Code">
                  <?php else: ?>
                    <span class="badge bg-warning">Missing</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="qr_generator.php?tree_id=<?= $tree['tree_id'] ?>"
                    class="btn btn-sm btn-primary" title="Generate QR">
                    <i class="fas fa-sync-alt"></i>
                  </a>
                  <?php if ($has_qr): ?>
                    <a href="<?= BASE_URL . str_replace(__DIR__ . '/../', '', $qr_file) ?>"
                      download="QR_<?= $tree['tree_code'] ?>.png"
                      class="btn btn-sm btn-success" title="Download">
                      <i class="fas fa-download"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>