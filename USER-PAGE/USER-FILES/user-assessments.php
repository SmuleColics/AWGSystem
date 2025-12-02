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
    .nav-assessment {
      color: #fff !important;
    }
  </style>
</head>

<body class="bg-light">
  <main class="container-xxl text-dark px-4">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center gap-4">
      <div>
        <h1 class="fs-36 mobile-fs-32">Assessments Request</h1>
        <p class="admin-top-desc">View the status of your assessment requests and updates</p>
      </div>
      <div>
        <a href="request-assessment.php" class="btn green-bg text-white add-item-btn">
          <i class="fa-solid fa-calendar-check me-1"></i> Schedule Assessment
        </a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4">
            <h2 class="fs-24 mb-0">All Assessments Requests</h2>
          </div>
          <div class="px-4 pb-4 d-flex flex-column gap-4">

            <div class="assessment-con row align-items-center border p-3 rounded-3 g-3">

              <div class="col-12 col-md">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h3 class="fs-18 mb-0">
                    Lenard Colico
                    <span class="fs-14 light-text">(johndoe@gmail.com)</span>
                  </h3>
                  <?php
                  $taskStatus = "Rejected";

                  $taskStatusClass = match ($taskStatus) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "Completed"    => "badge-pill taskstatus-completed",
                    "Rejected"     => "badge-pill priority-high",
                    default        => "badge-pill"
                  };
                  ?>
                  <span class="<?= $taskStatusClass ?>"><?= $taskStatus ?></span>
                </div>

                <div class="row mt-1">
                  <div class="col-6">
                    <p class="fs-14 mb-2"><span class="light-text">Service: </span> CCTV Assessment</p>
                    <p class="fs-14 mb-2"><span class="light-text">Time: </span> Morning</p>
                    <p class="fs-14 mb-2"><span class="light-text">Location: </span> GMA, Cavite</p>
                    <p class="fs-14 mb-0">
                      <span class="light-text">Notes: </span><br />
                      Need security camera installation for office building
                    </p>
                  </div>
                  <div class="col-6">
                    <p class="fs-14 mb-2"><span class="light-text">Date: </span> 11/25/2025</p>
                    <p class="fs-14 mb-2"><span class="light-text">Phone: </span> 09171234567</p>
                    <p class="fs-14 mb-2"><span class="light-text">Estimated Budget: </span> $50,000</p>
                    <p class="fs-14 mb-0">
                    <span class="light-text">Rejection Reason: </span><br />
                    Place too far away
                  </p>
                  </div>
                </div>


              </div>

              <div class="assessment-actions col-12 col-md-auto d-flex flex-column gap-2">
                <!-- <a href="admin-quotation-proposal.php" class="btn btn-green flex w-100 text-center">
                  <i class="fas fa-file-invoice me-1"></i> Create Quotation
                </a>
                <a href="admin-quotation-proposal.php" class="btn btn-light border flex w-100 text-center">
                  <i class="fas fa-file-invoice me-1"></i> Manage Quotation
                </a> -->
              </div>
            </div>

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h3 class="fs-18 mb-0">
                    John Doe
                    <span class="fs-14 light-text">(johndoe@gmail.com)</span>
                  </h3>
                  <?php
                  $taskStatus = "Completed";

                  $taskStatusClass = match ($taskStatus) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "Completed"    => "badge-pill taskstatus-completed",
                    default        => "badge-pill"
                  };

                  ?>

                  <span class="<?= $taskStatusClass ?>"><?= $taskStatus ?></span>


                </div>
                <div class="row mt-1">
                  <div class="col-6">
                    <p class="fs-14 mb-2">
                      <span class="light-text">Service: </span>
                      CCTV Assessment
                    </p>
                    <p class="fs-14 mb-2">
                      <span class="light-text">Time: </span>
                      Morning
                    </p>
                    <p class="fs-14 mb-2">
                      <span class="light-text">Location: </span>
                      Makati City, Metro Manila
                    </p>
                    <p class="fs-14 mb-0">
                      <span class="light-text">Notes: </span><BR />
                      Need security camera installation for office building
                    </p>
                  </div>
                  <div class="col-6">
                    <p class="fs-14 mb-2">
                      <span class="light-text">Date: </span>
                      11/25/2025
                    </p>
                    <p class="fs-14 mb-2">
                      <span class="light-text">Phone: </span>
                      09171234567
                    </p>
                    <p class="fs-14 mb-2">
                      <span class="light-text">Estimated Budget: </span>
                      $20,000
                    </p>

                  </div>


                </div>


              </div>
              <div class="d-flex flex-column gap-2">

                <a href="admin-quotations.php" class="btn btn-green flex flex-1 text-nowrap">
                  <i class="fas fa-check-circle me-1"></i>
                  View Quotation
                </a>

                <a href="admin-quotations.php" class="btn btn-green flex flex-1">
                  <i class="fas fa-check-circle me-1"></i>
                  View Project
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>