<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ayo_green');

// App configuration
define('BASE_URL', 'http://localhost/Recent/Green');
define('SITE_NAME', 'GREEN KNOWLEDGE');
define('ADMIN_EMAIL', 'admin@botanicalgarden.ui.edu.ng');

// File paths
define('TREE_PHOTOS_DIR', 'assets/images/tree_photos/');
define('QR_CODES_DIR', 'assets/images/qr_codes/');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>