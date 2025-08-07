<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
  header('Location: ../login.php');
  exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$unapproved_count = num_rows(query("SELECT * FROM trees WHERE approved = 0"));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?> Admin - <?= ucfirst(str_replace('.php', '', $current_page)) ?></title>
  <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div class="admin-container">
    <!-- Admin Sidebar -->
    <div class="admin-sidebar bg-dark text-white">
      <div class="sidebar-header p-3">
        <h4 class="text-center">
          <i class="fas fa-tree me-2"></i><?= SITE_NAME ?>
        </h4>
        <hr class="bg-light">
        <div class="user-info text-center">
          <p class="mb-1">Welcome, <?= $_SESSION['username'] ?></p>
          <small class="text-muted"><?= ucfirst($_SESSION['role']) ?></small>
        </div>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item <?= str_contains($current_page, 'trees/') ? 'active' : '' ?>">
          <a class="nav-link" href="trees/index.php">
            <i class="fas fa-tree me-2"></i> Manage Trees
            <?php if ($unapproved_count > 0): ?>
              <span class="badge bg-danger float-end"><?= $unapproved_count ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item <?= $current_page === 'qr_management.php' ? 'active' : '' ?>">
          <a class="nav-link" href="qr_management.php">
            <i class="fas fa-qrcode me-2"></i> QR Codes
          </a>
        </li>
        <li class="nav-item <?= $current_page === 'families.php' ? 'active' : '' ?>">
          <a class="nav-link" href="families.php">
            <i class="fas fa-layer-group me-2"></i> Plant Families
          </a>
        </li>
        <li class="nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
          <a class="nav-link" href="users.php">
            <i class="fas fa-users me-2"></i> Users
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../logout.php">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </a>
        </li>
      </ul>
    </div>

    <!-- Main Content Area -->
    <div class="admin-main">
      <!-- Quick Actions Bar -->
      <div class="quick-actions bg-light p-3 border-bottom">
        <div class="container-fluid">
          <div class="d-flex flex-wrap justify-content-start">
            <a href="trees/add.php" class="btn btn-success btn-sm m-1">
              <i class="fas fa-plus-circle me-1"></i> Add Tree
            </a>
            <a href="qr_management.php" class="btn btn-info btn-sm m-1">
              <i class="fas fa-qrcode me-1"></i> Generate QR
            </a>
            <a href="families.php" class="btn btn-primary btn-sm m-1">
              <i class="fas fa-layer-group me-1"></i> Add Family
            </a>
            <a href="export.php" class="btn btn-secondary btn-sm m-1">
              <i class="fas fa-file-export me-1"></i> Export Data
            </a>
            <?php if (is_admin()): ?>
              <a href="users.php" class="btn btn-warning btn-sm m-1">
                <i class="fas fa-user-plus me-1"></i> Add User
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Main Content Container -->
      <div class="admin-content p-4">
        <!-- Messages/Alerts -->
        <?php if (isset($_SESSION['message'])): ?>
          <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>