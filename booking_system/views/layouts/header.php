<?php require_once __DIR__ . '/../../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - MSBRMS' : 'Multi-Service Booking System' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php"><i class="bi bi-calendar-check"></i> MSBRMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <?php if (isLoggedIn() && isAdmin()): ?>
          <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php">Logout (<?= e($_SESSION['name']) ?>)</a></li>
        <?php elseif (isLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="/customer/dashboard.php">My Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/customer/browse.php">Book a Service</a></li>
          <li class="nav-item"><a class="nav-link" href="/customer/bookings.php">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php">Logout (<?= e($_SESSION['name']) ?>)</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4 mb-5">
<?php if ($msg = flash('success')): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
