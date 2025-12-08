<!-- Bootstrap-based Project Details Page (No React, No Tailwind, Pure HTML/CSS/Bootstrap) -->

<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Project Details</title>


  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</head>
  <style>
     .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A246 !important;

    }
  </style>

<body class="bg-light">

  <div class="container-xxl px-4 py-5 min-vh-100">

    <!-- BACK BUTTON -->
    <a href="admin-projects.php " class="btn btn-outline-secondary mb-4">
      <i class="fa fa-arrow-left me-2"></i> Back to Projects
    </a>

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

              <!-- VISIBILITY OPTION -->
              <div>
                <select class="form-select form-select-sm">
                  <option selected value="private">Private</option>
                  <option value="public">Public</option>
                </select>
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

            <div class="mt-4">

              <div class="d-flex justify-content-between align-items-center">
                <span class="light-text small">Progress</span>


              </div>

              <div class="d-flex justify-content-between mt-2">
                <span class="fw-semibold">45%</span>
              </div>

              <div class="progress" style="height: 8px;">
                <div class="progress-bar" style="width: 45%; background-color:#16a249;"></div>
              </div>
            </div>

          </div>
        </div>


        <!-- PROJECT UPDATES CARD -->
        <div class="card shadow-sm p-4">
          <div class="card-header mb-4 d-flex justify-content-between align-items-center bg-white px-0">
            <h5 class="mb-0 fw-semibold">Project Updates</h5>
            <button class="btn btn-green btn-sm" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
              <i class="fa fa-plus me-1"></i> Add Update
            </button>
          </div>

          <div class="card-body p-0">
            <!-- Example Update -->
            <div class="p-3 mb-3 rounded border bg-white">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-1 fs-18">Update Title</h6>
                <div class="update-btns">
                  <button class="btn btn-light border btn-sm me-1">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUpdateModal">
                    <i class="fa-solid fa-trash fs-16"></i>
                  </button>
                </div>
              </div>
              <p class="text-secondary small">Description of this update goes here.</p>
              <p class="light-text small">January 10, 2024 – 3:45 PM</p>
              <img src="https://via.placeholder.com/600x300" class="img-fluid rounded border mt-2" />
            </div>

            <p class="light-text small text-center">No updates yet</p>
          </div>
        </div>

      </div>

      <!-- RIGHT SIDE — PAYMENT DETAILS -->
      <div class="col-lg-4">
        <div class="card shadow-sm position-sticky" style="top: 90px;">
          <div class="card-header bg-white">
            <h5 class="fw-semibold mb-0">Payment Details</h5>
          </div>
          <div class="card-body" style="height: 286px; overflow-y: auto;"">

            <!-- TOTAL COST -->
            <div class=" p-3 border rounded mb-3 bg-light">
            <div class="fw-semibold mb-1">Project Cost</div>
            <p class="light-text small mb-0">
              Total Amount: <strong>₱150,000</strong>
            </p>
          </div>

          <!-- AMOUNT PAID -->
          <div class="p-3 border rounded mb-3 bg-light">
            <div class="fw-semibold mb-1">Payment Status</div>
            <p class="light-text small mb-1"><strong>Paid:</strong> ₱80,000 - 1/10/2025 (Gcash)</p>
            
          </div>

          <!-- NOTES -->
          <div class="p-3 border rounded mb-4 bg-light">
            <p class="light-text small mb-0 text-danger">
              <strong>Remaining Balance:</strong> ₱70,000
            </p>
          </div>

        </div>
        <div class="card-footer">
          <div class="d-grid">
            <button class="btn btn-green w-100 mb-2" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
              <i class="fas fa-wallet me-1"></i> Process Payment
            </button>
            <a href="#" class="btn btn-green w-100 mb-2">
              <i class="fas fa-clipboard-list me-1"></i> View Assessment
            </a>
            <a href="#" class="btn btn-green w-100 mb-2">
              <i class="fas fa-file-invoice me-1"></i> View Quotation
            </a>
          </div>
        </div>

      </div>

    </div>


  </div>
  </div>

  <!-- Process Payment Modal -->
  <div class="modal fade" id="processPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Process Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- PAYMENT SUMMARY -->
          <div class="p-3 rounded bg-light mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="light-text small">Total Amount</span>
              <span class="fw-semibold">₱150,000</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="light-text small">Already Paid</span>
              <span class="fw-semibold">₱80,000</span>

            </div>
            <div class="d-flex justify-content-between">
              <span class="light-text small">Remaining Balance</span>
              <span class="fw-bold text-danger">₱70,000</span>
            </div>
          </div>

          <!-- AMOUNT INPUT -->
          <label class="form-label fw-semibold">Enter Payment Amount</label>
          <input type="number" class="form-control mb-3" placeholder="e.g., 5000">

          <label class="form-label fw-semibold">Payment Method</label>
          <div class="mb-3">

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="paymentMethod" value="cash" id="cashRadio">
              <label class="form-check-label" for="cashRadio">Cash</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="paymentMethod" value="gcash" id="gcashRadio">
              <label class="form-check-label" for="gcashRadio">GCash</label>
            </div>

          </div>

          <div id="gcashSection" class="d-none">
            <label class="form-label">GCash Number</label>
            <input type="text" class="form-control mb-2" placeholder="09XX XXX XXXX">

            <label class="form-label">Reference Number</label>
            <input type="text" class="form-control mb-2" placeholder="Enter GCash reference number">
          </div>

          <div id="cashSection" class="alert alert-secondary small d-none">
            Payment will be recorded as cash.
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-green">Confirm Payment</button>
        </div>

      </div>
    </div>
  </div>

  <script>
    // show/hide method fields
    document.querySelectorAll("input[name='paymentMethod']").forEach(radio => {
      radio.addEventListener("change", function() {

        document.getElementById("gcashSection").classList.add("d-none");
        document.getElementById("cashSection").classList.add("d-none");

        if (this.value === "gcash") {
          document.getElementById("gcashSection").classList.remove("d-none");
        } else if (this.value === "cash") {
          document.getElementById("cashSection").classList.remove("d-none");
        }
      });
    });
  </script>



  <!-- ADD UPDATE + EDIT STATUS MODAL -->
  <div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Project Update</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- Update Title -->
          <label class="form-label">Update Title</label>
          <input type="text" placeholder="Enter update title..." class="form-control mb-3" />

          <!-- Update Description -->
          <label class="form-label">Description</label>
          <textarea class="form-control mb-3" rows="4" placeholder="Describe the update..."></textarea>

          <!-- Image Upload -->
          <label class="form-label">Image (Optional)</label>
          <input type="file" class="form-control mb-4" />

          <hr>

          <!-- STATUS + PROGRESS SECTION -->
          <h6 class="fw-semibold mb-3">Update Project Status</h6>

          <!-- Progress Input -->
          <div>
            <label class="form-label">Progress (%)</label>
            <input type="number" min="0" max="100" value="45" class="form-control">
            <small class="text-muted">Enter 0–100</small>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-green">Save Update</button>
        </div>

      </div>
    </div>
  </div>


  <!-- ========== ARE YOU SURE YOU WANT TO DELETE MODAL ========== -->
  <div class="modal fade" id="deleteUpdateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5 db-text-sec" id="staticBackdropLabel">Delete Project Update</h1>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="post">
          <input type="hidden" name="delete-id" id="delete-id">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to Delete this account?</h3>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="modal-restore-button" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>