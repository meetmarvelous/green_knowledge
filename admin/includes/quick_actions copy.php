<div class="d-flex flex-wrap gap-2">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="trees/index.php" class="btn btn-sm btn-outline-success">
        <i class="fas fa-tree"></i> Manage Trees
    </a>
    <a href="family.php" class="btn btn-sm btn-outline-info">
        <i class="fas fa-seedling"></i> Manage Families
    </a>
    <a href="qr_management.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-qrcode"></i> QR Codes
    </a>
    <?php if (is_admin()): ?>
    <a href="users/index.php" class="btn btn-sm btn-outline-dark">
        <i class="fas fa-users"></i> User Management
    </a>
    <?php endif; ?>
</div>