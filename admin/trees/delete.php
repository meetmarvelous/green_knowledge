<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$tree_id = intval($_GET['id']);
$tree = get_tree_by_id($tree_id);

if (!$tree) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete associated photos first
    $photos = get_tree_photos($tree_id);
    while ($photo = fetch_assoc($photos)) {
        $photo_path = TREE_PHOTOS_DIR . $photo['photo_path'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }
    query("DELETE FROM tree_photos WHERE tree_id = $tree_id");
    
    // Delete QR code if exists
    $qr_path = QR_CODES_DIR . "tree_$tree_id.png";
    if (file_exists($qr_path)) {
        unlink($qr_path);
    }
    
    // Delete tree record
    query("DELETE FROM trees WHERE tree_id = $tree_id");
    
    $_SESSION['message'] = "Tree deleted successfully!";
    header('Location: index.php');
    exit;
}

$page_title = 'Delete Tree';
require_once '../../includes/header.php';
?>

<div class="card">
    <div class="card-header bg-danger text-white">
        <h2 class="mb-0">Delete Tree</h2>
    </div>
    <div class="card-body">
        <p>Are you sure you want to delete the following tree?</p>
        <h4><?= $tree['scientific_name'] ?> (<?= $tree['tree_code'] ?>)</h4>
        <p class="text-muted">Family: <?= $tree['family_name'] ?></p>
        
        <form method="POST">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>