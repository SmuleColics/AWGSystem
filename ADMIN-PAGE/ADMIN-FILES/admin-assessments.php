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
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />
  <style>
    .sidebar-content-item:nth-child(4) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(4) .sidebar-anchor,
    .sidebar-content-item:nth-child(4) .sidebar-anchor span {
      color: #16A249 !important;

    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center gap-4">

      <div>
        <h1 class="fs-36 mobile-fs-32">Assessments Request</h1>
        <p class="admin-top-desc">Manage customer assessment requests and create quotations</p>
      </div>
      <div class="d-flex gap-2 flex-column flex-md-row">
        <a hre="#" class="btn btn-danger border d-flex align-items-center ">
          <i class="fas fa-times-circle me-1 d-none d-md-block"></i>
          Rejected <span class="d-none d-md-block ms-1">Assessments</span>
        </a>
        <a href="#" class="btn btn-green d-flex align-items-center ">
          <i class="fas fa-check-circle me-1 d-none d-md-block"></i>
          Accepted <span class="d-none d-md-block ms-1">Assessments</span>
        </a>
      </div>


    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Assessments Requests</h2>


          </div>
          <div class="px-4 pb-4 d-flex flex-column gap-4">
            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h3 class="fs-18 mb-0">
                    John Doe
                    <span class="fs-14 light-text">(johndoe@gmail.com)</span>
                  </h3>
                  <?php
                  $taskStatus = "";

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
                    <p class="fs-14 mb-2">
                      <span class="light-text">Payment Method: </span>
                      Cash (on Site)
                    </p>

                  </div>
                </div>


              </div>
              <div class="assessment-actions d-flex flex-column gap-2">
                <div class="btn btn-green flex">
                  <i class="fas fa-check-circle me-1"></i>
                  Accept
                </div>
                <div class="btn btn-danger border flex ">
                  <i class="fas fa-times-circle me-1"></i>
                  Reject
                </div>
              </div>
            </div>

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h3 class="fs-18 mb-0">
                    Lenard Colico
                    <span class="fs-14 light-text">(johndoe@gmail.com)</span>
                  </h3>
                  <?php
                  $taskStatus = "Pending"; // example, dynamic later

                  $taskStatusClass = match ($taskStatus) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "In Progress"  => "badge-pill taskstatus-inprogress",
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
                      GMA, Cavite
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
                      $50,000
                    </p>
                  </div>
                </div>

                <p class="fs-14 mb-0">
                  <span class="light-text">Notes: </span><BR />
                  Need security camera installation for office building
                </p>
              </div>
              <div class="assessment-actions d-flex flex-column gap-2">
                <a href="admin-quotation-proposal.php" class="btn btn-green flex">
                  <i class="fas fa-file-invoice me-1"></i>
                  Create Quotation
                </a>
                <a href="admin-quotation-proposal.php" class="btn btn-light border flex ">
                  <i class="fas fa-file-invoice me-1"></i></i>
                  Manage Quotation
                </a>
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
                  $userStatus = "Accepted";

                  $taskStatusClass = match ($taskStatus) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "Completed"    => "badge-pill taskstatus-completed",
                    default        => "badge-pill"
                  };

                  $userApprovalClass = match ($userStatus) {
                    "Rejected"      => "badge-pill priority-high",
                    "Waiting"      => "badge-pill taskstatus-pending",
                    "Accepted"    => "badge-pill taskstatus-completed",
                    default        => "badge-pill"
                  };
                  ?>

                  <span class="<?= $taskStatusClass ?>">Quotation <?= $taskStatus ?></span>
                  <!-- <span class="<?= $userApprovalClass ?>">Client <?= $userStatus ?></span> -->

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

                <p class="fs-14 mb-0">
                  <span class="light-text">Notes: </span><BR />
                  Need security camera installation for office building
                </p>
              </div>
              <div class="assessment-actions d-flex flex-column gap-2">
                <a href="admin-quotations.php" class="btn btn-green flex">
                  <i class="fas fa-check-circle me-1"></i>
                  View Quotation
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>


  </main>
  <!-- END OF MAIN -->

</body>





</html>