<?php
require 'includes/config.php';
requireRole('applicant');

$user = currentUser();
$stmt = db()->prepare(
    "SELECT a.*,j.title AS job_title,j.company,j.location,j.type
     FROM applications a JOIN jobs j ON a.job_id=j.id
     WHERE a.applicant_id=? ORDER BY a.applied_at DESC"
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusMap = [
    'Applied'      => ['class'=>'badge-applied',      'icon'=>'bi-send'],
    'Under Review' => ['class'=>'badge-under-review',  'icon'=>'bi-eye'],
    'Interview'    => ['class'=>'badge-interview',     'icon'=>'bi-camera-video'],
    'Offered'      => ['class'=>'badge-offered',       'icon'=>'bi-trophy'],
    'Rejected'     => ['class'=>'badge-rejected',      'icon'=>'bi-x-circle'],
];

// Count per status
$counts = [];
foreach ($apps as $a) {
    $counts[$a['status']] = ($counts[$a['status']] ?? 0) + 1;
}

$pageTitle = 'My Applications';
include 'includes/header.php';
?>
<div class="container py-4">
  <div class="row g-4">

    <!-- SIDEBAR STATS -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="wl-card p-3 mb-3">
        <h6 class="fw-bold text-uppercase text-muted small mb-3">Application Summary</h6>
        <?php foreach ($statusMap as $status => $meta): ?>
          <?php $cnt = $counts[$status] ?? 0; ?>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge <?= $meta['class'] ?>"><?= $status ?></span>
            <span class="fw-bold"><?= $cnt ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between">
          <span class="text-muted small">Total</span>
          <span class="fw-bold"><?= count($apps) ?></span>
        </div>
      </div>
    </div>

    <!-- MAIN -->
    <div class="col-lg-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0">My Applications</h5>
        <a href="index.php" class="btn btn-sm btn-wl"><i class="bi bi-search me-1"></i>Browse Jobs</a>
      </div>

      <?php if (empty($apps)): ?>
        <div class="wl-card p-5 text-center text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
          You haven't applied to anything yet.<br>
          <a href="index.php" class="btn btn-wl btn-sm mt-3">Browse Jobs</a>
        </div>
      <?php endif; ?>

      <?php foreach ($apps as $app):
        $meta = $statusMap[$app['status']] ?? ['class'=>'badge-applied','icon'=>'bi-send'];
      ?>
      <div class="wl-card p-4 mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
          <div>
            <h6 class="fw-bold mb-0"><?= e($app['job_title']) ?></h6>
            <small class="text-muted">
              <i class="bi bi-building me-1"></i><?= e($app['company']) ?>
              &nbsp;·&nbsp;<i class="bi bi-geo-alt me-1"></i><?= e($app['location']) ?>
            </small>
            <div class="mt-2">
              <span class="badge <?= $meta['class'] ?>">
                <i class="<?= $meta['icon'] ?> me-1"></i><?= $app['status'] ?>
              </span>
            </div>
            <small class="text-muted d-block mt-1">
              <i class="bi bi-calendar3 me-1"></i>Applied <?= date('M j, Y', strtotime($app['applied_at'])) ?>
            </small>
          </div>
          <div class="text-end">
            <?php if ($app['status'] === 'Interview'): ?>
              <div class="text-purple small fw-bold"><i class="bi bi-star-fill me-1"></i>You've been shortlisted!</div>
            <?php elseif ($app['status'] === 'Offered'): ?>
              <div class="text-success small fw-bold"><i class="bi bi-trophy-fill me-1"></i>You received an offer!</div>
            <?php elseif ($app['status'] === 'Rejected'): ?>
              <div class="text-danger small">Keep applying — the right role is out there.</div>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($app['cover_letter']): ?>
        <div class="mt-3 p-3 rounded-3 bg-light">
          <p class="small text-uppercase fw-bold text-muted mb-1">Cover Letter</p>
          <p class="text-muted small mb-0" style="line-height:1.7;">
            <?= e(mb_strimwidth($app['cover_letter'], 0, 200, '…')) ?>
          </p>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
