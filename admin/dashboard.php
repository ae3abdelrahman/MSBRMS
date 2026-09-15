<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
requireAdmin();

$stats = Booking::stats();
$recentBookings = array_slice(Booking::all(), 0, 8);

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3 class="mb-3">Admin Dashboard</h3>
<div class="row g-3">
  <div class="col-md-2"><div class="stat-card bg-primary"><h3><?= $stats['total'] ?></h3>Total Bookings</div></div>
  <div class="col-md-2"><div class="stat-card bg-success"><h3><?= $stats['confirmed'] ?></h3>Confirmed</div></div>
  <div class="col-md-2"><div class="stat-card bg-danger"><h3><?= $stats['cancelled'] ?></h3>Cancelled</div></div>
  <div class="col-md-2"><div class="stat-card bg-secondary"><h3><?= $stats['completed'] ?></h3>Completed</div></div>
  <div class="col-md-2"><div class="stat-card bg-info"><h3><?= $stats['customers'] ?></h3>Customers</div></div>
  <div class="col-md-2"><div class="stat-card bg-warning text-dark"><h3><?= $stats['services'] ?></h3>Active Services</div></div>
</div>

<h5 class="mt-4">Recent Bookings</h5>
<table class="table table-bordered bg-white">
  <thead class="table-light"><tr><th>Ref</th><th>Customer</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach ($recentBookings as $b): ?>
    <tr>
      <td><?= e($b['booking_ref']) ?></td>
      <td><?= e($b['full_name']) ?></td>
      <td><?= e($b['service_name']) ?></td>
      <td><?= e($b['slot_date']) ?></td>
      <td><span class="badge badge-status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
