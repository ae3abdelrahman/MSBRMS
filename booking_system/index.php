<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/models/Service.php';
$categories = Service::allCategories();
$pageTitle = 'Home';
include __DIR__ . '/views/layouts/header.php';
?>
<div class="hero text-center mb-5">
  <h1 class="fw-bold">Book Any Service, Anytime, Anywhere</h1>
  <p class="lead">Clinics, salons, meeting rooms and consultations — all in one reservation system.</p>
  <?php if (!isLoggedIn()): ?>
    <a href="/register.php" class="btn btn-light btn-lg mt-2">Get Started</a>
  <?php else: ?>
    <a href="/customer/browse.php" class="btn btn-light btn-lg mt-2">Browse Services</a>
  <?php endif; ?>
</div>

<h3 class="mb-3">Our Service Categories</h3>
<div class="row g-4">
<?php foreach ($categories as $cat): ?>
  <div class="col-md-3 col-sm-6">
    <div class="card card-service h-100 text-center p-3">
      <i class="bi <?= e($cat['icon']) ?> fs-1 text-primary"></i>
      <div class="card-body">
        <h5 class="card-title"><?= e($cat['category_name']) ?></h5>
        <p class="card-text small text-muted"><?= e($cat['description']) ?></p>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/views/layouts/footer.php'; ?>
