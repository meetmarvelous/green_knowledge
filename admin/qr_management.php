<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

// Handle download request first
if (isset($_GET['download'])) {
    $qr_id = intval($_GET['download']);
    $qr = fetch_assoc(query("SELECT q.*, t.tree_code 
                           FROM qr_codes q
                           JOIN trees t ON q.tree_id = t.tree_id
                           WHERE q.qr_id = $qr_id"));
    
    if ($qr && file_exists($qr['qr_path'])) {
        // Clean the output buffer
        ob_clean();
        
        // Set proper headers
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="QR_' . $qr['tree_code'] . '.png"');
        header('Content-Length: ' . filesize($qr['qr_path']));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Read and output the file
        readfile($qr['qr_path']);
        exit;
    } else {
        $_SESSION['message'] = "QR code file not found or inaccessible";
        $_SESSION['message_type'] = 'danger';
        header("Location: qr_management.php");
        exit;
    }
}

// Rest of your QR management code
$page_title = 'QR Code Management';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/includes/quick_actions.php';

// Handle generation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tree_id = intval($_POST['tree_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate' && $tree_id > 0) {
        $result = generate_qr_with_record($tree_id);
        if ($result['success']) {
            $_SESSION['message'] = "QR code generated successfully for " . $result['scientific_name'];
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error: " . $result['message'];
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: qr_management.php");
        exit;
    }
}

// Get all trees with their active QR codes
$trees = query("
    SELECT t.tree_id, t.scientific_name, t.tree_code, 
           q.qr_id, q.qr_path, q.created_at
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

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
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
                                    <a href="qr_management.php?download=<?= $tree['qr_id'] ?>" 
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