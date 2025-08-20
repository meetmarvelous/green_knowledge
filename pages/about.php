<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$page_title = 'About';
require_once '../includes/header.php';
?>

<!-- Page Banner -->
<section class="page-banner" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/about.jpg');">
    <div class="banner-content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/home.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Page</li>
            </ol>
        </nav>
        <h1 class="banner-title">About Page</h1>
    </div>
</section>

<div class="container mt-4">
<div class="card mb-4">
    <div class="card-body">
        <h1 class="mb-4">About GREEN KNOWLEDGE</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Project Overview</h3>
                <p>The GREEN KNOWLEDGE web application is a digital initiative by the University of Ibadan Botanical Garden to document and provide easy access to information about the diverse tree species in our collection.</p>
                <p>This platform serves as both an educational resource and a conservation tool, helping visitors, researchers, and students learn about our botanical heritage.</p>
                
                <h3 class="mt-4">Features</h3>
                <ul>
                    <li>Comprehensive database of 160 trees across 40 species from 20 families</li>
                    <li>QR code identification system for instant tree information access</li>
                    <li>Detailed botanical information for each species</li>
                    <li>Search and filter functionality</li>
                    <li>Admin dashboard for data management</li>
                </ul>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3>University of Ibadan Botanical Garden</h3>
                        <p>Established in 1948, the University of Ibadan Botanical Garden is one of the oldest and most significant botanical gardens in Nigeria. It serves as:</p>
                        <ul>
                            <li>A center for plant conservation</li>
                            <li>An outdoor laboratory for botanical research</li>
                            <li>An educational resource for students and visitors</li>
                            <li>A green space for recreation and relaxation</li>
                        </ul>
                        <p>The garden covers approximately 40 hectares and contains diverse plant collections including medicinal plants, economic trees, and ornamental species.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <h3 class="mt-4">Project Team</h3>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Dr. Adebayo Johnson</h5>
                        <p class="text-muted">Project Lead / Botanist</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Engr. Fatima Musa</h5>
                        <p class="text-muted">Technical Lead</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Miss Chioma Eze</h5>
                        <p class="text-muted">Research Assistant</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>