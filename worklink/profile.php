<?php
require 'includes/config.php';
requireRole('applicant');
$user = currentUser();

$stmt = db()->prepare(
    "SELECT a.*,j.title AS job_title,j.company FROM applications a JOIN jobs j ON a.job_id=j.id
     WHERE a.applicant_id=? ORDER BY a.applied_at DESC"
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$skills = array_filter(explode(',', $user['skills'] ?? ''));

$pageTitle = 'Profile';
include 'includes/header.php';
?>
<div class="container py-4" style="max-width:800px;">
  <div class="wl-card overflow-hidden">
    <div class="wl-profile-banner"></div>
    <div class="px-4 pb-4" style="margin-top:-36px;">
      <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
        <div class="wl-avatar-lg"><?= strtoupper(substr($user['name'],0,2)) ?></div>
      </div>
      <h4 class="fw-bold mt-2 mb-0"><?= e($user['name']) ?></h4>
      <p class="text-muted mb-1"><?= e($user['headline'] ?: 'Job Seeker') ?></p>
      <p class="text-muted small mb-3">
        <i class="bi bi-geo-alt me-1"></i><?= e($user['location'] ?: 'Philippines') ?>
        &nbsp;·&nbsp;<i class="bi bi-envelope me-1"></i><?= e($user['email']) ?>
      </p>

      <?php if ($user['about']): ?>
      <div class="border-top pt-3 mb-3">
        <h6 class="fw-bold">About</h6>
        <p class="text-muted" style="line-height:1.8;"><?= nl2br(e($user['about'])) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!empty($skills)): ?>
      <div class="border-top pt-3 mb-3">
        <h6 class="fw-bold">Skills</h6>
        <?php foreach ($skills as $s): ?>
          <span class="wl-tag me-1 mb-1"><?= e(trim($s)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="border-top pt-3">
        <h6 class="fw-bold mb-3">Application History (<?= count($apps) ?>)</h6>
        <?php if (empty($apps)): ?>
          <p class="text-muted small">No applications yet.</p>
        <?php endif; ?>
        <?php foreach ($apps as $app):
          $map = ['Applied'=>'badge-applied','Under Review'=>'badge-under-review',
                  'Interview'=>'badge-interview','Offered'=>'badge-offered','Rejected'=>'badge-rejected'];
          $cls = $map[$app['status']] ?? 'badge-applied';
        ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div>
            <p class="fw-semibold mb-0 small"><?= e($app['job_title']) ?></p>
            <small class="text-muted"><?= e($app['company']) ?> · <?= date('M j, Y', strtotime($app['applied_at'])) ?></small>
          </div>
          <span class="badge <?= $cls ?>"><?= $app['status'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
