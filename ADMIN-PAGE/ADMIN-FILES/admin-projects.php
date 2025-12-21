<?php
ob_start();
include 'admin-header.php';

// Get project statistics
$stats_sql = "SELECT 
    COUNT(*) as total_projects,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress
    FROM projects 
    WHERE is_archived = 0";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// Get all projects with user info
$projects_sql = "SELECT 
    p.project_id,
    p.project_name,
    p.project_type,
    p.location AS project_location,
    p.status,
    p.project_image,
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
WHERE p.is_archived = 0
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
    </div>

    <div class="row g-3 mb-2">
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Projects</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['total_projects'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Completed</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['completed'] ?? 0 ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">In Progress</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $stats['in_progress'] ?? 0 ?></p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">All Projects</p>
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
                      
                      <!-- Image Action Buttons -->
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
                    <?php else: ?>
                      <i class="fa-solid fa-image no-image-placeholder"></i>
                    <?php endif; ?>

                    <div class="upload-overlay" onclick="openUploadModal(<?= $project['project_id'] ?>)">
                      <div class="text-white text-center">
                        <i class="fa-solid fa-camera fs-32 mb-2"></i>
                        <p class="mb-0"><?= !empty($project['project_image']) ? 'Change Image' : 'Upload Image' ?></p>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex fs-14 mb-2 gap-1">
                    <span class="light-text">Client:</span>
                    <span><?= $client_name ?></span>
                  </div>
                  <div class="d-flex gap-1 fs-14 mb-2">
                    <span class="light-text">Location:</span>
                    <span><?= htmlspecialchars($location) ?></span>
                  </div>
                  <div class="d-grid">
                    <a href="admin-projects-detail.php?id=<?= $project['project_id'] ?>"
                      class="btn btn-outline-secondary mt-3">
                      View Details
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-5 w-100">
                <i class="fa-solid fa-folder-open fs-64 text-secondary mb-3"></i>
                <h3 class="fs-24">No Projects Yet</h3>
                <p class="text-muted">Projects will appear here once quotations are approved.</p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

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

            <!-- Image Preview -->
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

  <script>
    // Open upload modal and set project ID
    function openUploadModal(projectId) {
      document.getElementById('uploadModalTitle').textContent = 'Upload Project Image';
      document.getElementById('uploadProjectId').value = projectId;
      document.getElementById('projectImageInput').value = '';
      document.getElementById('imagePreview').classList.add('d-none');

      const modal = new bootstrap.Modal(document.getElementById('uploadProjectImageModal'));
      modal.show();
    }

    // Open edit modal (same as upload but different title)
    function openEditImageModal(projectId) {
      event.stopPropagation(); // Prevent triggering parent click
      document.getElementById('uploadModalTitle').textContent = 'Change Project Image';
      document.getElementById('uploadProjectId').value = projectId;
      document.getElementById('projectImageInput').value = '';
      document.getElementById('imagePreview').classList.add('d-none');

      const modal = new bootstrap.Modal(document.getElementById('uploadProjectImageModal'));
      modal.show();
    }

    // Delete project image
    function deleteProjectImage(projectId) {
      event.stopPropagation(); // Prevent triggering parent click
      
      if (confirm('Are you sure you want to delete this project image?')) {
        document.getElementById('deleteProjectId').value = projectId;
        const modal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
        modal.show();
      }
    }

    // Image preview before upload
    document.getElementById('projectImageInput')?.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('File size must be less than 5MB');
          this.value = '';
          document.getElementById('imagePreview').classList.add('d-none');
          return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
          alert('Please select a valid image file (JPG, PNG, or WEBP)');
          this.value = '';
          document.getElementById('imagePreview').classList.add('d-none');
          return;
        }

        // Show preview
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