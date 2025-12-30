<?php
include 'user-header.php';

// Get the most recent project for the current user
$recent_project_sql = "SELECT 
    p.project_id,
    p.project_name,
    p.project_type,
    p.description,
    p.location AS project_location,
    p.status,
    p.project_image,
    p.start_date,
    p.end_date,
    p.duration,
    p.total_budget,
    p.created_at,
    u.first_name,
    u.last_name,
    u.email,
    TRIM(CONCAT_WS(', ',
        NULLIF(u.house_no, ''),
        NULLIF(u.brgy, ''),
        NULLIF(u.city, ''),
        NULLIF(u.province, ''),
        NULLIF(u.zip_code, '')
    )) AS user_location
FROM projects p
JOIN users u ON p.user_id = u.user_id
WHERE p.user_id = $user_id AND p.is_archived = 0
ORDER BY p.created_at DESC
LIMIT 1";
$recent_project_result = mysqli_query($conn, $recent_project_sql);
$recent_project = mysqli_fetch_assoc($recent_project_result);

// Count total projects for the user
$project_count_sql = "SELECT COUNT(*) as total FROM projects WHERE user_id = $user_id AND is_archived = 0";
$project_count_result = mysqli_query($conn, $project_count_sql);
$project_count = mysqli_fetch_assoc($project_count_result)['total'];

// Get recent project updates (limit 5)
$updates_sql = "SELECT 
    pu.*,
    p.project_name,
    p.project_type
FROM project_updates pu
JOIN projects p ON pu.project_id = p.project_id
WHERE p.user_id = $user_id
ORDER BY pu.created_at DESC
LIMIT 5";
$updates_result = mysqli_query($conn, $updates_sql);

// Get recent assessments (limit 5) - FIXED: Removed services table join
$assessments_sql = "SELECT 
    a.*
FROM assessments a
WHERE a.user_id = $user_id
ORDER BY a.created_at DESC
LIMIT 5";
$assessments_result = mysqli_query($conn, $assessments_sql);

