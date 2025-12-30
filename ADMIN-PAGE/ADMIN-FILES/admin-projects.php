<?php
ob_start();
include 'admin-header.php';

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$budget_min = isset($_GET['budget_min']) ? $_GET['budget_min'] : '';
$budget_max = isset($_GET['budget_max']) ? $_GET['budget_max'] : '';

// Build WHERE clause for filters
$filter_conditions = ["p.is_archived = 0"];

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
  <title>Admin Dashboard - Projects</title>
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
    }

    .project-image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .upload-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s;
      cursor: pointer;
    }

    .project-image-container:hover .upload-overlay {
      opacity: 1;
    }

    .no-image-placeholder {
      font-size: 64px;
      color: #dee2e6;
    }

    .project-con {
      width: 100%;
      max-width: 350px;
    }

    .image-actions {
      position: absolute;
      top: 10px;
      right: 10px;
      display: flex;
      gap: 8px;
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 10;
    }

    .project-image-container:hover .image-actions {
      opacity: 1;
    }

    .image-action-btn {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }

    .image-action-btn:hover {
      background: white;
      transform: scale(1.1);
    }

    .image-action-btn.delete {
      color: #dc3545;
    }

    .image-action-btn.edit {
      color: #0d6efd;
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
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Projects</h1>
        <p class="admin-top-desc">Create new projects, assign tasks, and monitor progress in real time.</p>
      </div>
      <div class="d-flex gap-2 flex-column flex-md-row">
        <a href="admin-archive-project.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-folder me-1"></i> Archived <span class="d-none d-md-block ms-1">Project</span>
        </a>
      </div>
    </div>

    <!-- STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Projects</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['total_projects'] ?? 0 ?></p>
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
          <a href="admin-projects.php" class="btn btn-secondary">
            <i class="fas fa-redo me-1"></i> Clear Filters
          </a>
        </div>
      </form>
    </div>

    <div class="row g-3 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
              <p class="fs-24 mobile-fs-22 mb-0">
                All Projects
                <?php if (!empty($date_from) || !empty($date_to) || !empty($budget_min) || !empty($budget_max)): ?>
                  <span class="badge-pill taskstatus-completed fs-14">Filtered</span>
                <?php endif; ?>
              </p>
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
                <div class="project-con p-4 border rounded-3">
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
                    <?php if (!empty($project['project_image']) && file_exists($project['project_image'])): ?>
                      <img class="w-100 img-fluid rounded"
                        src="<?= htmlspecialchars($project['project_image']) ?>"
                        alt="<?= htmlspecialchars($project['project_name']) ?>">

                      <?php if ($is_admin): ?>
                        <div class="image-actions">
                          <button class="image-action-btn edit"
                            onclick="openEditImageModal(<?= $project['project_id'] ?>)"
                            title="Change image">
                            <i class="fas fa-edit"></i>
                          </button>
                          <button class="image-action-btn delete"
                            onclick="deleteProjectImage(<?= $project['project_id'] ?>)"
                            title="Delete image">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      <?php endif; ?>
                    <?php else: ?>
                      <i class="fa-solid fa-image no-image-placeholder"></i>
                    <?php endif; ?>

                    <?php if ($is_admin): ?>
                      <div class="upload-overlay" onclick="openUploadModal(<?= $project['project_id'] ?>)">
                        <div class="text-white text-center">
                          <i class="fa-solid fa-camera fs-32 mb-2"></i>
                          <p class="mb-0"><?= !empty($project['project_image']) ? 'Change Image' : 'Upload Image' ?></p>
                        </div>
                      </div>
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
                    <a href="admin-projects-detail.php?id=<?= $project['project_id'] ?>"
                      class="btn btn-outline-secondary mt-3">
                      View Details
                    </a>
                    <?php if ($is_admin): ?>
                      <button onclick="archiveProject(<?= $project['project_id'] ?>, '<?= addslashes($project['project_name']) ?>')"
                        class="btn btn-danger mt-2">
                        <i class="fa fa-archive me-1"></i> Archive Project
                      </button>
                    <?php endif; ?>
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
                  <?php else: ?>
                    Projects will appear here once quotations are approved.
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

  <!-- Keep all your existing modals here -->
  <!-- Upload/Edit Project Image Modal -->
  <div class="modal fade" id="uploadProjectImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalTitle">Upload Project Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-project-image.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="project_id" id="uploadProjectId">

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Select Project Image</label>
              <input type="file" class="form-control" name="project_image" id="projectImageInput"
                accept="image/jpeg,image/png,image/jpg,image/webp" required>
              <small class="text-muted">Accepted formats: JPG, PNG, WEBP (Max 5MB)</small>
            </div>

            <div id="imagePreview" class="d-none">
              <label class="form-label fw-semibold">Preview:</label>
              <img id="previewImage" class="img-fluid rounded border" style="max-height: 300px; width: 100%; object-fit: cover;">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green" name="upload_project_image">
              <i class="fas fa-upload me-1"></i> Upload Image
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Image Confirmation Modal -->
  <div class="modal fade" id="deleteImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Project Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-project-image.php" method="POST">
          <input type="hidden" name="project_id" id="deleteProjectId">

          <div class="modal-body">
            <div class="text-center py-3">
              <i class="fas fa-exclamation-triangle text-warning fs-48 mb-3"></i>
              <h5>Are you sure you want to delete this image?</h5>
              <p class="text-muted">This action cannot be undone.</p>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" name="delete_project_image">
              <i class="fas fa-trash me-1"></i> Delete Image
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Archive Project Confirmation Modal -->
  <div class="modal fade" id="archiveProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Archive Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-archive-project.php" method="POST">
          <input type="hidden" name="project_id" id="archiveProjectId">

          <div class="modal-body">
            <div class="text-center py-3">
              <h5 class="fs-24">Are you sure you want to archive this project?</h5>
              <p class="text-muted">Archived projects can be restored later.</p>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" name="archive_project">
              <i class="fas fa-box-archive me-1"></i> Archive Project
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Keep all your existing JavaScript functions
    function openUploadModal(projectId) {
      document.getElementById('uploadModalTitle').textContent = 'Upload Project Image';
      document.getElementById('uploadProjectId').value = projectId;
      document.getElementById('projectImageInput').value = '';
      document.getElementById('imagePreview').classList.add('d-none');

      const modal = new bootstrap.Modal(document.getElementById('uploadProjectImageModal'));
      modal.show();
    }

    function openEditImageModal(projectId) {
      event.stopPropagation();
      document.getElementById('uploadModalTitle').textContent = 'Change Project Image';
      document.getElementById('uploadProjectId').value = projectId;
      document.getElementById('projectImageInput').value = '';
      document.getElementById('imagePreview').classList.add('d-none');

      const modal = new bootstrap.Modal(document.getElementById('uploadProjectImageModal'));
      modal.show();
    }

    function deleteProjectImage(projectId) {
      event.stopPropagation();

      if (confirm('Are you sure you want to delete this project image?')) {
        document.getElementById('deleteProjectId').value = projectId;
        const modal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
        modal.show();
      }
    }

    document.getElementById('projectImageInput')?.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 5 * 1024 * 1024) {
          alert('File size must be less than 5MB');
          this.value = '';
          document.getElementById('imagePreview').classList.add('d-none');
          return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
          alert('Please select a valid image file (JPG, PNG, or WEBP)');
          this.value = '';
          document.getElementById('imagePreview').classList.add('d-none');
          return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('previewImage').src = event.target.result;
          document.getElementById('imagePreview').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      } else {
        document.getElementById('imagePreview').classList.add('d-none');
      }
    });

    <?php if (isset($_SESSION['success'])): ?>
      alert('<?= addslashes($_SESSION['success']) ?>');
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      alert('<?= addslashes($_SESSION['error']) ?>');
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    function archiveProject(projectId, projectName) {
      document.getElementById('archiveProjectId').value = projectId;
      const modal = new bootstrap.Modal(document.getElementById('archiveProjectModal'));
      modal.show();
    }
  </script>

</body>

</html>