<?php
require 'includes/config.php';
requireRole('employer');
$user = currentUser();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job'])) {
    $delId = (int)$_POST['delete_job'];
    $chk   = db()->prepare("SELECT id FROM jobs WHERE id=? AND employer_id=? LIMIT 1");
    $chk->bind_param('ii', $delId, $user['id']);
    $chk->execute();
    if ($chk->get_result()->fetch_assoc()) {
        $del = db()->prepare("DELETE FROM jobs WHERE id=?");
        $del->bind_param('i', $delId);
        $del->execute();
        flash('success', 'Job removed successfully.');
    }
    header('Location: employer_dashboard.php'); exit;
}

// Handle post new job
$postError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_job'])) {
    $title  = trim($_POST['title']       ?? '');
    $loc    = trim($_POST['location']    ?? '');
    $type   = trim($_POST['type']        ?? 'Full-time');
    $salary = trim($_POST['salary']      ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $tags   = trim($_POST['tags']        ?? '');
    $dl     = trim($_POST['deadline']    ?? '') ?: null;

    if (!$title || !$loc || !$desc) {
        $postError = 'Title, location, and description are required.';
    } else {
        $ins = db()->prepare(
            "INSERT INTO jobs (employer_id,title,company,location,type,salary,description,tags,deadline)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $ins->bind_param('issssssss',
            $user['id'], $title, $user['company'], $loc, $type, $salary, $desc, $tags, $dl
        );
        $ins->execute();
        flash('success', 'Job posted successfully! ✓');
        header('Location: employer_dashboard.php'); exit;
    }
}

// Fetch my jobs with app counts
$jobs = db()->query(
    "SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS app_count
     FROM jobs j WHERE j.employer_id={$user['id']} ORDER BY j.created_at DESC"
)->fetch_all(MYSQLI_ASSOC);

$totalApps = array_sum(array_column($jobs, 'app_count'));

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold mb-0">Job Postings</h4>
      <small class="text-muted"><?= count($jobs) ?> active listing<?= count($jobs)!==1?'s':'' ?></small>
    </div>
    <button class="btn btn-wl fw-bold" data-bs-toggle="modal" data-bs-target="#postModal">
      <i class="bi bi-plus-lg me-1"></i>Post a Job
    </button>
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-4">
    <?php foreach ([
      ['Jobs Posted',       count($jobs),  'bi-briefcase',       'text-primary'],
      ['Total Applications',$totalApps,    'bi-people',          'text-success'],
      ['Active Listings',   count($jobs),  'bi-check-circle',    'text-info'],
    ] as [$lbl,$val,$icon,$col]): ?>
    <div class="col-sm-4">
      <div class="wl-stat-card">
        <div class="stat-icon <?= $col ?>"><i class="<?= $icon ?>"></i></div>
        <div class="stat-val mt-1"><?= $val ?></div>
        <div class="stat-lbl"><?= $lbl ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($postError): ?>
    <div class="alert alert-danger"><?= e($postError) ?></div>
  <?php endif; ?>

  <?php if (empty($jobs)): ?>
    <div class="wl-card p-5 text-center text-muted">
      <i class="bi bi-megaphone fs-1 d-block mb-2"></i>
      No jobs posted yet. Create your first listing!
    </div>
  <?php endif; ?>

  <?php foreach ($jobs as $job): ?>
  <div class="wl-card p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
          <h6 class="fw-bold mb-0"><?= e($job['title']) ?></h6>
          <span class="badge <?= $job['type']==='Internship'?'text-bg-warning':'text-bg-info' ?>"><?= e($job['type']) ?></span>
        </div>
        <small class="text-muted">
          <i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?>
          &nbsp;·&nbsp;<?= e($job['salary'] ?? '—') ?>
        </small>
        <?php if ($job['tags']): ?>
        <div class="mt-2">
          <?php foreach (explode(',', $job['tags']) as $t): ?>
            <span class="wl-tag me-1"><?= e(trim($t)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <small class="text-muted d-block mt-2">
          Posted <?= date('M j, Y', strtotime($job['created_at'])) ?>
          <?= $job['deadline'] ? ' · Deadline ' . date('M j, Y', strtotime($job['deadline'])) : '' ?>
        </small>
      </div>

      <div class="text-end">
        <div class="fw-bold fs-3" style="color:var(--wl-navy);"><?= $job['app_count'] ?></div>
        <small class="text-muted">applicant<?= $job['app_count']!==1?'s':'' ?></small>
        <div class="d-flex gap-2 mt-2 justify-content-end">
          <a href="employer_applicants.php?job=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-people me-1"></i>View Applicants
          </a>
          <form method="POST" onsubmit="return confirm('Remove this job and all its applications?');" class="d-inline">
            <input type="hidden" name="delete_job" value="<?= $job['id'] ?>">
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- POST JOB MODAL -->
<div class="modal fade" id="postModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Post a New Job</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="post_job" value="1">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold small text-uppercase text-muted">Job Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Frontend Developer" required>
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold small text-uppercase text-muted">Location *</label>
              <input type="text" name="location" class="form-control" placeholder="e.g. Makati or Remote" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Job Type</label>
              <select name="type" class="form-select">
                <?php foreach (['Full-time','Part-time','Internship','Contract'] as $t): ?>
                  <option><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Salary Range</label>
              <input type="text" name="salary" class="form-control" placeholder="e.g. ₱30,000–₱40,000">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Application Deadline</label>
              <input type="date" name="deadline" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small text-uppercase text-muted">Skills (comma-separated)</label>
              <input type="text" name="tags" class="form-control" placeholder="PHP, SQL, Bootstrap">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small text-uppercase text-muted">Job Description *</label>
              <textarea name="description" class="form-control" rows="5"
                        placeholder="Describe the role, responsibilities, and requirements…" required></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-wl fw-bold px-4">Post Job</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
