<?php
require 'includes/config.php';
requireRole('applicant');

$user  = currentUser();
$jobId = (int)($_GET['job'] ?? 0);
if (!$jobId) { header('Location: index.php'); exit; }

$stmt = db()->prepare("SELECT j.*,u.name AS employer_name FROM jobs j JOIN users u ON j.employer_id=u.id WHERE j.id=? LIMIT 1");
$stmt->bind_param('i', $jobId);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
if (!$job) { header('Location: index.php'); exit; }

$chk = db()->prepare("SELECT id FROM applications WHERE job_id=? AND applicant_id=? LIMIT 1");
$chk->bind_param('ii', $jobId, $user['id']);
$chk->execute();
if ($chk->get_result()->fetch_assoc()) {
    flash('info', 'You have already applied for this position.', 'info');
    header('Location: index.php?job=' . $jobId); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover  = trim($_POST['cover_letter'] ?? '');
    $resume = trim($_POST['resume_link']  ?? '');
    $phone  = trim($_POST['phone']        ?? '');
    if (!$cover) {
        $error = 'Cover letter is required.';
    } else {
        $ins = db()->prepare("INSERT INTO applications (job_id,applicant_id,cover_letter,resume_link,phone) VALUES (?,?,?,?,?)");
        $ins->bind_param('iisss', $jobId, $user['id'], $cover, $resume, $phone);
        if ($ins->execute()) {
            flash('success', 'Application submitted successfully! ✓');
            header('Location: my_applications.php'); exit;
        } else {
            $error = 'Submission failed. Please try again.';
        }
    }
}

$pageTitle = 'Apply — ' . $job['title'];
include 'includes/header.php';
?>
<div class="container py-4" style="max-width:740px;">
  <a href="index.php?job=<?= $jobId ?>" class="btn btn-sm btn-outline-secondary mb-3">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
  <div class="wl-card p-4 mb-4 d-flex gap-3 align-items-center">
    <div class="wl-job-logo"><?= strtoupper(substr($job['company'],0,2)) ?></div>
    <div>
      <h5 class="fw-bold mb-0"><?= e($job['title']) ?></h5>
      <small class="text-muted"><?= e($job['company']) ?> · <?= e($job['location']) ?></small>
    </div>
  </div>
  <div class="wl-card p-4">
    <h5 class="fw-bold mb-4">Application Form</h5>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold small text-uppercase text-muted">Cover Letter *</label>
        <textarea name="cover_letter" class="form-control" rows="7"
                  placeholder="Tell them why you're a great fit for this role…" required><?= e($_POST['cover_letter'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small text-uppercase text-muted">Resume Link</label>
        <input type="url" name="resume_link" class="form-control"
               value="<?= e($_POST['resume_link'] ?? '') ?>" placeholder="https://drive.google.com/…">
        <div class="form-text">Google Drive, LinkedIn, or any public link</div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold small text-uppercase text-muted">Phone Number</label>
        <input type="text" name="phone" class="form-control"
               value="<?= e($_POST['phone'] ?? '') ?>" placeholder="09XX-XXX-XXXX">
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <a href="index.php?job=<?= $jobId ?>" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-wl fw-bold px-4">Submit Application</button>
      </div>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
