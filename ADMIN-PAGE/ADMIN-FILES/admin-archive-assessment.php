<?php
ob_start();
include 'admin-header.php';

// Handle RESTORE (unarchive)
if (isset($_POST['modal-restore-button'])) {

  $restore_id = (int) $_POST['restore_id'];

  // Get assessment info (for logs & notifications only)
  $get_assessment_sql = "
    SELECT a.assessment_id, a.service_type, a.status, a.user_id,
          u.first_name, u.last_name
    FROM assessments a
    LEFT JOIN users u ON a.user_id = u.user_id
    WHERE a.assessment_id = $restore_id
  ";

  $result = mysqli_query($conn, $get_assessment_sql);

  if ($result && mysqli_num_rows($result) > 0) {

    $assessment_data = mysqli_fetch_assoc($result);
    $user_full_name = $assessment_data['first_name'] . ' ' . $assessment_data['last_name'];
    $service_type   = $assessment_data['service_type'];

    // ✅ ONLY UPDATE is_archived
    $restore_sql = "
      UPDATE assessments 
      SET is_archived = 0 
      WHERE assessment_id = $restore_id
    ";

    if (mysqli_query($conn, $restore_sql)) {

      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'RESTORE',
        'ASSESSMENTS',
        $restore_id,
        $service_type,
        "Restored assessment for $user_full_name - Service: $service_type"
      );

      echo "<script>
        alert('Assessment restored successfully!');
        window.location = '{$_SERVER['PHP_SELF']}';
      </script>";
      exit;

    } else {
      echo "<script>alert('Restore failed.');</script>";
    }

  } else {
    echo "<script>alert('Assessment not found.');</script>";
  }
}

// Get statistics for ARCHIVED assessments only
$stats = [
  'total_archived' => 0,
  'pending' => 0,
  'accepted' => 0,
  'rejected' => 0,
  'completed' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments WHERE is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_archived'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments WHERE status = 'Pending' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['pending'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments WHERE status = 'Accepted' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['accepted'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments WHERE status = 'Rejected' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['rejected'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments WHERE status = 'Completed' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['completed'] = $row['count'];
}

