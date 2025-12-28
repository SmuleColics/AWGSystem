<?php
ob_start();
include 'admin-header.php';

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$budget_min = isset($_GET['budget_min']) ? $_GET['budget_min'] : '';
$budget_max = isset($_GET['budget_max']) ? $_GET['budget_max'] : '';

// Build WHERE clause for filters
$filter_conditions = ["p.is_archived = 1"];

if (!empty($date_from)) {
  $filter_conditions[] = "p.start_date >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}

if (!empty($date_to)) {
  $filter_conditions[] = "p.start_date <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}

if (!empty($budget_min) && is_numeric($budget_min)) {
  $filter_conditions[] = "p.total_budget >= " . floatval($budget_min);
}

if (!empty($budget_max) && is_numeric($budget_max)) {
  $filter_conditions[] = "p.total_budget <= " . floatval($budget_max);
}

$where_clause = implode(" AND ", $filter_conditions);

// Get archived project statistics with filters
$stats_sql = "SELECT 
    COUNT(*) as total_archived,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(total_budget) as total_budget_sum
    FROM projects p
    WHERE $where_clause";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// Get all archived projects with user info and filters
$projects_sql = "SELECT 
    p.project_id,
    p.project_name,
    p.project_type,
    p.location AS project_location,
    p.status,
    p.project_image,
    p.start_date,
    p.total_budget,
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
WHERE $where_clause
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

    .filter-card {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .filter-label {
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #495057;
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
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Projects</h1>
        <p class="admin-top-desc">View and restore archived projects.</p>
      </div>
    </div>

    <!-- STATISTICS -->
    <div class="row g-3 mb-4">
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
          <p class="light-text fs-14 mb-0">Total Budget</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">₱<?= number_format($stats['total_budget_sum'] ?? 0, 2) ?></p>
        </div>
      </div>
    </div>

    <!-- FILTER SECTION -->
    <div class="filter-card bg-white mb-1">
      <form method="GET" action="" id="filterForm">
        <div class="row g-3">
          <!-- Date Range Filter -->
          <div class="col-md-3">
            <label class="filter-label">
              <i class="fas fa-calendar-alt me-1"></i> Start Date From
            </label>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
          </div>

          <div class="col-md-3">
            <label class="filter-label">
              <i class="fas fa-calendar-alt me-1"></i> Start Date To
            </label>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
          </div>

          <!-- Budget Range Filter -->
          <div class="col-md-3">
            <label class="filter-label">
              <i class="fas fa-peso-sign me-1"></i> Min Budget (₱)
            </label>
            <input type="number" name="budget_min" class="form-control" placeholder="0"
              value="<?= htmlspecialchars($budget_min) ?>" step="0.01" min="0">
          </div>

          <div class="col-md-3">
            <label class="filter-label">
              <i class="fas fa-peso-sign me-1"></i> Max Budget (₱)
            </label>
            <input type="number" name="budget_max" class="form-control" placeholder="999999"
              value="<?= htmlspecialchars($budget_max) ?>" step="0.01" min="0">
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-green">
            <i class="fas fa-filter me-1"></i> Apply Filters
          </button>
          <a href="admin-archive-project.php" class="btn btn-secondary">
            <i class="fas fa-redo me-1"></i> Clear Filters
          </a>
        </div>
      </form>
    </div>

    <div class="row g-3 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">
              Archived Projects
              <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                <span class="badge-pill taskstatus-completed fs-14">Filtered</span>
              <?php endif; ?>
            </p>
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
                  <div class="d-flex gap-1 fs-14 mb-2">
                    <span class="light-text">Budget:</span>
                    <span class="fw-bold text-success">₱<?= number_format($project['total_budget'], 2) ?></span>
                  </div>
                  <?php if (!empty($project['start_date'])): ?>
                    <div class="d-flex gap-1 fs-14 mb-2">
                      <span class="light-text">Start Date:</span>
                      <span><?= date('M d, Y', strtotime($project['start_date'])) ?></span>
                    </div>
                  <?php endif; ?>
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
                <h3 class="fs-24">
                  <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                    No Archived Projects Found
                  <?php else: ?>
                    No Archived Projects
                  <?php endif; ?>
                </h3>
                <p class="text-muted">
                  <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                    Try adjusting your filters to see more results.
                  <?php else: ?>
                    Archived projects will appear here.
                  <?php endif; ?>
                </p>
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