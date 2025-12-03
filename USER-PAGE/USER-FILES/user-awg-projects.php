<?php
include 'user-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .nav-projects {
      color: #fff !important;
    }
  </style>
</head>

<body class="bg-light">
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4">
    <div class=" d-flex justify-content-between align-items-center pb-0" style="padding-top: 42px;">
      <div>
        <h1 class="fs-36 mobile-fs-32 green-text">A We Green Projects</h1>
        <p class="admin-top-desc mb-0">View accomplished projects of A We Green Enterprise.</p>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5 pt-0 mt-0">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 px-4 pb-4 pt-0">
          <div class="d-flex align-items-center justify-content-between gap-3 mt-3">
            <p class="fs-24 mobile-fs-22 mb-0">All Projects</p>

          </div>
          <div class="divider my-3"></div>

          <div class="project-container d-flex flex-wrap gap-4 justify-content-center">
            <div class="project-cont p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "Completed"; // Example status
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
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
            <div class="project-cont p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "Completed"; // Example status
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
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
            <div class="project-cont p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "Completed"; // Example status
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
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
            <div class="project-cont p-4 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="green-text fw-bold me-2">Solar Installation</span>

                <?php
                $status = "Completed"; // Example status
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
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
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>