// Get all ARCHIVED assessments only
$assessments = [];
$sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, 
        u.house_no, u.brgy, u.city, u.province, u.zip_code
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE a.is_archived = 1
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $assessments[] = $row;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Assessments - Admin Dashboard</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />

  <style>
    .sidebar-content-item:nth-child(4) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(4) .sidebar-anchor,
    .sidebar-content-item:nth-child(4) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>

  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4  py-5 min-vh-100">
    <!-- BACK BUTTON -->
    <a href="admin-assessments.php" class="btn btn-outline-secondary mb-3">
      <i class="fa fa-arrow-left me-2"></i> Back to Assessments
    </a>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Assessments</h1>
        <p class="admin-top-desc">View and restore archived assessment requests</p>
      </div>
    </div>

    <div class="row g-3 mb-4">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Archived</p>
            <p class="mb-0 fs-24 text-secondary"><?= $stats['total_archived'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box-archive fs-32 text-secondary"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Pending</p>
            <p class="mb-0 fs-24 text-warning"><?= $stats['pending'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-clock fs-32 text-warning"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Accepted</p>
            <p class="mb-0 fs-24 green-text"><?= $stats['accepted'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-check-circle fs-32 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Rejected</p>
            <p class="mb-0 fs-24 text-danger"><?= $stats['rejected'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-times-circle fs-32 text-danger"></i>
          </div>
        </div>
      </div>

    </div>

    <div class="row g-3 pb-5">

      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Archived Assessments (<?= count($assessments) ?>)</h2>
          </div>
          
          <div class="px-4 pb-4 d-flex flex-column gap-4">

            <?php if (empty($assessments)): ?>
              <div class="text-center py-5">
                <i class="fa-solid fa-box-archive fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Archived Assessments</h4>
                <p class="text-muted">All your assessments are active.</p>
              </div>
            <?php else: ?>
              <?php foreach ($assessments as $assessment): ?>
                <?php
                $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
                $location = trim($assessment['city'] . ', ' . $assessment['province']);
                $formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));

                // Status badge classes
                $statusClass = match ($assessment['status']) {
                  "Pending"      => "badge-pill taskstatus-pending",
                  "Accepted"     => "badge-pill taskstatus-inprogress",
                  "Completed"    => "badge-pill taskstatus-completed",
                  "Rejected"     => "badge-pill priority-high",
                  default        => "badge-pill"
                };
                ?>

                <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
                  <div class="w-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                      <h3 class="fs-18 mb-0">
                        <?= htmlspecialchars($user_full_name) ?>
                        <span class="fs-14 light-text">(<?= htmlspecialchars($assessment['email']) ?>)</span>
                      </h3>
                      <span class="<?= $statusClass ?>"><?= htmlspecialchars($assessment['status']) ?></span>
                      <span class="badge-pill priority-high">Archived</span>
                    </div>

                    <div class="row mt-1">
                      <div class="col-md-6">
                        <p class="fs-14 mb-2">
                          <span class="light-text">Service: </span>
                          <?= htmlspecialchars($assessment['service_type']) ?>
                        </p>
                        <p class="fs-14 mb-2">
                          <span class="light-text">Time: </span>
                          <?= htmlspecialchars($assessment['preferred_time']) ?>
                        </p>
                        <p class="fs-14 mb-2">
                          <span class="light-text">Location: </span>
                          <?= htmlspecialchars($location) ?>
                        </p>
                        <?php if (!empty($assessment['notes'])): ?>
                          <p class="fs-14 mb-0">
                            <span class="light-text">Notes: </span><br />
                            <?= htmlspecialchars($assessment['notes']) ?>
                          </p>
                        <?php endif; ?>
                      </div>

                      <div class="col-md-6">
                        <p class="fs-14 mb-2">
                          <span class="light-text">Date: </span>
                          <?= $formatted_date ?>
                        </p>
                        <p class="fs-14 mb-2">
                          <span class="light-text">Phone: </span>
                          <?= htmlspecialchars($assessment['phone']) ?>
                        </p>
                        <?php if (!empty($assessment['estimated_budget'])): ?>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Estimated Budget: </span>
                            ₱<?= number_format($assessment['estimated_budget'], 2) ?>
                          </p>
                        <?php endif; ?>
                        <p class="fs-14 mb-2">
                          <span class="light-text">Requested: </span>
                          <?= date('M d, Y h:i A', strtotime($assessment['created_at'])) ?>
                        </p>
                      </div>
                    </div>

                  </div>

                  <div class="assessment-actions d-flex flex-column gap-2">
                    <?php if ($is_admin): ?>
                      <!-- Restore button -->
                      <button
                        type="button"
                        class="btn btn-success flex w-100 restore-assessment"
                        data-id="<?= $assessment['assessment_id'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#restoreAssessmentModal">
                        <i class="fa-solid fa-rotate-left me-1"></i>
                        Restore
                      </button>
                    <?php endif; ?>
                  </div>
                </div>

              <?php endforeach; ?>
            <?php endif; ?>

          </div>
        </div>
      </div>

    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- RESTORE MODAL -->
  <div class="modal fade" id="restoreAssessmentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="restoreAssessmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="restoreAssessmentLabel">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Assessment
          </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="restore_id" id="restoreAssessmentId">
          <div class="modal-body">
            <h3 class="fs-20 text-center m-0 py-3">Are you sure you want to restore this assessment?</h3>
            <p class="text-center text-muted mb-0">This assessment will be moved back to active assessments.</p>
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
  // Restore Assessment Modal - Set ID
  document.querySelectorAll('.restore-assessment').forEach(button => {
    button.addEventListener('click', function() {
      const assessmentId = this.getAttribute('data-id');
      document.getElementById('restoreAssessmentId').value = assessmentId;
    });
  });
</script>

</html>