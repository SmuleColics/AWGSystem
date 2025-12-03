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
  <main id="main" class="container-xxl text-dark px-4">
    <!-- BACK BUTTON -->
<<<<<<< HEAD
    <a href="admin-assessments.php " class="btn btn-outline-secondary mb-4" style="margin-top: 42px;">
=======
    <a href="admin-assessments.php " class="btn btn-outline-secondary mb-2" style="margin-top: 42px;">
>>>>>>> newbranch
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="pb-3 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Quotation Proposal</h1>
        <p class="admin-top-desc">Handle customer assessment requests and generate quotation proposals.</p>
      </div>

    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
<<<<<<< HEAD
        <div class="assessment-details rounded-3 bg-white mb-4">
=======
        <!-- <div class="assessment-details rounded-3 bg-white mb-4">
>>>>>>> newbranch

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top">
                  <h2 class="fs-24 mobile-fs-22 mb-0">Assessment Details</h2>
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
<<<<<<< HEAD
        </div>

        <!-- BAKA GAWIN TONG ADD PROJECT MODAL -->
        <!-- <div class="project-details rounded-3 bg-white mb-3">
=======
        </div> -->

        <div class="project-details rounded-3 bg-white mb-3">
>>>>>>> newbranch

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top">
                  <h2 class="fs-24 mb-0">Project Details</h2>
                  <p class="lightt-text fs-14">Set the project parameters</p>
                </div>
                <div class="row mt-1">
                  <div class="col-6">
                    <label for="projectName" class="form-label">Project Name</label>
                    <input type="text" class="form-control" id="projectName" placeholder="CCTV Installation for Office">
                  </div>
                  <div class="col-6">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" class="form-select" required>
                      <option selected>CCTV Project</option>
                      <option>Solar Project</option>
                      <option>Room Renovation</option>
                    </select>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-4">
<<<<<<< HEAD
                    <label for="budget" class="form-label">Budget (PHP)</label>
                    <input id="budget" type="number" class="form-control" placeholder="0.00">
=======
                    <label for="estimated-cost" class="form-label">Estimated Cost:</label>
                    <!-- AUTO FILL KAPAG NA COMPUTE YUNG QUOTATION -->
                    <input id="estimated-cost" type="number" class="form-control" placeholder="0.00" readonly>
>>>>>>> newbranch
                  </div>
                  <div class="col-4">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input id="startDate" type="date" class="form-control">
                  </div>
                  <div class="col-4">
                    <label for="endDate" class="form-label">End Date</label>
                    <input id="endDate" type="date" class="form-control">
                  </div>
                </div>
                <div class="my-3">
                  <label for="notes" class="form-label">Notes</label>
                  <textarea class="form-control" id="notes" rows="3" placeholder="Add any additional project details here..."></textarea>
                </div>
              </div>
            </div>

          </div>
<<<<<<< HEAD
        </div> -->
=======
        </div>
>>>>>>> newbranch

        <div class="added-items rounded-3 bg-white mb-3">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con border p-3 rounded-3 gap-4">
              <!-- QUOTATION TOP -->
              <div class="w-100">
                <div class="assessment-top d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="fs-24 mobile-fs-22 mb-0">Quotation Items</h2>
                    <p class="light-text fs-14">Review the quotation items</p>
                  </div>

                  <div class="d-flex flex-column flex-md-row gap-2">
                    <!-- TRIGGER MODALS -->
                    <button class="btn btn-green me-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
                      <i class="fas fa-plus d-none d-md-block me-1"></i>
                      Add Item
                    </button>

                    <button class="btn btn-green d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addLaborModal">
                      <i class="fas fa-plus d-none d-md-block me-1"></i>
                      Add Labor
                    </button>
                  </div>

                </div>
              </div>
              <!-- QUOTATION ITEMS -->
              <div class="overflow-auto quotation-items-list">

                <!-- ITEM ROW -->
                <div class="row overflow-x-auto quotation-row mt-3 border rounded-3 p-2 mx-0">
                  <div class="col-4">
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
                  <div class="col-1 flex">
                    <a><i class="fas fa-edit"></i></a>
                  </div>
                  <div class="col-1 flex">
<<<<<<< HEAD
                    <a><i class="fas fa-trash text-danger"></i></a>
=======
                    <a data-bs-toggle="modal" data-bs-target="#deleteItemModal"><i class="fas fa-trash text-danger"></i></a>
