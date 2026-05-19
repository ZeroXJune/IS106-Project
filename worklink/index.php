<?php
require 'includes/config.php';
requireRole('applicant');

$user   = currentUser();
$search = trim($_GET['search'] ?? '');
$type   = $_GET['type']   ?? 'All';
$jobId  = (int)($_GET['job'] ?? 0);

// ── FETCH JOBS ────────────────────────────────────────────────
$sql    = "SELECT j.*, u.name AS employer_name FROM jobs j JOIN users u ON j.employer_id=u.id WHERE 1=1";
$params = []; $types = '';
if ($search) {
    $like    = "%$search%";
    $sql    .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.tags LIKE ?)";
    $params  = [$like, $like, $like];
    $types  .= 'sss';
}
if ($type && $type !== 'All') {
    $sql    .= " AND j.type = ?";
    $params[] = $type; $types .= 's';
}
$sql .= " ORDER BY j.created_at DESC";
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── JOBS USER HAS APPLIED TO ──────────────────────────────────
$appliedStmt = db()->prepare("SELECT job_id FROM applications WHERE applicant_id=?");
$appliedStmt->bind_param('i', $user['id']);
$appliedStmt->execute();
$appliedIds = array_column($appliedStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'job_id');

// ── SELECTED JOB DETAIL ───────────────────────────────────────
$selectedJob = null;
if ($jobId) {
    $js = db()->prepare("SELECT j.*,u.name AS employer_name FROM jobs j JOIN users u ON j.employer_id=u.id WHERE j.id=? LIMIT 1");
    $js->bind_param('i', $jobId);
    $js->execute();
    $selectedJob = $js->get_result()->fetch_assoc();
}

// ── APP COUNT (sidebar) ───────────────────────────────────────
$cntStmt = db()->prepare("SELECT COUNT(*) AS cnt FROM applications WHERE applicant_id=?");
$cntStmt->bind_param('i', $user['id']);
$cntStmt->execute();
$appCount = $cntStmt->get_result()->fetch_assoc()['cnt'];

$pageTitle = 'Job Feed';
include 'includes/header.php';

function initials(string $name): string {
    $words = explode(' ', trim($name));
    return strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice($words, 0, 2))));
}
function tagBadge(string $t): string {
    return '<span class="wl-tag me-1">' . htmlspecialchars(trim($t), ENT_QUOTES) . '</span>';
}
function typeBadge(string $t): string {
    $cls = $t === 'Internship' ? 'text-bg-warning' : 'text-bg-info';
    return '<span class="badge ' . $cls . ' fw-semibold">' . htmlspecialchars($t, ENT_QUOTES) . '</span>';
}
function statusBadge(string $s): string {
    $map = ['Applied'=>'applied','Under Review'=>'under-review','Interview'=>'interview','Offered'=>'offered','Rejected'=>'rejected'];
    $cls = $map[$s] ?? 'applied';
    return '<span class="badge badge-'.$cls.'">'.$s.'</span>';
}
?>

