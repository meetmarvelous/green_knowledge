<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$sort = $_GET['sort'] ?? 'scientific_name';
$order = $_GET['order'] ?? 'ASC';

$page_title = 'Tree Directory';
require_once '../includes/header.php';
?>

<!-- Page Banner -->
<section class="page-banner" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/trees-banner.jpg');">
    <div class="banner-content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/home.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tree Directory</li>
            </ol>
        </nav>
        <h1 class="banner-title">Tree Directory</h1>
    </div>
</section>

<div class="container mt-4">
<?php 
$trees = get_all_trees($sort, $order);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1>Tree Directory</h1>
  <div class="btn-group">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
      Sort: <?= ucfirst(str_replace('_', ' ', $sort)) ?> (<?= $order ?>)
    </button>
    <ul class="dropdown-menu">
      <li>
        <h6 class="dropdown-header">Sort By</h6>
      </li>
      <li><a class="dropdown-item" href="?sort=scientific_name&order=ASC">Scientific Name (A-Z)</a></li>
      <li><a class="dropdown-item" href="?sort=scientific_name&order=DESC">Scientific Name (Z-A)</a></li>
      <li><a class="dropdown-item" href="?sort=family_id&order=ASC">Family (A-Z)</a></li>
      <li><a class="dropdown-item" href="?sort=tree_code&order=ASC">Tree Code</a></li>
    </ul>
  </div>
</div>

<div class="row">
  <?php while ($tree = fetch_assoc($trees)): ?>
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card h-100">
        <?php
        $photos = get_tree_photos($tree['tree_id']);
        $first_photo = fetch_assoc($photos);
        ?>
        <img src="<?= BASE_URL . '/' . TREE_PHOTOS_URL . ($first_photo['photo_path'] ?? 'tree.png') ?>" 
          class="card-img-top"
          alt="<?= $tree['scientific_name'] ?>"
          style="height: 200px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title"><?= $tree['scientific_name'] ?></h5>
          <h6 class="card-subtitle mb-2 text-muted"><?= $tree['family_name'] ?></h6>
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge bg-<?=
                                  $tree['conservation_status'] == 'Endangered' ? 'danger' : ($tree['conservation_status'] == 'Vulnerable' ? 'warning' : 'success')
                                  ?>">
              <?= $tree['conservation_status'] ?>
            </span>
            <span class="text-muted small"><?= $tree['tree_code'] ?></span>
          </div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="tree.php?id=<?= $tree['tree_id'] ?>" class="btn btn-sm btn-outline-success">
            View Details
          </a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>