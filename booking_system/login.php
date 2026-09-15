<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/models/User.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '/admin/dashboard.php' : '/customer/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = User::attemptLogin($email, $password);
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];
        header('Location: ' . ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/customer/dashboard.php'));
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
include __DIR__ . '/views/layouts/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="card-title mb-3 text-center">Login</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3 small">No account? <a href="/register.php">Register here</a></p>
        <p class="text-center text-muted small">Demo admin: admin@msbrms.local / Admin@123</p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/views/layouts/footer.php'; ?>
