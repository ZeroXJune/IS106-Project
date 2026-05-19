<?php
require 'includes/config.php';
requireRole('employer');
$user  = currentUser();
$jobId = (int)($_GET['job'] ?? 0);

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['status'])) {
    $appId  = (int)$_POST['app_id'];
    $status = $_POST['status'];
    $valid  = ['Applied','Under Review','Interview','Offered','Rejected'];
    if (in_array($status, $valid)) {
        // Verify the app belongs to one of this employer's jobs
        $chk = db()->prepare(
            "SELECT a.id FROM applications a JOIN jobs j ON a.job_id=j.id
             WHERE a.id=? AND j.employer_id=? LIMIT 1"
        );
        $chk->bind_param('ii', $appId, $user['id']);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            $upd = db()->prepare("UPDATE applications SET status=? WHERE id=?");
            $upd->bind_param('si', $status, $appId);
            $upd->execute();
            flash('success', 'Status updated to "' . $status . '".');
        }
    }
    header('Location: employer_applicants.php?job=' . $jobId); exit;
}

// Get employer's jobs
$myJobs = db()->query(
    "SELECT j.id,j.title,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS cnt
     FROM jobs j WHERE j.employer_id={$user['id']} ORDER BY j.created_at DESC"
)->fetch_all(MYSQLI_ASSOC);

// Default to first job if none selected
if (!$jobId && !empty($myJobs)) $jobId = $myJobs[0]['id'];

// Fetch applicants for selected job
$apps = [];
$selectedJobTitle = '';
if ($jobId) {
    $jt = db()->prepare("SELECT title FROM jobs WHERE id=? AND employer_id=? LIMIT 1");
    $jt->bind_param('ii', $jobId, $user['id']);
    $jt->execute();
    $jrow = $jt->get_result()->fetch_assoc();
    $selectedJobTitle = $jrow['title'] ?? '';

    $stmt = db()->prepare(
        "SELECT a.*,u.name,u.email,u.headline,u.location,u.skills
         FROM applications a JOIN users u ON a.applicant_id=u.id
         WHERE a.job_id=? ORDER BY a.applied_at DESC"
    );
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    $apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$statusMap = [
    'Applied'      => 'badge-applied',
    'Under Review' => 'badge-under-review',
    'Interview'    => 'badge-interview',
    'Offered'      => 'badge-offered',
    'Rejected'     => 'badge-rejected',
];

$pageTitle = 'Applicants';
include 'includes/header.php';
?>
<div class="container py-4">
  <h4 class="fw-bold mb-3">Applicants</h4>

  <!-- Job filter tabs -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($myJobs as $j): ?>
      <a href="employer_applicants.php?job=<?= $j['id'] ?>"
         class="btn btn-sm <?= $jobId===$j['id'] ? 'btn-wl' : 'btn-outline-secondary' ?>">
        <?= e($j['title']) ?>
        <span class="badge bg-white text-dark ms-1"><?= $j['cnt'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($myJobs)): ?>
    <div class="wl-card p-5 text-center text-muted">
      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
      No jobs posted yet. <a href="employer_dashboard.php">Post your first job →</a>
    </div>
  <?php elseif (empty($apps)): ?>
    <div class="wl-card p-5 text-center text-muted">
      <i class="bi bi-person-x fs-1 d-block mb-2"></i>
      No applications yet for <strong><?= e($selectedJobTitle) ?></strong>.
    </div>
  <?php else: ?>
    <p class="text-muted small mb-3"><?= count($apps) ?> applicant<?= count($apps)!==1?'s':'' ?> for <strong><?= e($selectedJobTitle) ?></strong></p>

    <?php foreach ($apps as $app):
      $badgeCls = $statusMap[$app['status']] ?? 'badge-applied';
      $skills   = array_filter(explode(',', $app['skills'] ?? ''));
      $initials = strtoupper(substr($app['name'], 0, 2));
    ?>
    <div class="wl-card p-4 mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="flex-grow-1">
          <div class="d-flex gap-3 align-items-center mb-2">
            <div class="wl-avatar-md"><?= $initials ?></div>
            <div>
              <p class="fw-bold mb-0"><?= e($app['name']) ?></p>
              <small class="text-muted"><?= e($app['email']) ?> · Applied <?= date('M j, Y', strtotime($app['applied_at'])) ?></small>
            </div>
          </div>
          <?php if ($app['headline']): ?>
            <p class="text-muted small mb-2"><i class="bi bi-person-badge me-1"></i><?= e($app['headline']) ?></p>
          <?php endif; ?>
          <?php if (!empty($skills)): ?>
            <div class="mb-2">
              <?php foreach ($skills as $s): ?>
                <span class="wl-tag me-1"><?= e(trim($s)) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($app['cover_letter']): ?>
            <div class="p-3 bg-light rounded-3 mb-2">
              <p class="small text-uppercase fw-bold text-muted mb-1">Cover Letter</p>
              <p class="small text-muted mb-0" style="line-height:1.7;"><?= nl2br(e(mb_strimwidth($app['cover_letter'],0,300,'…'))) ?></p>
            </div>
          <?php endif; ?>
          <?php if ($app['resume_link']): ?>
            <p class="mb-0 small"><i class="bi bi-paperclip me-1"></i>
              <a href="<?= e($app['resume_link']) ?>" target="_blank" rel="noreferrer">View Resume</a>
            </p>
          <?php endif; ?>
        </div>

        <!-- STATUS CONTROL -->
        <div class="text-end" style="min-width:160px;">
          <span class="badge <?= $badgeCls ?> mb-2 d-inline-block"><?= $app['status'] ?></span>
          <form method="POST" action="employer_applicants.php?job=<?= $jobId ?>">
            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
            <select name="status" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
              <?php foreach (array_keys($statusMap) as $s): ?>
                <option <?= $app['status']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>