<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
requireLogin();

$bookings = Booking::forUser(currentUserId());
$pageTitle = 'My Bookings';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3 class="mb-3">My Bookings</h3>
<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Ref</th><th>Service</th><th>Category</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
  </thead>
  <tbody>
  <?php foreach ($bookings as $b): ?>
    <tr>
      <td><?= e($b['booking_ref']) ?></td>
      <td><?= e($b['service_name']) ?><br><small class="text-muted"><?= e($b['provider_name']) ?></small></td>
      <td><?= e($b['category_name']) ?></td>
      <td><?= e($b['slot_date']) ?></td>
      <td><?= e(substr($b['start_time'],0,5)) ?> - <?= e(substr($b['end_time'],0,5)) ?></td>
      <td><span class="badge badge-status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
      <td>
        <?php if ($b['status'] === 'confirmed'): ?>
        <form method="POST" action="/customer/cancel.php" onsubmit="return confirm('Cancel this booking?')">
          <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Cancel</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($bookings)): ?>
    <tr><td colspan="7" class="text-center text-muted">You have no bookings yet.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
