<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Accounts</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">

    <!-- BACK BUTTON -->
    <a href="admin-manage-accounts.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Accounts</h1>
        <p class="admin-top-desc">View and manage archived user accounts</p>
      </div>
    </div>

    <!-- Account Stats -->
    <div class="row g-3 mb-2">

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Accounts</p>
            <p class="mb-0 fs-24 mobile-fs-22 fw-bold">25</p>
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
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">21</p>
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
            <p class="mb-0 fs-24 mobile-fs-22 text-warning fw-bold">4</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-archive fs-20 text-warning"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- Archived Accounts Table -->
    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">

          <div class="table-responsive bg-white rounded">
            <table id="accountsTable" class="table table-hover mb-0">
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
                <!-- Example Archived Account -->
                <tr class="bg-white">
                  <th scope="row">John Doe</th>
                  <td>john.doe@email.com</td>
                  <td>10/15/2025</td>

                  <?php
                  $status = "Archived";
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

                    <button class="btn btn-sm btn-success text-white">
                      <i class="fa-solid fa-rotate-left me-1"></i> Return
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
          <h4 class="modal-title">Account Details</h4>
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
              <option value="Archived" selected>Archived</option>
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
    $('#accountsTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [{
        orderable: false,
        targets: [4] // Actions column cannot be sorted
      }]
    });
  });
</script>

</html>