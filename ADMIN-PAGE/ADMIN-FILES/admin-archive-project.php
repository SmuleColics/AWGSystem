<?php
ob_start();
include 'admin-header.php';

// Get archived project statistics
$stats_sql = "SELECT 
    COUNT(*) as total_archived,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM projects 
    WHERE is_archived = 1";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// Get all archived projects with user info
$projects_sql = "SELECT 
    p.project_id,
    p.project_name,
    p.project_type,
    p.location AS project_location,
    p.status,
    p.project_image,
    p.created_at,
    p.updated_at,
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
WHERE p.is_archived = 1
ORDER BY p.updated_at DESC";
$projects_result = mysqli_query($conn, $projects_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Projects - Admin</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A246 !important;
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
      opacity: 0.7;
    }

    .project-image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .no-image-placeholder {
      font-size: 64px;
      color: #dee2e6;
    }

    .project-con {
      width: 100%;
      max-width: 350px;
      opacity: 0.85;
    }

  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">
    <!-- BACK BUTTON -->
    <a href="admin-projects.php" class="btn btn-outline-secondary mb-3">
      <i class="fa fa-arrow-left me-2"></i> Back to Projects
    </a>
    
    <div class="d-flex">
    
    .<div>
        <h1 class="fs-36 mobile-fs-32">Archived Projects</h1>
        <p class="admin-top-desc">View and restore archived projects.</p>
      </div>
      
    </div>

    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Archived</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['total_archived'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Completed</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['completed'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">In Progress</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['in_progress'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Cancelled</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['cancelled'] ?? 0 ?></p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">Archived Projects</p>
          </div>
          <div class="divider my-3"></div>

          <div class="project-container d-flex flex-wrap gap-4 justify-content-center">

            <?php if (mysqli_num_rows($projects_result) > 0): ?>
              <?php while ($project = mysqli_fetch_assoc($projects_result)): ?>
                <?php
                $client_name = htmlspecialchars($project['first_name'] . ' ' . $project['last_name']);
                $location = !empty($project['project_location'])
                  ? $project['project_location']
                  : ($project['user_location'] ?: 'N/A');
                ?>
                <div class="project-con p-4 border rounded-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="green-text fw-bold me-2"><?= htmlspecialchars($project['project_type']) ?></span>

                    <div class="d-flex gap-2 align-items-center">
                      <span class="badge-pill priority-high">ARCHIVED</span>
                      <?php
                      $status = $project['status'];
                      $class = match ($status) {
                        "Completed"   => "badge-pill taskstatus-completed",
                        "Active"      => "badge-pill taskstatus-completed",
                        "In Progress" => "badge-pill taskstatus-pending",
                        "On Hold"     => "badge-pill status-lowstock",
                        "Cancelled"   => "badge-pill status-outstock",
                        default       => "badge-pill"
                      };
                      ?>
                      <span class="<?= $class ?> text-nowrap"><?= htmlspecialchars($status) ?></span>
                    </div>
                  </div>

                  <h2 class="fs-20 mb-2"><?= htmlspecialchars($project['project_name']) ?></h2>

                  <div class="div mb-3 project-image-container">
                    <?php if (!empty($project['project_image']) && file_exists($project['project_image'])): ?>
                      <img class="w-100 img-fluid rounded"
                        src="<?= htmlspecialchars($project['project_image']) ?>"
                        alt="<?= htmlspecialchars($project['project_name']) ?>">
                    <?php else: ?>
                      <i class="fa-solid fa-image no-image-placeholder"></i>
                    <?php endif; ?>
                  </div>

                  <div class="d-flex fs-14 mb-2 gap-1">
                    <span class="light-text">Client:</span>
                    <span><?= $client_name ?></span>
                  </div>
                  <div class="d-flex gap-1 fs-14 mb-2">
                    <span class="light-text">Location:</span>
                    <span><?= htmlspecialchars($location) ?></span>
                  </div>
                  <div class="d-flex gap-1 fs-14 mb-3">
                    <span class="light-text">Archived:</span>
                    <span><?= date('M d, Y', strtotime($project['updated_at'])) ?></span>
                  </div>
                  <?php if ($is_admin): ?>
                  <div class="d-grid">
                    <button onclick="restoreProject(<?= $project['project_id'] ?>, '<?= addslashes($project['project_name']) ?>')"
                      class="btn btn-success">
                      <i class="fa fa-rotate-left me-1"></i> Restore Project
                    </button>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-5 w-100">
                <i class="fa-solid fa-box-open fs-64 text-secondary mb-3"></i>
                <h3 class="fs-24">No Archived Projects</h3>
                <p class="text-muted">Archived projects will appear here.</p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- Restore Project Confirmation Modal -->
  <div class="modal fade" id="restoreProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Restore Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-archive-project.php" method="POST">
          <input type="hidden" name="project_id" id="restoreProjectId">

          <div class="modal-body">
            <div class="text-center py-3">
              <i class="fas fa-rotate-left text-success fs-48 mb-3"></i>
              <h5>Are you sure you want to restore this project?</h5>
              <p class="fw-semibold" id="restoreProjectName"></p>
              <p class="text-muted">This project will be moved back to active projects.</p>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success" name="restore_project">
              <i class="fas fa-rotate-left me-1"></i> Restore Project
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Restore project function
    function restoreProject(projectId, projectName) {
      document.getElementById('restoreProjectId').value = projectId;
      document.getElementById('restoreProjectName').textContent = projectName;
      const modal = new bootstrap.Modal(document.getElementById('restoreProjectModal'));
      modal.show();
    }

    // Show success/error messages
    <?php if (isset($_SESSION['success'])): ?>
      alert('<?= addslashes($_SESSION['success']) ?>');
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      alert('<?= addslashes($_SESSION['error']) ?>');
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  </script>

</body>

</html>