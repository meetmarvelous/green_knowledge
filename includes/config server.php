<?php
// Database configuration
define('DB_HOST', 'sql201.iceiy.com');
define('DB_USER', 'icei_39665027');
define('DB_PASS', 'Mahvellous1698');
define('DB_NAME', 'icei_39665027_green');

// App configuration - Use absolute paths for file operations
define('BASE_URL', 'http://localhost/Recent/Green');
define('SITE_NAME', 'GREEN KNOWLEDGE');
define('ADMIN_EMAIL', 'admin@botanicalgarden.ui.edu.ng');

// File paths - Now using absolute server paths
define('ROOT_PATH', __DIR__ . '/../'); // Points to Green/ directory
define('TREE_PHOTOS_DIR', ROOT_PATH . 'assets/images/tree_photos/');
define('QR_CODES_DIR', ROOT_PATH . 'assets/images/qr_codes/');

// Ensure directories exist
if (!file_exists(TREE_PHOTOS_DIR)) {
    mkdir(TREE_PHOTOS_DIR, 0755, true);
}
if (!file_exists(QR_CODES_DIR)) {
    mkdir(QR_CODES_DIR, 0755, true);
}

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>