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
<<<<<<< HEAD
  <main id="main" class="container-xxl text-dark px-4">
=======
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
>>>>>>> newbranch
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Support Messages</h1>
        <p class="admin-top-desc">View and manage customer support messages</p>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Messages</p>
            <p class="mb-0 fs-24 mobile-fs-22 fw-bold">5</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-envelope fs-20"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Pending</p>
            <p class="mb-0 fs-24 mobile-fs-22 text-warning fw-bold">4</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-hourglass-half fs-20 text-warning"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">In Progress</p>
            <p class="mb-0 fs-24 mobile-fs-22 text-info fw-bold">2</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-spinner fs-20 text-info"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Resolved</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">2</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-check-circle fs-20 green-text"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <!-- Date Filter -->

          <div class="table-responsive bg-white rounded ">
            <table id="messagesTable" class="table table-hover mb-0">
              <thead>
                <tr class="bg-white">
                  <th>Name</th>
                  <th>Email</th>
                  <th>Subject</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                <!-- Employee 1 -->
                <tr class="bg-white">
                  <th scope="row">Maria Santos</th>
                  <td>maria.santos@email.com</td>
                  <td class="text-truncate" >Good day! I would like to inquire about the cost and process of installing solar panels for a residential property. My house has approximately 150 square meters of roof space. Can you provide an estimate?</td>
                  <td>11/21/2025</td>


                  <?php
                  $status = "Completed";
                  $class = match ($status) {
                    "Completed" => "status-badge taskstatus-completed",
                    "Pending"   => "status-badge taskstatus-pending",
                    default     => "status-badge"
                  };
                  ?>

                  <td><span class="<?= $class ?>"><?= $status ?></span></td>

                  <td>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#viewMessagesModal">
                      <i class="fa-solid fa-tasks me-1"></i> Manage
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

  <!-- Message Detail Modal -->
  <div class="modal fade" id="viewMessagesModal" tabindex="-1" aria-labelledby="messageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="messageDetailModalLabel">Message Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <p class="light-text mb-1">From:</p>
            <p id="modal-sender">Maria Santos (maria.santos@email.com)</p>
          </div>
          <div class="mb-3">
            <p class="light-text mb-1">Message:</p>
            <p id="modal-subject">Good day! I would like to inquire about the cost and process of installing solar panels for a residential property. My house has approximately 150 square meters of roof space. Can you provide an estimate?</p>
          </div>
          <div class="mb-3">
            <div class="row">
              <div class="col-6">
                <p class="light-text mb-1">Date:</p>
                <p id="modal-date">11/21/2025</p>
              </div>
              <div class="col-6">
                <label for="modal-status-select" class="form-label light-text">Current Status</label>
                <select id="modal-status-select" class="form-select">
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                </select>
              </div>
            </div>

          </div>

        </div>
        <div class="modal-footer">
          <span id="status-update-feedback" class="text-success me-auto" style="display:none;"></span>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="saveStatusButton">Save Status</button>

        </div>
      </div>
    </div>
  </div>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <!-- jQuery and DataTables JS -->
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
        targets: [5] // Disable sorting for the Actions column (0-based index)
      }]
    });
  });
</script>





</html>