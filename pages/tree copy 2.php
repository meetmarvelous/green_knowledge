<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';



if (!isset($_GET['id'])) {
  header('Location: list.php');
  exit;
}

$tree_id = intval($_GET['id']);
$tree = get_tree_by_id($tree_id);
$photos = get_tree_photos($tree_id);

if (!$tree) {
  header('Location: list.php');
  exit;
}

$page_title = $tree['scientific_name'];
require_once '../includes/header.php';
?>

<!-- Page Banner -->
<section class="page-banner" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/trees-banner.jpg');">
  <div class="banner-content">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/home.php">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/list.php">Tree Directory</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($tree['scientific_name']) ?></li>
      </ol>
    </nav>
    <h1 class="banner-title">Tree Directory</h1>
  </div>
</section>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h2 class="mb-0"><?= $tree['scientific_name'] ?></h2>
          <div class="d-flex justify-content-between">
            <span><?= $tree['common_name'] ?></span>
            <span><?= $tree['tree_code'] ?></span>
          </div>
        </div>



        <!-- Update the tree details section -->
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <h5>Common Name</h5>
              <p><?= $tree['common_name'] ?></p>

              <h5>Family</h5>
              <p><?= $tree['family_name'] ?></p>

              <h5>Origin & Distribution</h5>
              <p><?= $tree['origin_distribution'] ?></p>

              <h5>Conservation Status</h5>
              <p class="badge bg-<?=
                                  $tree['conservation_status'] == 'Endangered' ? 'danger' : ($tree['conservation_status'] == 'Vulnerable' ? 'warning' : 'success')
                                  ?>">
                <?= $tree['conservation_status'] ?>
              </p>
            </div>
            <div class="col-md-6">
              <h5>Tree Code</h5>
              <p><?= $tree['tree_code'] ?></p>

              <?php if (!empty($tree['gps_coordinates'])): ?>
                <h5>GPS Coordinates</h5>
                <p><?= $tree['gps_coordinates'] ?></p>
              <?php endif; ?>
            </div>
          </div>

          <h4>Description</h4>
          <p><?= $tree['description'] ?></p>

          <h4>Ecological Information</h4>
          <p><?= $tree['ecological_info'] ?></p>

          <h4>Uses & Importance</h4>
          <p><?= $tree['uses_importance'] ?></p>
        </div>


      </div>
    </div>

    <div class="col-md-4">
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h4 class="mb-0">Tree Photos</h4>
        </div>
        <div class="card-body">
          <div id="treePhotosCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php $active = true;
              while ($photo = fetch_assoc($photos)): ?>
                <div class="carousel-item <?= $active ? 'active' : '' ?>">
                  <img src="<?= BASE_URL ?>/assets/images/tree_photos/<?= $photo['photo_path'] ?>"
                    class="d-block w-100"
                    alt="<?= $tree['scientific_name'] ?>">
                  <?php if (!empty($photo['caption'])): ?>
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50">
                      <p><?= $photo['caption'] ?></p>
                    </div>
                  <?php endif; ?>
                </div>
              <?php $active = false;
              endwhile; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#treePhotosCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#treePhotosCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-light">
          <h4 class="mb-0">Quick Info</h4>
        </div>
        <div class="card-body">
          <dl class="row">
            <dt class="col-sm-5">Scientific Name</dt>
            <dd class="col-sm-7"><?= $tree['scientific_name'] ?></dd>

            <dt class="col-sm-5">Family</dt>
            <dd class="col-sm-7"><?= $tree['family_name'] ?></dd>

            <dt class="col-sm-5">Tree Code</dt>
            <dd class="col-sm-7"><?= $tree['tree_code'] ?></dd>

            <dt class="col-sm-5">Conservation</dt>
            <dd class="col-sm-7"><?= $tree['conservation_status'] ?></dd>

            <dt class="col-sm-5">Health Status</dt>
            <dd class="col-sm-7"><?= $tree['health_status'] ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

  <?php require_once '../includes/footer.php'; ?>