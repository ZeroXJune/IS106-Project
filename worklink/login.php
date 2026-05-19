<?php
require 'includes/config.php';

if (isLoggedIn()) {
    header('Location: ' . (currentUser()['role'] === 'employer' ? 'employer_dashboard.php' : 'index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Invalid email or password.';
        } else {
            $_SESSION['user'] = [
                'id'       => $user['id'],
                'name'     => $user['name'],
                'email'    => $user['email'],
                'role'     => $user['role'],
                'company'  => $user['company']  ?? '',
                'headline' => $user['headline'] ?? '',
                'location' => $user['location'] ?? '',
                'skills'   => $user['skills']   ?? '',
                'about'    => $user['about']    ?? '',
            ];
            header('Location: ' . ($user['role'] === 'employer' ? 'employer_dashboard.php' : 'index.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — WorkLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid p-0" style="min-height:100vh;">
  <div class="row g-0" style="min-height:100vh;">

    <!-- HERO -->
    <div class="col-lg-5 wl-hero d-none d-lg-flex">
      <div>
        <div class="wl-logo-box mb-4" style="width:52px;height:52px;font-size:22px;">W</div>
        <h1 class="text-white fw-bold" style="font-size:2.4rem;line-height:1.2;">Find your next opportunity.</h1>
        <p class="text-white-50 mt-3 mb-4" style="line-height:1.8;">
          WorkLink connects Filipino students and professionals with companies actively hiring.
        </p>
        <ul class="list-unstyled mb-4">
          <?php foreach (['Browse verified job listings','Track every application in one place','Connect with top Philippine employers'] as $f): ?>
          <li class="d-flex align-items-center gap-2 mb-2 text-white" style="font-size:.9rem;">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-white bg-opacity-25"
                  style="width:22px;height:22px;font-size:11px;flex-shrink:0;">✓</span>
            <?= e($f) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="p-3 rounded-3" style="background:rgba(255,255,255,.12);">
          <p class="text-white fst-italic mb-1" style="font-size:.85rem;">"Got my internship at Globe through WorkLink in just 2 weeks!"</p>
          <small class="text-white-50">— Maria S., BS Information Systems</small>
        </div>
      </div>
    </div>

    <!-- FORM -->
    <div class="col-lg-7 d-flex align-items-center justify-content-center bg-white p-4 p-lg-5">
      <div class="wl-auth-card w-100">
        <div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
          <div class="wl-logo-box">W</div>
          <span class="fw-bold fs-5" style="color:var(--wl-navy)">WorkLink</span>
        </div>
        <h2 class="fw-bold mb-1">Welcome back</h2>
        <p class="text-muted mb-4">Sign in to continue to WorkLink</p>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?= e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-uppercase text-muted">Email</label>
            <input type="email" name="email" class="form-control form-control-lg"
                   value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@email.com" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold small text-uppercase text-muted">Password</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
          </div>
          <button class="btn btn-wl btn-lg w-100 fw-bold">Sign In</button>
        </form>

        <p class="text-center mt-3 text-muted small">
          No account? <a href="register.php" class="fw-bold text-decoration-none">Register →</a>
        </p>

        <div class="wl-demo-box p-3 mt-3">
          <p class="fw-bold small mb-1 text-uppercase" style="color:var(--wl-blue);letter-spacing:.05em;">Demo Credentials</p>
          <p class="mb-1 text-muted small"><i class="bi bi-person me-1"></i>Applicant: <strong>alber@email.com</strong> / tpc123</p>
          <p class="mb-0 text-muted small"><i class="bi bi-building me-1"></i>Employer: <strong>zeroxjune@gmail.com</strong> / pass123</p>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