<div class="container py-4">
  <div class="row g-4">

    <!-- SIDEBAR -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="wl-card overflow-hidden mb-3">
        <div class="wl-profile-banner"></div>
        <div class="px-3 pb-3" style="margin-top:-28px;">
          <div class="wl-avatar-lg mb-2"><?= initials($user['name']) ?></div>
          <h6 class="fw-bold mb-0"><?= e($user['name']) ?></h6>
          <small class="text-muted"><?= e($user['headline'] ?: 'Job Seeker') ?></small><br>
          <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($user['location'] ?: 'Philippines') ?></small>
          <hr class="my-2">
          <div class="text-muted small text-uppercase fw-bold">Applications</div>
          <div class="wl-stat-num"><?= $appCount ?></div>
        </div>
      </div>
    </div>

    <!-- MAIN FEED -->
    <div class="col-lg-9">

      <?php if ($selectedJob): ?>
        <!-- JOB DETAIL VIEW -->
        <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">
          <i class="bi bi-arrow-left me-1"></i>Back to Jobs
        </a>
        <div class="wl-card p-4 mb-3">
          <div class="d-flex gap-3 align-items-start mb-3 pb-3 border-bottom">
            <div class="wl-job-logo"><?= initials($selectedJob['company']) ?></div>
            <div class="flex-grow-1">
              <h4 class="fw-bold mb-1"><?= e($selectedJob['title']) ?></h4>
              <p class="text-muted mb-1"><?= e($selectedJob['company']) ?></p>
              <div class="d-flex flex-wrap gap-3 text-muted small">
                <span><i class="bi bi-geo-alt me-1"></i><?= e($selectedJob['location']) ?></span>
                <span><i class="bi bi-briefcase me-1"></i><?= e($selectedJob['type']) ?></span>
                <span><i class="bi bi-currency-exchange me-1"></i><?= e($selectedJob['salary'] ?? '—') ?></span>
                <span><i class="bi bi-calendar me-1"></i>Deadline: <?= e($selectedJob['deadline'] ?? '—') ?></span>
              </div>
            </div>
            <?php if (in_array($selectedJob['id'], $appliedIds)): ?>
              <span class="btn btn-sm btn-outline-success disabled"><i class="bi bi-check-lg me-1"></i>Applied</span>
            <?php else: ?>
              <a href="apply.php?job=<?= $selectedJob['id'] ?>" class="btn btn-wl btn-sm fw-bold">Apply Now</a>
            <?php endif; ?>
          </div>
          <h6 class="fw-bold">About this role</h6>
          <p class="text-muted" style="line-height:1.8;"><?= nl2br(e($selectedJob['description'])) ?></p>
          <?php if ($selectedJob['tags']): ?>
          <h6 class="fw-bold mt-3">Skills Required</h6>
          <div><?php foreach (explode(',', $selectedJob['tags']) as $t) echo tagBadge($t); ?></div>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- SEARCH + FILTERS -->
        <form method="GET" class="wl-card p-3 mb-4">
          <div class="row g-2 align-items-center">
            <div class="col-md-7">
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0"
                       value="<?= e($search) ?>" placeholder="Search jobs, companies, skills…">
              </div>
            </div>
            <div class="col-md-4">
              <select name="type" class="form-select">
                <?php foreach (['All','Full-time','Part-time','Internship','Contract'] as $t): ?>
                  <option <?= $type===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-1">
              <button class="btn btn-wl w-100"><i class="bi bi-funnel"></i></button>
            </div>
          </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0"><?= count($jobs) ?> job<?= count($jobs)!==1?'s':'' ?> found</h6>
          <?php if ($search || $type!=='All'): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">Clear filters</a>
          <?php endif; ?>
        </div>

        <?php if (empty($jobs)): ?>
          <div class="wl-card p-5 text-center text-muted">
            <i class="bi bi-search fs-1 d-block mb-2"></i>No jobs match your search.
          </div>
        <?php endif; ?>

        <?php foreach ($jobs as $job): ?>
        <div class="wl-card p-4 mb-3">
          <div class="d-flex gap-3">
            <div class="wl-job-logo"><?= initials($job['company']) ?></div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                  <h6 class="fw-bold mb-0">
                    <a href="index.php?job=<?= $job['id'] ?>" class="text-decoration-none text-dark">
                      <?= e($job['title']) ?>
                    </a>
                  </h6>
                  <small class="text-muted"><?= e($job['company']) ?> · <?= e($job['location']) ?></small>
                </div>
                <?= typeBadge($job['type']) ?>
              </div>
              <p class="text-muted small mt-2 mb-2" style="line-height:1.6;">
                <?= e(mb_strimwidth($job['description'], 0, 130, '…')) ?>
              </p>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if ($job['tags']): foreach (explode(',', $job['tags']) as $t) echo tagBadge($t); endif; ?>
                <span class="ms-auto fw-bold text-primary small"><?= e($job['salary'] ?? '') ?></span>
              </div>
              <div class="d-flex gap-2 mt-3">
                <a href="index.php?job=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                <?php if (in_array($job['id'], $appliedIds)): ?>
                  <span class="btn btn-sm btn-outline-success disabled"><i class="bi bi-check-lg me-1"></i>Applied</span>
                <?php else: ?>
                  <a href="apply.php?job=<?= $job['id'] ?>" class="btn btn-sm btn-wl">Apply Now</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
