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
require_once __DIR__ . '/includes/quick_actions.php';

// Handle QR Generation
if (isset($_GET['generate'])) {
  $tree_id = intval($_GET['generate']);
  try {
    $result = generate_qr_with_record($tree_id);

    if ($result['success']) {
      $_SESSION['message'] = "QR Code generated successfully!";
      $_SESSION['message_type'] = 'success';
    } else {
      throw new Exception($result['message']);
    }
  } catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
  }
  header("Location: qr_management.php");
  exit;
}

// Handle QR Download
if (isset($_GET['download'])) {
  $qr_id = intval($_GET['download']);
  $qr = fetch_assoc(query("SELECT * FROM qr_codes WHERE qr_id = $qr_id"));

  // Construct full server path
  $full_path = ROOT_PATH . $qr['qr_path'];

  if ($qr && file_exists($full_path)) {
      // Validate image format
      if (exif_imagetype($full_path) !== IMAGETYPE_PNG) {
          $_SESSION['message'] = "Invalid QR code image format";
          $_SESSION['message_type'] = 'danger';
          header("Location: qr_management.php");
          exit;
      }

      // Generate clean filename
      $tree = get_tree_by_id($qr['tree_id']);
      $clean_name = preg_replace('/[^a-z0-9]/i', '_', $tree['scientific_name']);
      $filename = "QR_{$clean_name}_{$qr['tree_id']}.png";

      // Force download with proper headers
      header('Content-Type: image/png');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Content-Length: ' . filesize($full_path));
      header('Content-Transfer-Encoding: binary');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Expires: 0');

      // Clear output buffer and send file
      ob_clean();
      flush();
      readfile($full_path);
      exit;
  } else {
      $_SESSION['message'] = "QR code file not found";
      $_SESSION['message_type'] = 'danger';
      header("Location: qr_management.php");
      exit;
  }
}


// Get all trees with their active QR codes
$trees = query("
    SELECT t.tree_id, t.scientific_name, t.tree_code, 
           q.qr_id, q.qr_path, q.created_at
    FROM trees t
    LEFT JOIN qr_codes q ON t.tree_id = q.tree_id AND q.is_active = 1
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

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?>">
      <?= $_SESSION['message'] ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">QR Code Generator</h5>
    </div>
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-8">
          <select name="generate" class="form-select" required>
            <option value="">Select a Tree to Generate QR</option>
            <?php while ($tree = fetch_assoc($trees)): ?>
              <option value="<?= $tree['tree_id'] ?>">
                <?= $tree['scientific_name'] ?> (<?= $tree['tree_code'] ?>)
                <?= $tree['qr_id'] ? ' - QR Exists' : '' ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-qrcode"></i> Generate QR
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
              if (empty($tree['qr_id'])) continue;
            ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($tree['scientific_name']) ?></strong><br>
                  <small class="text-muted"><?= $tree['tree_code'] ?></small>
                </td>
                <td>
                  <img src="<?= BASE_URL . '/' . str_replace(ROOT_PATH, '', $tree['qr_path']) ?>"
                    class="img-thumbnail"
                    style="width: 100px; height: 100px;"
                    alt="QR Code for <?= htmlspecialchars($tree['scientific_name']) ?>">
                </td>
                <td><?= date('M j, Y H:i', strtotime($tree['created_at'])) ?></td>
                <td>
                  <a href="qr_management.php?download=<?= $tree['qr_id'] ?>"
                    class="btn btn-sm btn-success me-2"
                    title="Download high-quality QR code">
                    <i class="fas fa-download"></i> Download
                  </a>
                  <a href="qr_management.php?generate=<?= $tree['tree_id'] ?>"
                    class="btn btn-sm btn-primary"
                    title="Regenerate QR code"
                    onclick="return confirm('Regenerate QR for <?= htmlspecialchars(addslashes($tree['scientific_name'])) ?>?')">
                    <i class="fas fa-sync-alt"></i> Regenerate
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  // Client-side confirmation for regeneration
  document.querySelectorAll('[onclick]').forEach(btn => {
    btn.onclick = function() {
      return confirm(this.getAttribute('title') + '\n\nAre you sure?');
    };
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>