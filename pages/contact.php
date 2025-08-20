<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$message_sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  // Validation
  if (empty($name)) $errors['name'] = 'Name is required';
  if (empty($email)) {
    $errors['email'] = 'Email is required';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
  }
  if (empty($subject)) $errors['subject'] = 'Subject is required';
  if (empty($message)) $errors['message'] = 'Message is required';

  if (empty($errors)) {
    // In a real application, you would send the email here
    // For this example, we'll just set a flag
    $message_sent = true;

    // You could also store the message in a database
    /*
        $sql = "INSERT INTO contact_messages (name, email, subject, message) 
                VALUES ('" . escape_string($name) . "', 
                        '" . escape_string($email) . "', 
                        '" . escape_string($subject) . "', 
                        '" . escape_string($message) . "')";
        query($sql);
        */
  }
}

$page_title = 'Contact Us';
require_once '../includes/header.php';
?>

<!-- Page Banner -->
<section class="page-banner" style="background-image: url('<?= BASE_URL ?>/assets/images/banners/about.jpg');">
    <div class="banner-content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pages/home.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Page</li>
            </ol>
        </nav>
        <h1 class="banner-title">Contact Page</h1>
    </div>
</section>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h2 class="mb-0">Contact Form</h2>
        </div>
        <div class="card-body">
          <?php if ($message_sent): ?>
            <div class="alert alert-success">
              Thank you for your message! We'll get back to you soon.
            </div>
          <?php else: ?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST">
              <div class="mb-3">
                <label for="name" class="form-label">Your Name *</label>
                <input type="text" class="form-control" id="name" name="name"
                  value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email"
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label for="subject" class="form-label">Subject *</label>
                <input type="text" class="form-control" id="subject" name="subject"
                  value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message *</label>
                <textarea class="form-control" id="message" name="message" rows="5" required>
              <?= htmlspecialchars($_POST['message'] ?? '')
              ?></textarea>
              </div>
              <button type="submit" class="btn btn-success">Send Message</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-light">
          <h2 class="mb-0">Contact Information</h2>
        </div>
        <div class="card-body">
          <h4>University of Ibadan Botanical Garden</h4>
          <address class="mt-3">
            <p><i class="fas fa-map-marker-alt me-2"></i> University of Ibadan, Ibadan, Nigeria</p>
            <p><i class="fas fa-phone me-2"></i> +234 812 345 6789</p>
            <p><i class="fas fa-envelope me-2"></i> botanicalgarden@ui.edu.ng</p>
          </address>

          <hr class="my-4">

          <h4>Visiting Hours</h4>
          <ul class="list-unstyled">
            <li><strong>Monday - Friday:</strong> 8:00 AM - 5:00 PM</li>
            <li><strong>Saturday:</strong> 9:00 AM - 4:00 PM</li>
            <li><strong>Sunday:</strong> Closed</li>
          </ul>

          <hr class="my-4">

          <h4>Follow Us</h4>
          <div class="social-links">
            <a href="#" class="text-success me-3"><i class="fab fa-facebook fa-2x"></i></a>
            <a href="#" class="text-success me-3"><i class="fab fa-twitter fa-2x"></i></a>
            <a href="#" class="text-success me-3"><i class="fab fa-instagram fa-2x"></i></a>
            <a href="#" class="text-success"><i class="fab fa-youtube fa-2x"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once '../includes/footer.php'; ?>