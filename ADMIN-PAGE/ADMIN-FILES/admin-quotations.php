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
  <main id="main" class="container-xxl text-dark px-4">
    <!-- BACK BUTTON -->
    <a href="admin-assessments.php " class="btn btn-outline-secondary mb-4" style="margin-top: 42px;">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36">Quotation Proposal</h1>
        <p class="admin-top-desc">View the detailed breakdown of the quotation for this project.</p>
      </div>

    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
        <!-- <div class="assessment-details rounded-3 bg-white mb-4">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top">
                  <h2 class="fs-24 mb-0">Assessment Details</h2>
                  <p class="lightt-text fs-14">Information from the service schedule</p>
                </div>
                <div class="row mt-1">
                  <div class="col-6">
                    <span class="fs-14 light-text">Client Name: </span>
                    <p class="mb-2">Lenard Colico</p>

                    <span class="fs-14 light-text">Email: </span>
                    <p class="mb-2">awegreen@gmail.com</p>

                    <span class="fs-14 light-text">Location: </span>
                    <p class="mb-2">GMA, Cavite</p>

                  </div>
                  <div class="col-6">
                    <span class="fs-14 light-text">Service Type: </span>
                    <p class="mb-2">CCTV Installation</p>

                    <span class="fs-14 light-text">Phone: </span>
                    <p class="mb-2">09123456789</p>

                    <span class="fs-14 light-text">Preferred Date: </span>
                    <p class="mb-2">12/3/2025</p>

                  </div>
                </div>

                <p class="fs-14 mb-0">
                  <span class="light-text">Notes: </span><BR />
                  Need security camera installation for office building
                </p>
              </div>
            </div>

          </div>
        </div> -->
        <div class="project-details rounded-3 bg-white mb-3">
          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top">
                  <h2 class="fs-24 mb-0">Project Details</h2>
                  <p class="lightt-text fs-14">Detailed project information</p>
                </div>

                <div class="row mt-1">
                  <div class="col-6">
                    <label class="form-label">Project Name</label>
                    <p class="fw-semibold mb-0">CCTV Installation for Office</p>
                  </div>

                  <div class="col-6">
                    <label class="form-label">Category</label>
                    <p class="fw-semibold mb-0">CCTV Project</p>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-4">
                    <label class="form-label">Estimated Cost</label>
                    <p class="fw-semibold mb-0">₱150,000.00</p>
                  </div>

                  <div class="col-4">
                    <label class="form-label">Start Date</label>
                    <p class="fw-semibold mb-0">2025-02-15</p>
                  </div>

                  <div class="col-4">
                    <label class="form-label">End Date</label>
                    <p class="fw-semibold mb-0">2025-03-01</p>
                  </div>
                </div>

                <div class="my-3">
                  <label class="form-label">Notes</label>
                  <p class="mb-0">
                    Installation includes 12 CCTV cameras, wiring, and DVR setup.
                  </p>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="added-items rounded-3 bg-white mb-3">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div>
                  <h2 class="fs-24 mb-0">Quotation</h2>
                  <p class="light-text fs-14">Review the quotation items</p>
                </div>
              </div>
              <div class="row mt-3 border rounded-3 p-2 mx-0">
                <div class="col-6">
                  <span class="fs-14 light-text">Item Name: </span>
                  <p class="mb-2">4mp CCTV Camera</p>
                </div>
                <div class="col-2">
                  <span class="fs-14 light-text">Quantity: </span>
                  <p class="mb-2">1</p>
                </div>
                <div class="col-2">
                  <span class="fs-14 light-text">Unit Price: </span>
                  <p class="mb-2">$100.00</p>
                </div>
                <div class="col-2">
                  <span class="fs-14 light-text">Total: </span>
                  <p class="mb-2">$100.00</p>
                </div>
              </div>
              <div class="row mt-3 border rounded-3 p-2 mx-0">
                <div class="col-10">
                  <span class="fs-14 light-text">Labor & Maintenance: </span>
                  <p class="mb-2">Labor, Maintenance, and Related Services</p>
                </div>
                <div class="col-2">
                  <span class="fs-14 light-text">Total: </span>
                  <p class="mb-2">$1000.00</p>
                </div>
              </div>
              <div class="divider my-3"></div>
              <div class="d-flex justify-content-between align-items-center">
                <p class="fs-18 fw-semibold mb-0">Total Quotation Amount:</p>
                <p class="fs-24 green-text fw-bold mb-0">$100.00</p>
              </div>
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