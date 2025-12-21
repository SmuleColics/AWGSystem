<?php
ob_start();
include 'admin-header.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id === 0) {
  header('Location: admin-projects.php');
  exit;
}

// Fetch project details
$project_sql = "SELECT project_name FROM projects WHERE project_id = $project_id AND is_archived = 0";
$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  header('Location: admin-projects.php');
  exit;
}

$project = mysqli_fetch_assoc($project_result);

// Handle RESTORE
if (isset($_POST['modal-restore-button'])) {
  $restore_id = (int) $_POST['restore_id'];
  
  $restore_sql = "UPDATE project_updates SET is_archived = 0 WHERE update_id = $restore_id";
  
  if (mysqli_query($conn, $restore_sql)) {
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'RESTORE',
      'PROJECT_UPDATES',
      $restore_id,
      'Update Restored',
      "Restored project update for project #$project_id"
    );
    
    echo "<script>
      alert('Update restored successfully!');
      window.location = '{$_SERVER['PHP_SELF']}?id=$project_id';
    </script>";
    exit;
  }
}

// Fetch archived updates
$updates_sql = "SELECT pu.*, e.first_name, e.last_name 
                FROM project_updates pu
                LEFT JOIN employees e ON pu.created_by = e.employee_id
                WHERE pu.project_id = $project_id AND pu.is_archived = 1
                ORDER BY pu.created_at DESC";
$updates_result = mysqli_query($conn, $updates_sql);
$updates = [];
while ($row = mysqli_fetch_assoc($updates_result)) {
  $updates[] = $row;
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Archived Updates - <?= htmlspecialchars($project['project_name']) ?></title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A246 !important;
    }

    .update-image {
      max-width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>

<body class="bg-light">

  <div class="container-xxl px-4 py-5 min-vh-100">

    <!-- BACK BUTTON -->
    <a href="admin-projects-detail.php?id=<?= $project_id ?>" class="btn btn-outline-secondary mb-4">
      <i class="fa fa-arrow-left me-2"></i> Back to Project
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Updates</h1>
        <p class="admin-top-desc">Project: <?= htmlspecialchars($project['project_name']) ?></p>
      </div>
    </div>

    <div class="card shadow-sm p-4">
      <div class="card-body">
        <?php if (count($updates) > 0): ?>
          <?php foreach ($updates as $update): ?>
            <div class="p-3 mb-3 rounded border bg-light">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="fw-bold mb-1 fs-18"><?= htmlspecialchars($update['update_title']) ?></h6>
                  <span class="badge bg-danger">Archived</span>
                </div>
                <div class="update-btns">
                  <button class="btn btn-success btn-sm restore-update"
                    data-id="<?= $update['update_id'] ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#restoreUpdateModal">
                    <i class="fa-solid fa-rotate-left me-1"></i> Restore
                  </button>
                </div>
              </div>
              
              <p class="text-secondary small mb-2 mt-2"><?= nl2br(htmlspecialchars($update['update_description'])) ?></p>

              <?php if (!empty($update['update_image']) && file_exists($update['update_image'])): ?>
                <img src="<?= htmlspecialchars($update['update_image']) ?>"
                  class="update-image mt-2 border"
                  alt="Update image">
              <?php endif; ?>

              <div class="d-flex justify-content-between align-items-center mt-2">
                <p class="light-text small mb-0">
                  <?= date('F d, Y – g:i A', strtotime($update['created_at'])) ?>
                  <?php if (!empty($update['first_name'])): ?>
                    by <?= htmlspecialchars($update['first_name'] . ' ' . $update['last_name']) ?>
                  <?php endif; ?>
                </p>
                <?php if ($update['progress_percentage'] !== null): ?>
                  <span class="badge bg-success">Progress: <?= $update['progress_percentage'] ?>%</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fa-solid fa-box-archive fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No Archived Updates</h4>
            <p class="text-muted">All project updates are active.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- RESTORE MODAL -->
  <div class="modal fade" id="restoreUpdateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Update
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="restore_id" id="restoreUpdateId">
          <div class="modal-body">
            <h6 class="text-center py-4">Are you sure you want to restore this update?</h6>
            <p class="text-center text-muted">This update will be moved back to active updates.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-restore-button" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

<script>
  // Restore Update Modal
  document.querySelectorAll('.restore-update').forEach(button => {
    button.addEventListener('click', function() {
      const updateId = this.getAttribute('data-id');
      document.getElementById('restoreUpdateId').value = updateId;
    });
  });
</script>

</html>