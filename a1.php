// Handle QR Download
// Handle QR Download
if (isset($_GET['download'])) {
  $qr_id = intval($_GET['download']);
  $qr = fetch_assoc(query("SELECT * FROM qr_codes WHERE qr_id = $qr_id"));
  
  // Fix the path by prepending ROOT_PATH
  $full_path = ROOT_PATH . $qr['qr_path']; // This converts "assets/..." to full server path
  
  if ($qr && file_exists($full_path)) {
      // Generate clean filename
      $tree = get_tree_by_id($qr['tree_id']);
      $clean_name = preg_replace('/[^a-z0-9]/i', '_', $tree['scientific_name']);
      $filename = "QR_{$clean_name}_{$qr['tree_id']}.png";
      
      // Force download
      header('Content-Type: image/png');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Content-Length: ' . filesize($full_path));
      readfile($full_path);
      exit;
  } else {
      $_SESSION['message'] = "QR code file not found at: " . $full_path;
      $_SESSION['message_type'] = 'danger';
      header("Location: qr_management.php");
      exit;
  }
}