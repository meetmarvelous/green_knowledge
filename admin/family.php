<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Plant Families Management';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
<?php 
require_once __DIR__ . '/includes/quick_actions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $family_id = intval($_POST['family_id'] ?? 0);
    
    try {
        switch ($action) {
            case 'add':
                $name = trim($_POST['family_name']);
                $description = trim($_POST['family_description'] ?? '');
                
                if (empty($name)) {
                    throw new Exception('Family name is required');
                }
                
                $sql = "INSERT INTO families (family_name, family_description) 
                        VALUES ('" . escape_string($name) . "', '" . escape_string($description) . "')";
                if (!query($sql)) {
                    throw new Exception('Error adding family: ' . error());
                }
                
                $_SESSION['message'] = 'Family added successfully';
                $_SESSION['message_type'] = 'success';
                break;
                
            case 'edit':
                $name = trim($_POST['family_name']);
                $description = trim($_POST['family_description'] ?? '');
                
                if (empty($name)) {
                    throw new Exception('Family name is required');
                }
                
                $sql = "UPDATE families SET 
                        family_name = '" . escape_string($name) . "', 
                        family_description = '" . escape_string($description) . "'
                        WHERE family_id = $family_id";
                
                if (!query($sql)) {
                    throw new Exception('Error updating family: ' . error());
                }
                
                $_SESSION['message'] = 'Family updated successfully';
                $_SESSION['message_type'] = 'success';
                break;
                
            case 'delete':
                // Check if any trees are using this family
                $trees_using = num_rows(query("SELECT tree_id FROM trees WHERE family_id = $family_id"));
                
                if ($trees_using > 0) {
                    throw new Exception("Cannot delete: $trees_using trees are using this family");
                }
                
                $sql = "DELETE FROM families WHERE family_id = $family_id";
                if (!query($sql)) {
                    throw new Exception('Error deleting family: ' . error());
                }
                
                $_SESSION['message'] = 'Family deleted successfully';
                $_SESSION['message_type'] = 'success';
                break;
        }
        
        header('Location: family.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}

// Determine current view mode
$view = $_GET['view'] ?? 'list';
$family_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<div class="container">
    <!-- Status Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <?php if ($view === 'list'): ?>
        <!-- List View -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Plant Families</h1>
            <a href="family.php?view=add" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Family
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Family Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $families = query("SELECT * FROM families ORDER BY family_name");
                            while ($family = fetch_assoc($families)): 
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($family['family_name']) ?></td>
                                    <td><?= htmlspecialchars($family['family_description'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="family.php?view=edit&id=<?= $family['family_id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="family_id" value="<?= $family['family_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure? This cannot be undone.')">
                                                <i class="fas fa-trash"></i> Delete
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

    <?php elseif ($view === 'add' || $view === 'edit'): ?>
        <!-- Add/Edit Form -->
        <?php
        $family = [];
        $form_title = 'Add New Plant Family';
        $form_action = 'add';
        
        if ($view === 'edit' && $family_id > 0) {
            $family = fetch_assoc(query("SELECT * FROM families WHERE family_id = $family_id"));
            if (!$family) {
                header('Location: family.php');
                exit;
            }
            $form_title = 'Edit Plant Family';
            $form_action = 'edit';
        }
        ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= $form_title ?></h1>
            <a href="family.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?= $form_title ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $form_action ?>">
                            <?php if ($view === 'edit'): ?>
                                <input type="hidden" name="family_id" value="<?= $family_id ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="family_name" class="form-label">Family Name *</label>
                                <input type="text" class="form-control" id="family_name" name="family_name" 
                                       value="<?= htmlspecialchars($family['family_name'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="family_description" class="form-label">Description</label>
                                <textarea class="form-control" id="family_description" name="family_description" 
                                          rows="3"><?= htmlspecialchars($family['family_description'] ?? '') ?></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="family.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <?= $view === 'add' ? 'Add Family' : 'Update Family' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>