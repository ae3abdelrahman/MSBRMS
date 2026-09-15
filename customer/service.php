<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/TimeSlot.php';
requireLogin();

$serviceId = $_GET['id'] ?? 0;
$service = Service::find($serviceId);
if (!$service) { header('Location: /customer/browse.php'); exit; }

$slots = TimeSlot::availableForService($serviceId);
// group slots by date
$grouped = [];
foreach ($slots as $slot) {
    $grouped[$slot['slot_date']][] = $slot;
}

$pageTitle = $service['service_name'];
include __DIR__ . '/../views/layouts/header.php';
?>
<div class="card p-4 mb-4">
  <span class="badge bg-secondary mb-2" style="width:fit-content"><?= e($service['category_name']) ?></span>
  <h3><?= e($service['service_name']) ?></h3>
  <p class="text-muted"><?= e($service['description']) ?></p>
  <div class="row">
    <div class="col-md-3"><strong>Provider:</strong> <?= e($service['provider_name']) ?></div>
    <div class="col-md-3"><strong>Location:</strong> <?= e($service['location']) ?></div>
    <div class="col-md-3"><strong>Duration:</strong> <?= (int)$service['duration_minutes'] ?> mins</div>
    <div class="col-md-3"><strong>Price:</strong> RM <?= number_format($service['price'],2) ?></div>
  </div>
</div>

<h5>Select an Available Time Slot</h5>
<?php if (empty($grouped)): ?>
  <div class="alert alert-warning">No available slots for this service at the moment. Please check back later.</div>
<?php endif; ?>

<?php foreach ($grouped as $date => $daySlots): ?>
  <div class="card mb-3">
    <div class="card-header fw-bold"><?= date('l, d F Y', strtotime($date)) ?></div>
    <div class="card-body">
      <?php foreach ($daySlots as $slot): ?>
        <form method="POST" action="/customer/book.php" class="d-inline">
          <input type="hidden" name="slot_id" value="<?= $slot['slot_id'] ?>">
          <button type="submit" class="btn btn-outline-primary slot-btn"
                  onclick="return confirm('Confirm booking for <?= e(substr($slot['start_time'],0,5)) ?> on <?= e($date) ?>?')">
            <?= e(substr($slot['start_time'],0,5)) ?> - <?= e(substr($slot['end_time'],0,5)) ?>
          </button>
        </form>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
