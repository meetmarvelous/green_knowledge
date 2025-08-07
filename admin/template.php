<?php
define('ADMIN_CONTEXT', true);
require_once __DIR__ . '/includes/admin_header.php';

// Page-specific content
$page_title = "Page Title";
?>
<div class="container-fluid">
    <h2 class="my-4"><?= $page_title ?></h2>
    
    <!-- Your content here -->
    
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>