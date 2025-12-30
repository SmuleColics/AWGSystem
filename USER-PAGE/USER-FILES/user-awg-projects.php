<?php
include 'user-header.php';

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$budget_min = isset($_GET['budget_min']) ? $_GET['budget_min'] : '';
$budget_max = isset($_GET['budget_max']) ? $_GET['budget_max'] : '';
$view_type = isset($_GET['view']) ? $_GET['view'] : 'my_projects'; // Default to My Projects

// Build WHERE clause for filters
$filter_conditions = ["p.is_archived = 0"];

// Add view type condition
if ($view_type === 'my_projects') {
  // Show only current user's projects (both public and private)
  $filter_conditions[] = "p.user_id = " . intval($user_id);
} else {
  // Show only public A We Green projects (includes all public projects from any user)
  $filter_conditions[] = "p.visibility = 'Public'";
}

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

// Get project statistics with filters
$stats_sql = "SELECT 
    COUNT(*) as total_projects,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(total_budget) as total_budget_sum
    FROM projects p
    WHERE $where_clause";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// Get all projects with user info and filters
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
ORDER BY p.created_at DESC";
$projects_result = mysqli_query($conn, $projects_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Projects - A We Green Enterprise</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .nav-projects {
      color: #fff !important;
    }

    .filter-card {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .filter-input-group {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .filter-label {
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #495057;
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

    .project-cont {
      width: 100%;
      max-width: 350px;
    }

    .view-toggle {
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
      padding: 0.5rem;
    }

    .view-toggle select {
      border: none;
      background: transparent;
      font-size: 0.875rem;
      font-weight: 600;
      color: #495057;
      cursor: pointer;
      padding: 0.25rem 0.5rem;
    }

    .view-toggle select:focus {
      outline: none;
    }
  </style>
</head>

<body class="bg-light">

  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4">
    <div class="d-flex justify-content-between align-items-center pb-0" style="padding-top: 42px;">
      <div>
        <h1 class="fs-36 mobile-fs-32 green-text">
          <?= $view_type === 'my_projects' ? 'My Projects' : 'A We Green Projects' ?>
        </h1>
        <p class="admin-top-desc mb-0">
          <?= $view_type === 'my_projects' ? 'View and manage your personal projects.' : 'View accomplished projects of A We Green Enterprise.' ?>
        </p>
      </div>
    </div>

    <!-- STATISTICS -->
    <div class="row g-3 mt-3">
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded bg-white">
          <p class="light-text fs-14 mb-0">Total Projects</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['total_projects'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded bg-white">
          <p class="light-text fs-14 mb-0">Completed</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['completed'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded bg-white">
          <p class="light-text fs-14 mb-0">In Progress</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['in_progress'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded bg-white">
          <p class="light-text fs-14 mb-0">Total Budget</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">₱<?= number_format($stats['total_budget_sum'] ?? 0, 2) ?></p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5 pt-0 mt-0">

      <!-- FILTER SECTION -->
      <div class="col-12">
        <div class="filter-card bg-white mb-1 p-4">
          <form method="GET" action="" id="filterForm">
            <input type="hidden" name="view" value="<?= htmlspecialchars($view_type) ?>">

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
              <a href="user-awg-projects.php?view=<?= htmlspecialchars($view_type) ?>" class="btn btn-secondary">
                <i class="fas fa-redo me-1"></i> Clear Filters
              </a>
            </div>
          </form>
        </div>
      </div>

      <div class="col-12 mt-0">
        <div class="border bg-white rounded-3 mt-0 px-4 pb-4 pt-0">
          <div class="d-flex align-items-center justify-content-between gap-3 mt-3">
            <div>
              <p class="fs-24 mobile-fs-22 mb-0">
                All Projects
                <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                  <span class="badge-pill taskstatus-completed fs-14">Filtered</span>
                <?php endif; ?>
              </p>
            </div>
            <div class="view-toggle">
              <select name="view" id="viewSelect" onchange="changeView(this.value)">
                <option value="my_projects" <?= $view_type === 'my_projects' ? 'selected' : '' ?>>My Projects</option>
                <option value="awg_projects" <?= $view_type === 'awg_projects' ? 'selected' : '' ?>>A We Green Projects</option>
              </select>
            </div>
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
                <div class="project-cont p-4 border rounded-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="green-text fw-bold me-2"><?= htmlspecialchars($project['project_type']) ?></span>

                    <?php
                    $status = $project['status'];
                    $class = match ($status) {
                      "Completed"   => "status-badge taskstatus-completed",
                      "Active"      => "status-badge taskstatus-completed",
                      "In Progress" => "status-badge taskstatus-pending",
                      "On Hold"     => "status-badge status-lowstock",
                      "Cancelled"   => "status-badge status-outstock",
                      default       => "status-badge"
                    };
                    ?>
                    <span class="<?= $class ?> text-nowrap"><?= htmlspecialchars($status) ?></span>
                  </div>

                  <h2 class="fs-20 mb-2"><?= htmlspecialchars($project['project_name']) ?></h2>

                  <div class="div mb-3 project-image-container">
                    <?php
                    $project_image_path = '';
                    if (!empty($project['project_image'])) {
                      $pi_clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $project['project_image']);
                      $server_path = __DIR__ . '/../../ADMIN-PAGE/' . $pi_clean_path;
                      if (file_exists($server_path)) {
                        // Construct URL path for <img src>
                        $project_image_path = '../../ADMIN-PAGE/' . $pi_clean_path;
                      }
                    }
                    ?>
                    <?php if ($project_image_path): ?>
                      <img class="w-100 img-fluid rounded"
                        src="<?= htmlspecialchars($project_image_path) ?>"
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
                  <div class="d-grid">
                    <a href="user-projects-detail.php?id=<?= $project['project_id'] ?>"
                      class="btn btn-outline-secondary mt-3">
                      View Details
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-5 w-100">
                <i class="fa-solid fa-folder-open fs-64 text-secondary mb-3"></i>
                <h3 class="fs-24">
                  <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                    No Projects Found
                  <?php else: ?>
                    No Projects Yet
                  <?php endif; ?>
                </h3>
                <p class="text-muted">
                  <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                    Try adjusting your filters to see more results.
                  <?php elseif ($view_type === 'my_projects'): ?>
                    Your projects will appear here once they are created.
                  <?php else: ?>
                    Public A We Green projects will appear here.
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

  <script>
    function changeView(viewType) {
      // Preserve existing filter parameters
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set('view', viewType);
      window.location.href = 'user-awg-projects.php?' + urlParams.toString();
    }

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>