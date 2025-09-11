<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$query = $_GET['q'] ?? '';
$family_filter = $_GET['family'] ?? '';
$conservation_filter = $_GET['conservation'] ?? '';

$page_title = 'Search Results';
require_once '../includes/header.php';
?>
<div class="container mt-4">
  <?php
  $results = search_trees($query, $family_filter, $conservation_filter);
  $families = get_families();
  ?>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="search.php">
        <div class="row">
          <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Search trees..." value="<?= htmlspecialchars($query) ?>">
          </div>
          <div class="col-md-3">
            <select name="family" class="form-select">
              <option value="">All Families</option>
              <?php while ($family = fetch_assoc($families)): ?>
                <option value="<?= $family['family_id'] ?>" <?= $family_filter == $family['family_id'] ? 'selected' : '' ?>>
                  <?= $family['family_name'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="conservation" class="form-select">
              <option value="">All Statuses</option>
              <option value="Endangered" <?= $conservation_filter == 'Endangered' ? 'selected' : '' ?>>Endangered</option>
              <option value="Vulnerable" <?= $conservation_filter == 'Vulnerable' ? 'selected' : '' ?>>Vulnerable</option>
              <option value="Least Concern" <?= $conservation_filter == 'Least Concern' ? 'selected' : '' ?>>Least Concern</option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-success w-100">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <h3 class="mb-4">Search Results</h3>

  <?php if (num_rows($results) > 0): ?>
    <div class="list-group">
      <?php while ($tree = fetch_assoc($results)): ?>
        <a href="tree.php?id=<?= $tree['tree_id'] ?>" class="list-group-item list-group-item-action">
          <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><?= $tree['scientific_name'] ?></h5>
            <small><?= $tree['tree_code'] ?></small>
          </div>
          <p class="mb-1"><?= $tree['family_name'] ?></p>
          <small class="text-muted"><?= $tree['conservation_status'] ?></small>
        </a>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">
      No trees found matching your search criteria.
    </div>
  <?php endif; ?>

  <?php require_once '../includes/footer.php'; ?>