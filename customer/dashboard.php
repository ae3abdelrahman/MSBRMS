<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
requireLogin();
if (isAdmin()) { header('Location: /admin/dashboard.php'); exit; }

$bookings = Booking::forUser(currentUserId());
$upcoming = array_filter($bookings, fn($b) => $b['status'] === 'confirmed' && $b['slot_date'] >= date('Y-m-d'));

$pageTitle = 'My Dashboard';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3>Welcome, <?= e($_SESSION['name']) ?></h3>
<div class="row g-3 my-2">
  <div class="col-md-4">
    <div class="stat-card bg-primary"><h2><?= count($bookings) ?></h2><div>Total Bookings</div></div>
  </div>
  <div class="col-md-4">
    <div class="stat-card bg-success"><h2><?= count($upcoming) ?></h2><div>Upcoming</div></div>
  </div>
  <div class="col-md-4">
    <div class="stat-card bg-info"><h2><?= count(array_filter($bookings, fn($b)=>$b['status']==='cancelled')) ?></h2><div>Cancelled</div></div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
  <h5>Upcoming Bookings</h5>
  <a href="/customer/browse.php" class="btn btn-primary btn-sm">+ New Booking</a>
</div>
<table class="table table-bordered bg-white mt-2">
  <thead><tr><th>Ref</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
  <tbody>
  <?php if (empty($upcoming)): ?>
    <tr><td colspan="5" class="text-center text-muted">No upcoming bookings.</td></tr>
  <?php endif; ?>
  <?php foreach ($upcoming as $b): ?>
    <tr>
      <td><?= e($b['booking_ref']) ?></td>
      <td><?= e($b['service_name']) ?></td>
      <td><?= e($b['slot_date']) ?></td>
      <td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td>
      <td><span class="badge badge-status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
