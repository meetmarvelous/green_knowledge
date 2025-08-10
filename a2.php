// Handle QR Download
if (isset($_GET['download'])) {
  $qr_id = intval($_GET['download']);
  $qr = fetch_assoc(query("SELECT * FROM qr_codes WHERE qr_id = $qr_id"));
  
  if ($qr && file_exists($qr['qr_path'])) {
      // Generate clean filename
      $tree = get_tree_by_id($qr['tree_id']);
      $clean_name = preg_replace('/[^a-z0-9]/i', '_', $tree['scientific_name']);
      $filename = "QR_{$clean_name}_{$qr['tree_id']}.png";
      
      // Validate the QR image file
      if (exif_imagetype($qr['qr_path']) !== IMAGETYPE_PNG) {
          $_SESSION['message'] = "Invalid QR code image format";
          $_SESSION['message_type'] = 'danger';
          header("Location: qr_management.php");
          exit;
      }

      // Force download with proper headers
      header('Content-Type: image/png');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Content-Length: ' . filesize($qr['qr_path']));
      header('Content-Transfer-Encoding: binary');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Expires: 0');
      
      // Clear output buffer and send file
      ob_clean();
      flush();
      readfile($qr['qr_path']);
      exit;
  } else {
      $_SESSION['message'] = "QR code file not found";
      $_SESSION['message_type'] = 'danger';
      header("Location: qr_management.php");
      exit;
  }
}