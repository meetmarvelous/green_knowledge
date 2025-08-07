<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/includes/quick_actions.php';

// Get stats
$stats = [
    'trees' => fetch_assoc(query("SELECT COUNT(*) as count FROM trees"))['count'],
    'families' => fetch_assoc(query("SELECT COUNT(*) as count FROM families"))['count'],
    'photos' => fetch_assoc(query("SELECT COUNT(*) as count FROM tree_photos"))['count'],
    'qr_codes' => fetch_assoc(query("SELECT COUNT(*) as count FROM qr_codes WHERE is_active = TRUE"))['count']
];
?>

<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Trees</h5>
                    <h2><?= $stats['trees'] ?></h2>
                    <a href="trees/index.php" class="text-white">Manage Trees</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Families</h5>
                    <h2><?= $stats['families'] ?></h2>
                    <a href="family.php" class="text-white">Manage Families</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Photos</h5>
                    <h2><?= $stats['photos'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Active QR Codes</h5>
                    <h2><?= $stats['qr_codes'] ?></h2>
                    <a href="qr_management.php" class="text-white">Manage QR Codes</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
            <?php require_once __DIR__ . '/includes/quick_actions.php'; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>