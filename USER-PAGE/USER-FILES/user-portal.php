<!-- Bootstrap-based Project Details Page (No React, No Tailwind, Pure HTML/CSS/Bootstrap) -->

<?php
include 'user-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Project Details</title>


  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .nav-portal {
      color: #fff !important;
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

        <div class="card mb-4 p-4">
          <div class="card-body">

            <!-- TOP ROW -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
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


            </div>

            <!-- PROJECT TITLE -->
            <h3 class="fw-bold mb-3">Project Name Here</h3>

            <!-- DESCRIPTION -->
            <h6 class="fw-semibold">Description</h6>
            <p class="text-secondary">This is where the project description will appear.</p>

            <!-- DETAILS GRID -->
            <div class="row g-3 mt-4">
              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-user text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Client</p>
                  <p class="fw-semibold mb-0">Juan Dela Cruz</p>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-location-dot text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Location</p>
                  <p class="fw-semibold mb-0">Tagaytay City</p>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-calendar text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Duration</p>
                  <p class="fw-semibold mb-0">Jan 12 – Feb 28, 2024</p>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-dollar-sign text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Budget</p>
                  <p class="fw-semibold mb-0">₱150,000</p>
                </div>
              </div>
            </div>
            <div class="d-grid mt-4">
              <a href="#" class="btn btn-green">View Project Details and Monitoring</a>
            </div>
          </div>
        </div>


        <!-- RECENT UPDATES -->
        <div class="card border mb-4">
          <div class="card-header d-flex align-items-center bg-white">
            <i class="fas fa-bell me-2"></i>
            <h5 class="mb-0">Recent Updates</h5>
          </div>

          <div class="card-body">

            <div class="d-flex flex-column gap-3">

              <!-- Update Item -->
              <div class="card border">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <p class="fw-semibold mb-1">Solar Installation</p>

                    <?php
                    $status = "In Progress"; // Example status
                    $class = match ($status) {
                      "Completed"   => "status-badge taskstatus-completed",
                      "In Progress"  => "status-badge taskstatus-pending",
                      default    => "status-badge"
                    };
                    ?>
                    <span class="<?= $class ?>"><?= $status ?></span>
                  </div>

                  <p class="text-muted small mb-1">The installation of solar panels has begun.</p>
                  <p class="text-muted small mb-0">January 10, 2024 – 3:45 PM</p>
                </div>
              </div>

              <!-- Update Item -->
              <div class="card border">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <p class="fw-semibold mb-1">CCTV Checkup</p>

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

                  <p class="text-muted small mb-1">Assessment completed successfully.</p>
                  <p class="text-muted small mb-0">January 09, 2024 – 2:10 PM</p>
                </div>
              </div>

            </div>

          </div>
        </div>

      </div>

      <!-- RIGHT SIDE — PROJECT SUMMARY LIST -->
      <div class="col-lg-4">
        <div class="card shadow-sm position-sticky" style="top: 90px; max-height: 65vh;">
          <div class="card-header bg-white">
            <h5 class="fw-semibold mb-0">My Assessments</h5>
          </div>

          <div class="card-body overflow-y-auto">

            <!-- PROJECT SUMMARY CARD (Repeatable) -->
            <div class="p-3 border rounded mb-3">
              <h6 class="fw-bold mb-1">Solar Installation</h6>
              <p class="light-text small mb-2">
                A solar panel system installation for residential property.
              </p>

              <p class="light-text small mb-1"><strong>Service:</strong> Solar Installation</p>
              <p class="light-text small mb-2"><strong>Estimated Budget:</strong> ₱150,000</p>

              <button class="btn btn btn-light border w-100 small">
                View Assessment Details
              </button>
            </div>

            <!-- DUPLICATE BELOW FOR MORE PROJECTS -->
            <div class="p-3 border rounded mb-3">
              <h6 class="fw-bold mb-1">CCTV Assessment</h6>
              <p class="light-text small mb-2">
                Assessment of CCTV installation for office security.
              </p>

              <p class="light-text small mb-1"><strong>Service:</strong> CCTV Assessment</p>
              <p class="light-text small mb-2"><strong>Estimated Budget:</strong> ₱20,000</p>

              <button class="btn btn btn-light border w-100 small">
                View Assessment Details
              </button>
            </div>

            <!-- Another Example -->
            <div class="p-3 border rounded mb-3">
              <h6 class="fw-bold mb-1">Network Cabling</h6>
              <p class="light-text small mb-2">
                Installation of structured network cabling for office.
              </p>

              <p class="light-text small mb-1"><strong>Service:</strong> Network Setup</p>
              <p class="light-text small mb-2"><strong>Estimated Budget:</strong> ₱45,000</p>

            </div>
          </div>
          <div class="card-footer bg-white">
            <a href="#" class="btn btn-green w-100 mb-2">
              <i class="fas fa-eye me-1"></i>View All Assessment
            </a>
            <a href="#" class="btn btn-green w-100">
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