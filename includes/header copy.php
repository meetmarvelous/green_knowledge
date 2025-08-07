<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';  // This contains is_logged_in()

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?> - <?= $page_title ?? 'Botanical Garden' ?></title>
  <!-- <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="<?= BASE_URL ?>/assets/css/styles.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>">
        <i class="fas fa-tree me-2"></i><?= SITE_NAME ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/list.php">Tree Directory</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/contact.php">Contact</a></li>
        </ul>
        <form class="d-flex" action="pages/search.php" method="GET">
          <input class="form-control me-2" type="search" name="q" placeholder="Search trees...">
          <button class="btn btn-light" type="submit">Search</button>
        </form>
        <?php if (is_logged_in()): ?>
          <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-light ms-2">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/admin/login.php" class="btn btn-outline-light ms-2">
            <i class="fas fa-sign-in-alt me-1"></i> Admin Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
  <div class="container mt-4">