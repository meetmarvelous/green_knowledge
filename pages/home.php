<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'Home';
require_once '../includes/header.php';
?>

<!-- Hero Slider -->
<section class="hero-slider-container">
    <!-- Slide 1 -->
    <div class="hero-slide active" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/hero-1.jpg');">
        <div class="hero-slide-content">
            <h1 class="hero-title">Discover Our Botanical Treasures</h1>
            <p class="hero-subtitle">Explore over 160 tree species from 40 different plant families in our living collection</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/pages/list.php" class="btn btn-hero btn-hero-primary">Browse Trees</a>
                <a href="<?= BASE_URL ?>/pages/about.php" class="btn btn-hero btn-hero-secondary">Learn More</a>
            </div>
        </div>
    </div>
    
    <!-- Slide 2 -->
    <div class="hero-slide" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/hero-2.jpg');">
        <div class="hero-slide-content">
            <h1 class="hero-title">Education Through Nature</h1>
            <p class="hero-subtitle">Join our guided tours and learn about plant conservation and biodiversity</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/pages/contact.php" class="btn btn-hero btn-hero-primary">Book a Tour</a>
                <a href="<?= BASE_URL ?>/pages/about.php#education" class="btn btn-hero btn-hero-secondary">Education Programs</a>
            </div>
        </div>
    </div>
    
    <!-- Slide 3 -->
    <div class="hero-slide" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/hero-3.jpg');">
        <div class="hero-slide-content">
            <h1 class="hero-title">QR Code Technology</h1>
            <p class="hero-subtitle">Scan QR codes on our tree tags to access detailed information instantly</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/pages/about.php#technology" class="btn btn-hero btn-hero-primary">How It Works</a>
                <a href="<?= BASE_URL ?>/pages/list.php" class="btn btn-hero btn-hero-secondary">View Trees</a>
            </div>
        </div>
    </div>
</section>

<div class="container mt-4">
<?php 
// Get latest trees
$sql = "SELECT t.*, f.family_name 
        FROM trees t 
        JOIN families f ON t.family_id = f.family_id 
        ORDER BY t.created_at DESC 
        LIMIT 6";
$result = query($sql);
?>

<div class="jumbotron bg-light p-5 mb-4 rounded">
  <h1 class="display-4">Welcome to GREEN KNOWLEDGE</h1>
  <p class="lead">University of Ibadan Botanical Garden's digital repository of tree species</p>
  <hr class="my-4">
  <p>Explore our collection of 160 trees across 40 species from 20 plant families</p>
  <a class="btn btn-success btn-lg" href="list.php" role="button">
    <i class="fas fa-tree me-1"></i> Browse Tree Directory
  </a>
</div>

<h2 class="mb-4">Recently Added Trees</h2>
<div class="row">
  <?php while ($tree = fetch_assoc($result)): ?>
    <div class="col-md-4 mb-4">
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
          <p class="card-text"><?= substr($tree['description'], 0, 100) ?>...</p>
          <a href="tree.php?id=<?= $tree['tree_id'] ?>" class="btn btn-sm btn-outline-success">
            View Details
          </a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<div class="row mt-5">
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-qrcode me-2"></i>QR Code Identification</h5>
        <p class="card-text">Scan QR codes on tree tags to instantly access detailed information on your mobile device.</p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title"><i class="fas fa-book me-2"></i>Educational Resource</h5>
        <p class="card-text">Access botanical information for research, education, and conservation purposes.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>