<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if (!isset($_GET['tree_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tree ID required']);
    exit;
}

$tree_id = intval($_GET['tree_id']);
$size = isset($_GET['size']) ? intval($_GET['size']) : 10;

$tree = get_tree_by_id($tree_id);
if (!$tree) {
    echo json_encode(['success' => false, 'message' => 'Tree not found']);
    exit;
}

// Generate QR code if not exists or needs update
$qr_file = QR_CODES_DIR . "tree_$tree_id.png";
$url = BASE_URL . "/pages/tree.php?id=$tree_id";

if (!file_exists($qr_file) {
    QRcode::png($url, $qr_file, QR_ECLEVEL_L, $size);
} else {
    // Regenerate if size is different
    $current_size = getimagesize($qr_file);
    if ($current_size[0] != $size * 10) { // PHP QR Code size * 10 = pixels
        QRcode::png($url, $qr_file, QR_ECLEVEL_L, $size);
    }
}

echo json_encode([
    'success' => true,
    'qr_path' => BASE_URL . '/' . $qr_file,
    'tree_code' => $tree['tree_code'],
    'scientific_name' => $tree['scientific_name']
]);
?>