</div> <!-- Close admin-content div -->

<!-- Admin Footer -->
<footer class="admin-footer bg-dark text-white p-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?> - Admin Panel</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <span class="text-muted">Version 1.0.0</span> | 
                    <a href="<?= BASE_URL ?>/pages/about.php" class="text-white">About</a> | 
                    <a href="<?= BASE_URL ?>/pages/contact.php" class="text-white">Contact</a>
                </p>
            </div>
        </div>
    </div>
</footer>
</div> <!-- Close admin-main div -->
</div> <!-- Close admin-container div -->

<!-- JavaScript Libraries -->
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>

<!-- Page-specific scripts -->
<?php if (function_exists('page_scripts')): ?>
<?php page_scripts(); ?>
<?php endif; ?>
</body>
</html>