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
</head>

<body class="bg-light">

  <!-- START OF MAIN  -->
  <main class="container-xxl text-dark px-4">
    <!-- BACK BUTTON -->
    <a href="user-assessments.php " class="btn btn-outline-secondary mb-4" style="margin-top: 42px;">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="pb-3 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36">Quotation Proposal</h1>
        <p class="admin-top-desc">Handle customer assessment requests and generate quotation proposals.</p>
      </div>

    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
        <div class="assessment-details rounded-3 bg-white mb-4">

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
        </div>

        <div class="added-items rounded-3 bg-white mb-3">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="fs-24 mb-0">Quotation Items</h2>
                    <p class="light-text fs-14">Review the quotation items</p>
                  </div>

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
            <div class="d-flex align-items-center justify-content-end">
              <div>
                <div class="btn btn-danger">Reject</div>
                <div class="btn btn-green">Confirm</div>
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