// Count total assessments
$assessment_count_sql = "SELECT COUNT(*) as total FROM assessments WHERE user_id = $user_id";
$assessment_count_result = mysqli_query($conn, $assessment_count_sql);
$assessment_count = mysqli_fetch_assoc($assessment_count_result)['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customer Portal - A We Green Enterprise</title>

  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .nav-portal {
      color: #fff !important;
    }

    .project-image-container {
      position: relative;
      height: 200px;
      overflow: hidden;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.375rem;
      margin-bottom: 1rem;
    }

    .project-image-container {
      min-height: 300px;
      object-fit: cover;
    }

    .no-image-placeholder {
      font-size: 64px;
      color: #dee2e6;
    }

    .update-image {
      max-width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: 0.375rem;
    }
  </style>
</head>

<body class="bg-light">

  <div class="container-xxl px-4">

    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Customer Portal</h1>
        <p class="admin-top-desc">Access and manage your customer information and updates.</p>
      </div>
    </div>

    <div class="row g-4">

      <!-- LEFT SIDE -->
      <div class="col-lg-8">

        <?php if ($recent_project): ?>
          <?php
          $client_name = htmlspecialchars($recent_project['first_name'] . ' ' . $recent_project['last_name']);
          $location = !empty($recent_project['project_location'])
            ? $recent_project['project_location']
            : ($recent_project['user_location'] ?: 'N/A');

          // Handle project image path
          $project_image_path = '';
          if (!empty($recent_project['project_image'])) {
            $pi_clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $recent_project['project_image']);
            $server_path = __DIR__ . '/../../ADMIN-PAGE/' . $pi_clean_path;
            if (file_exists($server_path)) {
              $project_image_path = '../../ADMIN-PAGE/' . $pi_clean_path;
            }
          }

          // Format dates
          $duration = 'N/A';
          if (!empty($recent_project['start_date']) && !empty($recent_project['end_date'])) {
            $duration = date('M d', strtotime($recent_project['start_date'])) . ' – ' .
              date('M d, Y', strtotime($recent_project['end_date']));
          } elseif (!empty($recent_project['duration'])) {
            $duration = htmlspecialchars($recent_project['duration']);
          }
          ?>

          <div class="card mb-4 p-4">
            <div class="card-body">

              <!-- TOP ROW -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="green-text fw-bold me-2"><?= htmlspecialchars($recent_project['project_type']) ?></span>
                </div>

                <?php
                $status = $recent_project['status'];
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
                  "Active"      => "status-badge taskstatus-completed",
                  "In Progress" => "status-badge taskstatus-pending",
                  "On Hold"     => "status-badge status-lowstock",
                  "Cancelled"   => "status-badge status-outstock",
                  default       => "status-badge"
                };
                ?>
                <div>
                  <span class="<?= $class ?>"><?= htmlspecialchars($status) ?></span>
                </div>
              </div>

              <!-- PROJECT IMAGE -->
              <?php if ($project_image_path): ?>
                <div class="project-image-container">
                  <img class="w-100" src="<?= htmlspecialchars($project_image_path) ?>"
                    alt="<?= htmlspecialchars($recent_project['project_name']) ?>">
                </div>
              <?php endif; ?>

              <!-- PROJECT TITLE -->
              <h3 class="fw-bold mb-3"><?= htmlspecialchars($recent_project['project_name']) ?></h3>

              <!-- DESCRIPTION -->
              <?php if (!empty($recent_project['description'])): ?>
                <h6 class="fw-semibold">Description</h6>
                <p class="text-secondary"><?= nl2br(htmlspecialchars($recent_project['description'])) ?></p>
              <?php endif; ?>

              <!-- DETAILS GRID -->
              <div class="row g-3 mt-4">
                <div class="col-md-6 d-flex align-items-center">
                  <i class="fa fa-user text-secondary me-2"></i>
                  <div>
                    <p class="light-text small mb-0">Client</p>
                    <p class="fw-semibold mb-0"><?= $client_name ?></p>
                  </div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                  <i class="fa fa-location-dot text-secondary me-2"></i>
                  <div>
                    <p class="light-text small mb-0">Location</p>
                    <p class="fw-semibold mb-0"><?= htmlspecialchars($location) ?></p>
                  </div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                  <i class="fa fa-calendar text-secondary me-2"></i>
                  <div>
                    <p class="light-text small mb-0">Duration</p>
                    <p class="fw-semibold mb-0"><?= $duration ?></p>
                  </div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                  <i class="fa fa-peso-sign text-secondary me-2"></i>
                  <div>
                    <p class="light-text small mb-0">Budget</p>
                    <p class="fw-semibold mb-0">₱<?= number_format($recent_project['total_budget'], 2) ?></p>
                  </div>
                </div>
              </div>

              <div class="d-grid mt-4">
                <a href="user-project-monitoring.php?id=<?= $recent_project['project_id'] ?>"
                  class="btn btn-green">View Project Details and Monitoring</a>
              </div>

              <?php if ($project_count > 1): ?>
                <div class="d-grid mt-2">
                  <a href="user-awg-projects.php?view=my_projects" class="btn btn-outline-secondary">
                    <i class="fas fa-folder-open me-1"></i> View All Projects (<?= $project_count ?>)
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>

        <?php else: ?>
          <div class="card mb-4 p-4">
            <div class="card-body text-center py-5">
              <i class="fa-solid fa-folder-open fs-64 text-secondary mb-3"></i>
              <h3 class="fs-24">No Projects Yet</h3>
              <p class="text-muted">Your projects will appear here once quotations are approved.</p>
              <a href="user-assessments.php" class="btn btn-green mt-3">
                <i class="fas fa-clipboard-check me-1"></i> Request Assessment
              </a>
            </div>
          </div>
        <?php endif; ?>

        <!-- RECENT UPDATES -->
        <div class="card border mb-4">
          <div class="card-header d-flex align-items-center bg-white">
            <i class="fas fa-bell me-2"></i>
            <h5 class="mb-0">Recent Updates</h5>
          </div>

          <div class="card-body">
            <?php if (mysqli_num_rows($updates_result) > 0): ?>
              <div class="d-flex flex-column gap-3">
                <?php while ($update = mysqli_fetch_assoc($updates_result)): ?>
                  <div class="card border">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start">
                        <p class="fw-semibold mb-1"><?= htmlspecialchars($update['project_name']) ?></p>

                        <?php
                        $update_status = $update['status'] ?? 'In Progress';
                        $class = match ($update_status) {
                          "Completed"   => "status-badge taskstatus-completed",
                          "In Progress" => "status-badge taskstatus-pending",
                          "On Hold"     => "status-badge status-lowstock",
                          default       => "status-badge"
                        };
                        ?>
                        <span class="<?= $class ?>"><?= htmlspecialchars($update_status) ?></span>
                      </div>

                      <p class="text-muted small mb-1"><?= nl2br(htmlspecialchars($update['update_description'])) ?></p>

                      <?php
                      // Handle update image path
                      $update_image_path = '';
                      if (!empty($update['update_image'])) {
                        $ui_clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $update['update_image']);
                        $server_path = __DIR__ . '/../../ADMIN-PAGE/' . $ui_clean_path;
                        if (file_exists($server_path)) {
                          $update_image_path = '../../ADMIN-PAGE/' . $ui_clean_path;
                        }
                      }
                      ?>
                      <?php if ($update_image_path): ?>
                        <div class="w-100 flex mt-2">
                          <img src="<?= htmlspecialchars($update_image_path) ?>"
                            class="update-image border"
                            alt="Update image">
                        </div>
                      <?php endif; ?>

                      <p class="text-muted small mb-0 mt-2">
                        <?= date('F d, Y – g:i A', strtotime($update['created_at'])) ?>
                      </p>
                    </div>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                <p class="text-muted mb-0">No recent updates available.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- RIGHT SIDE — ASSESSMENTS LIST -->
      <div class="col-lg-4">
        <div class="card shadow-sm position-sticky" style="top: 90px; max-height: 65vh;">
          <div class="card-header bg-white">
            <h5 class="fw-semibold mb-0">My Assessments</h5>
          </div>

          <div class="card-body overflow-y-auto">
            <?php if (mysqli_num_rows($assessments_result) > 0): ?>
              <?php while ($assessment = mysqli_fetch_assoc($assessments_result)): ?>
                <div class="p-3 border rounded mb-3">
                  <h6 class="fw-bold mb-1">
                    <?= htmlspecialchars($assessment['service_type'] ?? 'Assessment') ?>
                  </h6>

                  <?php if (!empty($assessment['notes'])): ?>
                    <p class="light-text small mb-2">
                      <?= htmlspecialchars(substr($assessment['notes'], 0, 80)) ?>
                      <?= strlen($assessment['notes']) > 80 ? '...' : '' ?>
                    </p>
                  <?php endif; ?>

                  <p class="light-text small mb-1">
                    <strong>Service:</strong> <?= htmlspecialchars($assessment['service_type'] ?? 'N/A') ?>
                  </p>

                  <p class="light-text small mb-1">
                    <strong>Preferred Date:</strong> <?= date('M d, Y', strtotime($assessment['preferred_date'])) ?>
                  </p>

                  <?php if (!empty($assessment['estimated_budget'])): ?>
                    <p class="light-text small mb-2">
                      <strong>Estimated Budget:</strong> ₱<?= number_format($assessment['estimated_budget'], 2) ?>
                    </p>
                  <?php endif; ?>

                  <?php
                  $assess_status = $assessment['status'] ?? 'Pending';
                  $status_class = match ($assess_status) {
                    "Approved", "Accepted"  => "status-badge taskstatus-completed",
                    "Pending"               => "status-badge taskstatus-pending",
                    "Rejected", "Declined"  => "status-badge status-outstock",
                    default                 => "status-badge"
                  };
                  ?>
                  <span class="<?= $status_class ?> mb-2 d-inline-block"><?= htmlspecialchars($assess_status) ?></span>

                  <a href="user-assessments-detail.php?id=<?= $assessment['assessment_id'] ?>"
                    class="btn btn-light border w-100 small">
                    View Assessment Details
                  </a>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-2"></i>
                <p class="text-muted mb-0">No assessments yet.</p>
              </div>
            <?php endif; ?>
          </div>

          <div class="card-footer bg-white">
            <?php if ($assessment_count > 5): ?>
              <a href="user-assessments.php" class="btn btn-green w-100 mb-2">
                <i class="fas fa-eye me-1"></i>View All Assessments (<?= $assessment_count ?>)
              </a>
            <?php else: ?>
              <a href="user-assessments.php" class="btn btn-green w-100 mb-2">
                <i class="fas fa-eye me-1"></i>View All Assessments
              </a>
            <?php endif; ?>
            <a href="request-assessment.php" class="btn btn-green w-100">
              <i class="fas fa-calendar-check me-1"></i>Schedule Assessment
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>