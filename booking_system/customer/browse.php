<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Service.php';
requireLogin();

$categoryId = $_GET['category_id'] ?? null;
$categories = Service::allCategories();
$services = $categoryId ? Service::byCategory($categoryId) : Service::allServices();

$pageTitle = 'Browse Services';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3 class="mb-3">Browse Services</h3>

<div class="mb-4">
  <a href="/customer/browse.php" class="btn btn-sm <?= !$categoryId ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
  <?php foreach ($categories as $cat): ?>
    <a href="?category_id=<?= $cat['category_id'] ?>"
       class="btn btn-sm <?= $categoryId == $cat['category_id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
       <i class="bi <?= e($cat['icon']) ?>"></i> <?= e($cat['category_name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="row g-4">
<?php foreach ($services as $s): ?>
  <div class="col-md-4">
    <div class="card card-service h-100">
      <div class="card-body">
        <span class="badge bg-secondary mb-2"><?= e($s['category_name'] ?? '') ?></span>
        <h5 class="card-title"><?= e($s['service_name']) ?></h5>
        <p class="text-muted mb-1"><i class="bi bi-person"></i> <?= e($s['provider_name']) ?></p>
        <p class="text-muted mb-1"><i class="bi bi-geo-alt"></i> <?= e($s['location']) ?></p>
        <p class="text-muted mb-1"><i class="bi bi-clock"></i> <?= (int)$s['duration_minutes'] ?> mins</p>
        <p class="fw-bold text-primary">RM <?= number_format($s['price'],2) ?></p>
        <a href="/customer/service.php?id=<?= $s['service_id'] ?>" class="btn btn-primary w-100">View Availability</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($services)): ?><p class="text-muted">No services found.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
