<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Manage Accounts</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .sidebar-content-item:nth-child(7) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(7) .sidebar-anchor,
    .sidebar-content-item:nth-child(7) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Manage Accounts</h1>
        <p class="admin-top-desc">View, manage, and archive user accounts</p>
      </div>
      <div>
        <a href="admin-archive-accounts.php" class="btn btn-danger text-white">
          <i class="fa-solid fa-box-archive me-1"></i>  Archived Accounts
        </a>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Accounts</p>
            <p class="mb-0 fs-24 mobile-fs-22 fw-bold">1</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-users fs-20"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Active Accounts</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">1</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-user-check fs-20 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Archived Accounts</p>
            <p class="mb-0 fs-24 mobile-fs-22 text-danger fw-bold">0</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-archive fs-20 text-danger"></i>
          </div>
        </div>
      </div>

    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">

          <div class="table-responsive bg-white rounded ">
            <table id="messagesTable" class="table table-hover mb-0">
              <thead>
                <tr class="bg-white">
                  <th>Name</th>
                  <th>Email</th>
                  <th>Date Created</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>

                <!-- Example Account -->
                <tr class="bg-white">
                  <th scope="row">John Doe</th>
                  <td>john.doe@email.com</td>
                  <td>7/15/2025</td>

                  <?php
                  $status = "Active";
                  $class = match ($status) {
                    "Active"   => "status-badge taskstatus-completed",
                    "Archived" => "status-badge taskstatus-pending",
                    default    => "status-badge"
                  };
                  ?>

                  <td><span class="<?= $class ?>"><?= $status ?></span></td>

                  <td>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#viewAccountModal">
                      <i class="fa-solid fa-eye me-1"></i> View
                    </button>

                    <button class="btn btn-sm btn-danger text-white">
                      <i class="fa-solid fa-box-archive me-1"></i> Archive
                    </button>
                  </td>
                </tr>

              </tbody>

            </table>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->


  <!-- Account Detail Modal -->
<div class="modal fade" id="viewAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Account Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- PERSONAL INFO -->
        <h5 class="fw-bold mb-3">Personal Information</h5>

        <div class="row g-3">

          <div class="col-md-6">
            <p class="light-text mb-1">Full Name</p>
            <p id="modal-name">John Doe</p>
          </div>

          <div class="col-md-6">
            <p class="light-text mb-1">Phone Number</p>
            <p id="modal-phone">09123456789</p>
          </div>

          <div class="col-md-6">
            <p class="light-text mb-1">Street Name, Bldg, House No</p>
            <p id="modal-house">Unit 2, ABC Street</p>
          </div>

          <div class="col-md-6">
            <p class="light-text mb-1">Barangay</p>
            <p id="modal-brgy">San Isidro</p>
          </div>

          <div class="col-md-4">
            <p class="light-text mb-1">City</p>
            <p id="modal-city">Carmona</p>
          </div>

          <div class="col-md-4">
            <p class="light-text mb-1">Province</p>
            <p id="modal-province">Cavite</p>
          </div>

          <div class="col-md-4">
            <p class="light-text mb-1">Zip Code</p>
            <p id="modal-zip">4116</p>
          </div>

        </div>

        <hr>

        <!-- ACCOUNT STATUS -->
        <div class="mb-3">
          <label class="form-label light-text">Status</label>
          <select id="modal-status-select" class="form-select">
            <option value="Active">Active</option>
            <option value="Archived">Archived</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success">Save Changes</button>
      </div>

    </div>
  </div>
</div>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <!-- jQuery & DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</body>

<script>
  $(document).ready(function() {
    $('#messagesTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [{
        orderable: false,
        targets: [4] // Action buttons cannot be sorted
      }]
    });
  });
</script>

</html>
