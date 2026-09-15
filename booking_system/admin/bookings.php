<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'cancel') {
        Booking::cancelBooking($_POST['booking_id'], null, true);
        flash('success', 'Booking cancelled by admin.');
    } elseif ($_POST['action'] === 'complete') {
        Booking::markCompleted($_POST['booking_id']);
        flash('success', 'Booking marked as completed.');
    }
    header('Location: /admin/bookings.php');
    exit;
}

$filter = $_GET['status'] ?? null;
$bookings = Booking::all($filter);

$pageTitle = 'Manage Bookings';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3 class="mb-3">Manage Bookings</h3>
<div class="mb-3">
  <a href="?status=" class="btn btn-sm <?= !$filter?'btn-primary':'btn-outline-primary' ?>">All</a>
  <a href="?status=confirmed" class="btn btn-sm <?= $filter=='confirmed'?'btn-primary':'btn-outline-primary' ?>">Confirmed</a>
  <a href="?status=cancelled" class="btn btn-sm <?= $filter=='cancelled'?'btn-primary':'btn-outline-primary' ?>">Cancelled</a>
  <a href="?status=completed" class="btn btn-sm <?= $filter=='completed'?'btn-primary':'btn-outline-primary' ?>">Completed</a>
</div>
<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Ref</th><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
  </thead>
  <tbody>
  <?php foreach ($bookings as $b): ?>
    <tr>
      <td><?= e($b['booking_ref']) ?></td>
      <td><?= e($b['full_name']) ?><br><small class="text-muted"><?= e($b['email']) ?></small></td>
      <td><?= e($b['service_name']) ?></td>
      <td><?= e($b['slot_date']) ?></td>
      <td><?= e(substr($b['start_time'],0,5)) ?>-<?= e(substr($b['end_time'],0,5)) ?></td>
      <td><span class="badge badge-status-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
      <td>
        <?php if ($b['status'] === 'confirmed'): ?>
        <form method="POST" class="d-inline">
          <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
          <input type="hidden" name="action" value="complete">
          <button class="btn btn-sm btn-outline-success">Complete</button>
        </form>
        <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
          <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
          <input type="hidden" name="action" value="cancel">
          <button class="btn btn-sm btn-outline-danger">Cancel</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
