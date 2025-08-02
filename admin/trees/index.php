<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Manage Trees';
require_once '../../includes/header.php';

$trees = get_all_trees();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Manage Trees</h1>
    <a href="add.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Add New Tree
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Scientific Name</th>
                        <th>Family</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tree = fetch_assoc($trees)): ?>
                        <tr>
                            <td><?= $tree['tree_code'] ?></td>
                            <td><?= $tree['scientific_name'] ?></td>
                            <td><?= $tree['family_name'] ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $tree['conservation_status'] == 'Endangered' ? 'danger' : 
                                    ($tree['conservation_status'] == 'Vulnerable' ? 'warning' : 'success') 
                                ?>">
                                    <?= $tree['conservation_status'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $tree['tree_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?= $tree['tree_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/pages/tree.php?id=<?= $tree['tree_id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>