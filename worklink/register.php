<?php
require 'includes/config.php';
 
if (isLoggedIn()) {
    header('Location: ' . (currentUser()['role'] === 'employer' ? 'employer_dashboard.php' : 'index.php'));
    exit;
}
 
$error = '';
 
// Read role from POST first (form submission), then GET (tab click), then default
$role = $_POST['role'] ?? $_GET['role'] ?? 'applicant';
if (!in_array($role, ['applicant', 'employer'])) $role = 'applicant';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $company  = trim($_POST['company']  ?? '');
    $position = trim($_POST['position'] ?? '');
 
    if (!$name || !$email || !$password) {
        $error = 'Name, email, and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'employer' && !$company) {
        $error = 'Company name is required for employer accounts.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db   = db();
 
        if ($role === 'applicant') {
            $stmt = $db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'applicant')");
            $stmt->bind_param('sss', $name, $email, $hash);
        } else {
            $stmt = $db->prepare("INSERT INTO users (name,email,password,role,company,position) VALUES (?,?,?,'employer',?,?)");
            $stmt->bind_param('sssss', $name, $email, $hash, $company, $position);
        }
 
        if (!$stmt->execute()) {
            $error = ($db->errno === 1062) ? 'Email is already registered.' : 'Registration failed. Please try again.';
        } else {
            $newId = $db->insert_id;
            $row   = $db->query("SELECT * FROM users WHERE id = $newId")->fetch_assoc();
            $_SESSION['user'] = [
                'id'       => $row['id'],
                'name'     => $row['name'],
                'email'    => $row['email'],
                'role'     => $row['role'],
                'company'  => $row['company']  ?? '',
                'headline' => $row['headline'] ?? '',
                'location' => $row['location'] ?? '',
                'skills'   => $row['skills']   ?? '',
                'about'    => $row['about']    ?? '',
            ];
            header('Location: ' . ($role === 'employer' ? 'employer_dashboard.php' : 'index.php'));
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
  <title>Register — WorkLink</title>
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
        <h1 class="text-white fw-bold" style="font-size:2.4rem;line-height:1.2;">Join WorkLink today.</h1>
        <p class="text-white-50 mt-3 mb-4" style="line-height:1.8;">
          Create your free account and start applying or hiring in minutes.
        </p>
        <div class="p-3 rounded-3" style="background:rgba(255,255,255,.12);">
          <?php if ($role === 'employer'): ?>
            <p class="text-white fst-italic mb-1" style="font-size:.85rem;">"Posted my first job and got 12 applicants within 3 days!"</p>
            <small class="text-white-50">— Rico R., HR Manager, TechCorp PH</small>
          <?php else: ?>
            <p class="text-white fst-italic mb-1" style="font-size:.85rem;">"Got my internship at Globe through WorkLink in just 2 weeks!"</p>
            <small class="text-white-50">— Maria S., BS Information Systems</small>
          <?php endif; ?>
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
 
        <h2 class="fw-bold mb-1">Create account</h2>
        <p class="text-muted mb-4">Join WorkLink — it's free</p>
 
        <!-- Role toggle — uses GET so it reloads the page cleanly -->
        <div class="d-flex bg-light rounded-3 p-1 mb-4">
          <a href="register.php?role=applicant"
             class="btn btn-sm flex-fill fw-bold <?= $role === 'applicant' ? 'btn-wl' : 'btn-light text-muted' ?>">
            <i class="bi bi-person me-1"></i>Job Seeker
          </a>
          <a href="register.php?role=employer"
             class="btn btn-sm flex-fill fw-bold ms-1 <?= $role === 'employer' ? 'btn-wl' : 'btn-light text-muted' ?>">
            <i class="bi bi-building me-1"></i>Employer
          </a>
        </div>
 
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <?= e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
 
        <!-- Role is passed both in GET (for tab state) and hidden input (for form submission) -->
        <form method="POST" action="register.php?role=<?= e($role) ?>">
          <input type="hidden" name="role" value="<?= e($role) ?>">
 
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold small text-uppercase text-muted">Full Name *</label>
              <input type="text" name="name" class="form-control"
                     value="<?= e($_POST['name'] ?? '') ?>" placeholder="Juan dela Cruz" required>
            </div>
 
            <?php if ($role === 'employer'): ?>
            <div class="col-sm-7">
              <label class="form-label fw-semibold small text-uppercase text-muted">Company Name *</label>
              <input type="text" name="company" class="form-control"
                     value="<?= e($_POST['company'] ?? '') ?>" placeholder="Your Company Inc." required>
            </div>
            <div class="col-sm-5">
              <label class="form-label fw-semibold small text-uppercase text-muted">Your Position</label>
              <input type="text" name="position" class="form-control"
                     value="<?= e($_POST['position'] ?? '') ?>" placeholder="HR Manager">
            </div>
            <?php endif; ?>
 
            <div class="col-12">
              <label class="form-label fw-semibold small text-uppercase text-muted">Email *</label>
              <input type="email" name="email" class="form-control"
                     value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@email.com" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Password *</label>
              <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Confirm Password *</label>
              <input type="password" name="confirm" class="form-control" placeholder="••••••••" required>
            </div>
          </div>
 
          <button class="btn btn-wl btn-lg w-100 fw-bold mt-4">
            Create <?= $role === 'employer' ? 'Employer' : 'Job Seeker' ?> Account
          </button>
        </form>
 
        <p class="text-center mt-3 text-muted small">
          Have an account? <a href="login.php" class="fw-bold text-decoration-none">Sign in →</a>
        </p>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>