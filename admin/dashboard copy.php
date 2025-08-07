<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Admin Dashboard';
require_once '../includes/header.php';

// Get stats
$trees = query("SELECT COUNT(*) AS count FROM trees");
$trees_count = fetch_assoc($trees)['count'];

$families = query("SELECT COUNT(*) AS count FROM families");
$families_count = fetch_assoc($families)['count'];

$photos = query("SELECT COUNT(*) AS count FROM tree_photos");
$photos_count = fetch_assoc($photos)['count'];
?>

<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Trees</h5>
                    <h2><?= $trees_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Plant Families</h5>
                    <h2><?= $families_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tree Photos</h5>
                    <h2><?= $photos_count ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex">
                <a href="trees/add.php" class="btn btn-success me-md-2">
                    <i class="fas fa-plus me-1"></i> Add New Tree
                </a>
                <a href="trees/index.php" class="btn btn-primary me-md-2">
                    <i class="fas fa-tree me-1"></i> Manage Trees
                </a>
                <a href="qr_generator.php" class="btn btn-info me-md-2">
                    <i class="fas fa-qrcode me-1"></i> Generate QR Codes
                </a>
                <a href="qr_management.php" class="btn btn-info me-md-2">
                    <i class="fas fa-qrcode me-1"></i> Manage QR Codes
                </a>
                <a href="export.php" class="btn btn-secondary">
                    <i class="fas fa-download me-1"></i> Export Data
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>