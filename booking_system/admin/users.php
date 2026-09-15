<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';
requireAdmin();

if (isset($_GET['suspend'])) {
    User::setStatus($_GET['suspend'], 'suspended');
    header('Location: /admin/users.php'); exit;
}
if (isset($_GET['activate'])) {
    User::setStatus($_GET['activate'], 'active');
    header('Location: /admin/users.php'); exit;
}

$users = User::all('customer');
$pageTitle = 'Manage Users';
include __DIR__ . '/../views/layouts/header.php';
?>
<h3 class="mb-3">Manage Customers</h3>
<table class="table table-bordered bg-white">
  <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= e($u['full_name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= e($u['phone']) ?></td>
      <td><?= $u['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Suspended</span>' ?></td>
      <td><?= e($u['created_at']) ?></td>
      <td>
        <?php if ($u['status']==='active'): ?>
          <a href="?suspend=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-danger">Suspend</a>
        <?php else: ?>
          <a href="?activate=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-success">Activate</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
