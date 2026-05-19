<?php
// includes/header.php
// Usage: include 'includes/header.php';
// Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'WorkLink';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?> — WorkLink</title>

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark wl-navbar sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="index.php">
      <span class="wl-logo-box">W</span>
      WorkLink
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <?php if ($user): ?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <?php if ($user['role'] === 'applicant'): ?>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
                 href="index.php"><i class="bi bi-house-door me-1"></i>Feed</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'my_applications.php' ? 'active' : '' ?>"
                 href="my_applications.php"><i class="bi bi-file-earmark-text me-1"></i>My Applications</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>"
                 href="profile.php"><i class="bi bi-person me-1"></i>Profile</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'employer_dashboard.php' ? 'active' : '' ?>"
                 href="employer_dashboard.php"><i class="bi bi-building me-1"></i>Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'employer_applicants.php' ? 'active' : '' ?>"
                 href="employer_applicants.php"><i class="bi bi-people me-1"></i>Applicants</a>
            </li>
          <?php endif; ?>
        </ul>

        <div class="d-flex align-items-center gap-3">
          <div class="wl-avatar-sm"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
          <span class="text-white fw-semibold small"><?= e(explode(' ', $user['name'])[0]) ?></span>
          <a href="logout.php" class="btn btn-outline-light btn-sm">Sign out</a>
        </div>

      <?php else: ?>
        <ul class="navbar-nav me-auto"></ul>
        <div class="d-flex gap-2">
          <a href="login.php"    class="btn btn-outline-light btn-sm">Sign In</a>
          <a href="register.php" class="btn btn-light btn-sm text-primary fw-bold">Register</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- FLASH MESSAGES (global) -->
<?php foreach (['success','error','info'] as $fkey):
  $fl = getFlash($fkey);
  if ($fl):
    $alertClass = $fl['type'] === 'error' ? 'alert-danger' : 'alert-' . $fl['type'];
?>
<div class="container mt-3">
  <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
    <?= e($fl['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; endforeach; ?>
