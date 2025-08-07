<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

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

// Generate QR code if not exists
$qr_file = QR_CODES_DIR . "tree_$tree_id.png";
if (!file_exists($qr_file)) {
  generate_qr_code($tree_id);
}

$page_title = $tree['scientific_name'];
require_once '../includes/header.php';
?>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h2 class="mb-0"><?= $tree['scientific_name'] ?></h2>
        <div class="d-flex justify-content-between">
          <span><?= $tree['common_names'] ?></span>
          <span><?= $tree['tree_code'] ?></span>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
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
            <h5>Health Status</h5>
            <p><?= $tree['health_status'] ?></p>

            <h5>Location</h5>
            <p>Lat: <?= $tree['geotag_lat'] ?>, Lng: <?= $tree['geotag_lng'] ?></p>

            <h5>QR Code</h5>
            <img src="<?= BASE_URL . '/' . $qr_file ?>" alt="QR Code" class="img-thumbnail" style="width: 100px;">
            <a href="<?= BASE_URL . '/' . $qr_file ?>" download class="btn btn-sm btn-outline-success ms-2">
              <i class="fas fa-download me-1"></i> Download
            </a>
          </div>
        </div>

        <h4>Physical Description</h4>
        <p><?= $tree['physical_description'] ?></p>

        <h4>Ecological Information</h4>
        <p><?= $tree['ecological_info'] ?></p>

        <h4>Uses & Economic Value</h4>
        <p><?= $tree['uses_economic'] ?></p>

        <h4>Remarks</h4>
        <p><?= $tree['remarks'] ?></p>
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
                <img src="<?= BASE_URL . '/' . TREE_PHOTOS_DIR . $photo['photo_path'] ?>"
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