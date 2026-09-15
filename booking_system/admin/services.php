<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/TimeSlot.php';
requireAdmin();

// Handle create service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    Service::create([
        'category_id' => $_POST['category_id'],
        'provider_name' => trim($_POST['provider_name']),
        'service_name' => trim($_POST['service_name']),
        'description' => trim($_POST['description']),
        'duration_minutes' => (int)$_POST['duration_minutes'],
        'price' => (float)$_POST['price'],
        'location' => trim($_POST['location']),
    ]);
    flash('success', 'Service created successfully.');
    header('Location: /admin/services.php');
    exit;
}

// Handle generate slots
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_slots') {
    $service = Service::find($_POST['service_id']);
    $count = TimeSlot::generateSlots(
        $_POST['service_id'], $service['duration_minutes'],
        $_POST['start_date'], $_POST['end_date']
    );
    flash('success', "$count new time slots generated.");
    header('Location: /admin/services.php');
    exit;
}

// Handle toggle active
if (isset($_GET['toggle'])) {
    $s = Service::find($_GET['toggle']);
    if ($s) {
        Service::update($s['service_id'], [
            'category_id' => $s['category_id'], 'provider_name' => $s['provider_name'],
            'service_name' => $s['service_name'], 'description' => $s['description'],
            'duration_minutes' => $s['duration_minutes'], 'price' => $s['price'],
            'location' => $s['location'], 'is_active' => $s['is_active'] ? 0 : 1,
        ]);
    }
    header('Location: /admin/services.php');
    exit;
}

$categories = Service::allCategories();
$services = Service::allServices(false);

$pageTitle = 'Manage Services';
include __DIR__ . '/../views/layouts/header.php';
?>
<div class="d-flex justify-content-between">
  <h3>Manage Services</h3>
  <div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">+ Add Service</button>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#slotsModal">Generate Slots</button>
  </div>
</div>

<table class="table table-bordered bg-white mt-3">
  <thead class="table-light">
    <tr><th>Service</th><th>Category</th><th>Provider</th><th>Duration</th><th>Price</th><th>Status</th><th>Action</th></tr>
  </thead>
  <tbody>
  <?php foreach ($services as $s): ?>
    <tr>
      <td><?= e($s['service_name']) ?></td>
      <td><?= e($s['category_name']) ?></td>
      <td><?= e($s['provider_name']) ?></td>
      <td><?= (int)$s['duration_minutes'] ?> mins</td>
      <td>RM <?= number_format($s['price'],2) ?></td>
      <td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
      <td><a href="?toggle=<?= $s['service_id'] ?>" class="btn btn-sm btn-outline-secondary">Toggle</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<!-- Create Service Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header"><h5 class="modal-title">Add New Service</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Category</label>
          <select name="category_id" class="form-select" required>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['category_id'] ?>"><?= e($c['category_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Service Name</label>
          <input name="service_name" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Provider Name</label>
          <input name="provider_name" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Description</label>
          <textarea name="description" class="form-control"></textarea></div>
        <div class="row">
          <div class="col mb-2"><label class="form-label">Duration (mins)</label>
            <input type="number" name="duration_minutes" class="form-control" value="30" required></div>
          <div class="col mb-2"><label class="form-label">Price (RM)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="0"></div>
        </div>
        <div class="mb-2"><label class="form-label">Location</label>
          <input name="location" class="form-control"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">Save Service</button></div>
    </form>
  </div>
</div>

<!-- Generate Slots Modal -->
<div class="modal fade" id="slotsModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="generate_slots">
      <div class="modal-header"><h5 class="modal-title">Generate Time Slots</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Service</label>
          <select name="service_id" class="form-select" required>
            <?php foreach ($services as $s): ?>
              <option value="<?= $s['service_id'] ?>"><?= e($s['service_name']) ?> (<?= e($s['provider_name']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col mb-2"><label class="form-label">From Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
          <div class="col mb-2"><label class="form-label">To Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required></div>
        </div>
        <p class="small text-muted">Slots are generated for working days (Sun–Thu), 09:00–17:00, based on the service duration.</p>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">Generate</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
