<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/qr_functions.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../../login.php');
    exit;
}

$tree_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Archive QR codes instead of deleting
    query("UPDATE qr_codes SET is_active = FALSE WHERE tree_id = $tree_id");
    
    // Then delete the tree
    query("DELETE FROM trees WHERE tree_id = $tree_id");
    
    $_SESSION['message'] = "Tree deleted successfully (QR codes archived)";
    header('Location: ../index.php');
    exit;
}

$page_title = 'Delete Tree';
require_once '../../includes/header.php';
?>
<div class="container mt-4">

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