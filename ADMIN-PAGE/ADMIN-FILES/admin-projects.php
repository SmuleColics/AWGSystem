<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .sidebar-content-item:nth-child(9) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(9) .sidebar-anchor,
    .sidebar-content-item:nth-child(9) .sidebar-anchor span {
      color: #16A249 !important;

    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
<<<<<<< HEAD
  <main id="main" class="container-xxl text-dark px-4">
=======
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
>>>>>>> newbranch
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
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">5</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Completed</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">1</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">In Progress</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">4</p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">All Projects</p>
            <div>
              <div>
                <button class="btn green-bg text-white add-item-btn" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                  <i class="fa-solid fa-plus me-1"></i> Add Project
                </button>
              </div>
            </div>
          </div>
          <div class="divider my-3"></div>

          <div class="project-container d-flex flex-wrap gap-4 justify-content-center">
            <div class="project-con p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "In Progress"; // Example status
                $class = match ($status) {
                  "Active"   => "status-badge taskstatus-completed",
                  "In Progress"  => "status-badge taskstatus-pending",
                  default    => "status-badge"
                };
                ?>
                <span class="<?= $class ?>"><?= $status ?></span>
              </div>
              <h2 class="fs-20 mb-2">Office CCTV Installation</h2>
              <div class="div mb-3">
                <img class="w-100 img-fluid rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="daily users analytics">
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Client:</span>
                <span>Tech Corp Ltd</span>
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Location:</span>
                <span>Carmona, Cavite</span>
              </div>
              <div class="d-grid">
                <button class="btn btn-outline-secondary mt-3" type="button">View Details</button>
              </div>

            </div>
            <div class="project-con p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "In Progress"; // Example status
                $class = match ($status) {
                  "Active"   => "status-badge taskstatus-completed",
                  "In Progress"  => "status-badge taskstatus-pending",
                  default    => "status-badge"
                };
                ?>
                <span class="<?= $class ?>"><?= $status ?></span>
              </div>
              <h2 class="fs-20 mb-2">Office CCTV Installation</h2>
              <div class="div mb-3">
                <img class="w-100 img-fluid rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="daily users analytics">
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Client:</span>
                <span>Tech Corp Ltd</span>
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Location:</span>
                <span>GMA, Cavite</span>
              </div>
              <div class="d-grid">
                <a href="admin-projects-detail.php"  class="btn btn-outline-secondary mt-3" type="button">View Details</a>
              </div>

            </div>
            <div class="project-con p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "In Progress"; // Example status
                $class = match ($status) {
                  "Active"   => "status-badge taskstatus-completed",
                  "In Progress"  => "status-badge taskstatus-pending",
                  default    => "status-badge"
                };
                ?>
                <span class="<?= $class ?>"><?= $status ?></span>
              </div>
              <h2 class="fs-20 mb-2">Office CCTV Installation</h2>
              <div class="div mb-3">
                <img class="w-100 img-fluid rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="daily users analytics">
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Client:</span>
                <span>Tech Corp Ltd</span>
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Location:</span>
                <span>Carmona, Cavite</span>
              </div>
              <div class="d-grid">
                <button class="btn btn-outline-secondary mt-3" type="button">View Details</button>
              </div>

            </div>
            <div class="project-con p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "In Progress"; // Example status
                $class = match ($status) {
                  "Active"   => "status-badge taskstatus-completed",
                  "In Progress"  => "status-badge taskstatus-pending",
                  default    => "status-badge"
                };
                ?>
                <span class="<?= $class ?>"><?= $status ?></span>
              </div>
              <h2 class="fs-20 mb-2">Office CCTV Installation</h2>
              <div class="div mb-3">
                <img class="w-100 img-fluid rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="daily users analytics">
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Client:</span>
                <span>Tech Corp Ltd</span>
              </div>
              <div class="d-flex justify-content-between fs-14 mb-2">
                <span class="light-text">Location:</span>
                <span>Carmona, Cavite</span>
              </div>
              <div class="d-grid">
                <button class="btn btn-outline-secondary mt-3" type="button">View Details</button>
              </div>

            </div>
            
          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  





</html>