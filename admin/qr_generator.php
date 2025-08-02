<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

// Handle batch generation
if (isset($_GET['action']) && $_GET['action'] === 'generate_all') {
  $trees = get_all_trees();
  while ($tree = fetch_assoc($trees)) {
    generate_qr_code($tree['tree_id']);
  }
  $_SESSION['message'] = "All missing QR codes generated successfully!";
  header('Location: qr_management.php');
  exit;
}

// Handle single generation
if (isset($_GET['tree_id'])) {
  $tree_id = intval($_GET['tree_id']);
  $tree = get_tree_by_id($tree_id);

  if ($tree) {
    generate_qr_code($tree_id);
    $_SESSION['message'] = "QR code for {$tree['scientific_name']} generated successfully!";
  }

  header('Location: qr_management.php');
  exit;
}

// If no action specified, redirect back
header('Location: qr_management.php');
exit;