>>>>>>> newbranch
                  </div>
                </div>

                <!-- LABOR ROW -->
                <div class="row quotation-row mt-3 border rounded-3 p-2 mx-0">
                  <div class="col-8">
                    <span class="fs-14 light-text">Labor & Maintenance: </span>
                    <p class="mb-2">Labor, Maintenance, and Related Services</p>
                  </div>
                  <div class="col-2">
                    <span class="fs-14 light-text">Total: </span>
                    <p class="mb-2">$1000.00</p>
                  </div>
                  <div class="col-1 flex">
                    <a><i class="fas fa-edit"></i></a>
                  </div>
                  <div class="col-1 flex">
<<<<<<< HEAD
                    <a><i class="fas fa-trash text-danger"></i></a>
=======
                    <a data-bs-toggle="modal" data-bs-target="#deleteItemModal"><i class="fas fa-trash text-danger"></i></a>
>>>>>>> newbranch
                  </div>
                </div>

              </div>

              <div class="divider my-3"></div>
              <div class="d-flex justify-content-between align-items-center">
                <p class="fs-18 fw-semibold mb-0">Total Quotation Amount:</p>
                <p class="fs-24 green-text fw-bold mb-0">$100.00</p>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-end">
<<<<<<< HEAD
              <div class="btn btn-green">Confirm</div>
=======
              <div class="btn btn-green">
                Create Quotation</div>
>>>>>>> newbranch
            </div>
          </div>

        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- ADD ITEM MODAL -->
  <div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

<<<<<<< HEAD
        <div class="modal-body">

          <div class="row g-3">
            <div class="col-6">
=======
        <div class="modal-body overflow-x-auto">

          <div class="row g-3">

            <!-- Item Name -->
            <div class="col-5">
>>>>>>> newbranch
              <label class="form-label">Item Name</label>
              <input type="text" class="form-control" placeholder="4mp CCTV Camera">
            </div>

<<<<<<< HEAD
            <div class="col-3">
=======
            <!-- Quantity -->
            <div class="col-2">
>>>>>>> newbranch
              <label class="form-label">Quantity</label>
              <input type="number" class="form-control" placeholder="1">
            </div>

<<<<<<< HEAD
            <div class="col-3">
              <label class="form-label">Unit Price (PHP)</label>
              <input type="number" class="form-control" placeholder="0.00">
            </div>
=======
            <!-- Unit Type  -->
            <div class="col-3">
              <label class="form-label">Unit Type</label>
              <select class="form-select">
                <option selected disabled>Select unit</option>
                <option value="piece">Piece</option>
                <option value="roll">Roll</option>
                <option value="unit">Unit</option>
                <option value="box">Box</option>
                <option value="pack">Pack</option>
                <option value="set">Set</option>
              </select>
            </div>

            <!-- Unit Price -->
            <div class="col-2">
              <label class="form-label text-nowrap">Unit Price</label>
              <input type="number" class="form-control" placeholder="₱100">
            </div>

>>>>>>> newbranch
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-green">Add Item</button>
        </div>

      </div>
    </div>
  </div>

<<<<<<< HEAD
  <!-- ADD LABOR MODAL -->
  <div class="modal fade" id="addLaborModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
=======

  <!-- ADD LABOR MODAL -->
  <div class="modal fade" id="addLaborModal" tabindex="-1">
    <div class="modal-dialog ">
>>>>>>> newbranch
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add Labor / Additional Charges</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row g-3">
            <div class="col-8">
              <label class="form-label">Labor / Service Description</label>
              <input type="text" class="form-control" placeholder="Labor, Maintenance, Installation Fee">
            </div>

            <div class="col-4">
              <label class="form-label">Amount (PHP)</label>
              <input type="number" class="form-control" placeholder="0.00">
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-green">Add Labor</button>
        </div>

      </div>
    </div>
  </div>

<<<<<<< HEAD
=======
  <!-- ========== ARE YOU SURE YOU WANT TO DELETE MODAL ========== -->
  <div class="modal fade" id="deleteItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5 db-text-sec" id="staticBackdropLabel">Delete Quotation Item</h1>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="post">
          <input type="hidden" name="delete-id" id="delete-id">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to Delete this item?</h3>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="modal-restore-button" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
>>>>>>> newbranch

</body>

</html>