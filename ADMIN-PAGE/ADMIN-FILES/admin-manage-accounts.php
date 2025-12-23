<?php
ob_start();
include 'admin-header.php';

if (isset($_POST['modal-archive-button'])) {

  if (!isset($is_admin) || !$is_admin) {
    echo "<script>alert('You do not have permission to archive accounts.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $archive_id = (int)$_POST['archive_id'];

  // Get user info before archiving (for message / logging if available)
  $user_info_query = "SELECT first_name, last_name FROM users WHERE user_id = $archive_id";
  $user_info_result = $conn->query($user_info_query);
  $user_info = $user_info_result ? $user_info_result->fetch_assoc() : null;
  $user_name = $user_info ? ($user_info['first_name'] . ' ' . $user_info['last_name']) : '';

  $sql = "UPDATE users SET is_archived = 1 WHERE user_id = $archive_id";
  if ($conn->query($sql)) {
    // Optionally log activity if log_activity exists
    if (function_exists('log_activity') && isset($employee_id, $employee_full_name)) {
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'ARCHIVE',
        'USERS',
        $archive_id,
        $user_name,
        "Archived user account '$user_name'"
      );
    }

    echo "<script>alert('Account archived successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error archiving account: " . addslashes($conn->error) . "');</script>";
  }
}

// Fetch account statistics
$total_query = "SELECT COUNT(*) as total FROM users";
$active_query = "SELECT COUNT(*) as active FROM users WHERE is_archived = 0";
$archived_query = "SELECT COUNT(*) as archived FROM users WHERE is_archived = 1";

$total_result = $conn->query($total_query);
$active_result = $conn->query($active_query);
$archived_result = $conn->query($archived_query);

$total_accounts = $total_result->fetch_assoc()['total'];
$active_accounts = $active_result->fetch_assoc()['active'];
$archived_accounts = $archived_result->fetch_assoc()['archived'];

// Fetch all active accounts
$accounts_query = "SELECT * FROM users WHERE is_archived = 0 ORDER BY created_at DESC";
$accounts_result = $conn->query($accounts_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=1, initial-scale=1.0" />
  <title>Manage Accounts</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  
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
          <i class="fa-solid fa-box-archive me-1"></i> Archived Accounts
        </a>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Accounts</p>
            <p class="mb-0 fs-24 mobile-fs-22 fw-bold"><?= $total_accounts ?></p>
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
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold"><?= $active_accounts ?></p>
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
            <p class="mb-0 fs-24 mobile-fs-22 text-danger fw-bold"><?= $archived_accounts ?></p>
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

          <?php if ($accounts_result->num_rows > 0): ?>
            <div class="table-responsive bg-white rounded">
              <table id="messagesTable" class="table table-hover mb-0">
                <thead>
                  <tr class="bg-white">
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <?php while ($account = $accounts_result->fetch_assoc()): ?>
                    <?php
                    // Format the created_at date
                    $date_created = date('m/d/Y', strtotime($account['created_at']));
                    $status = $account['is_archived'] == 0 ? 'Active' : 'Archived';
                    $class = $status == 'Active' ? 'status-badge taskstatus-completed' : 'status-badge taskstatus-pending';
                    $full_name = htmlspecialchars($account['first_name'] . ' ' . $account['last_name']);
                    ?>
                    <tr class="bg-white">
                      <th scope="row"><?= $full_name ?></th>
                      <td><?= htmlspecialchars($account['email']) ?></td>
                      <td><?= htmlspecialchars($account['phone']) ?></td>
                      <td><?= $date_created ?></td>
                      <td><span class="<?= $class ?>"><?= $status ?></span></td>

                      <td>
                        <button class="btn btn-sm btn-light view-account-btn"
                                data-id="<?= $account['user_id'] ?>"
                                data-firstname="<?= htmlspecialchars($account['first_name']) ?>"
                                data-lastname="<?= htmlspecialchars($account['last_name']) ?>"
                                data-email="<?= htmlspecialchars($account['email']) ?>"
                                data-phone="<?= htmlspecialchars($account['phone']) ?>"
                                data-house="<?= htmlspecialchars($account['house_no'] ?? '') ?>"
                                data-brgy="<?= htmlspecialchars($account['brgy'] ?? '') ?>"
                                data-city="<?= htmlspecialchars($account['city'] ?? '') ?>"
                                data-province="<?= htmlspecialchars($account['province'] ?? '') ?>"
                                data-zip="<?= htmlspecialchars($account['zip_code'] ?? '') ?>"
                                data-created="<?= $date_created ?>"
                                data-status="<?= $status ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#viewAccountModal">
                          <i class="fa-solid fa-eye me-1"></i> View
                        </button>

                        <!-- Archive button now opens a modal and submits via POST (no JSON/AJAX) -->
                        <button class="btn btn-sm btn-danger text-white archive-account-btn"
                                data-id="<?= $account['user_id'] ?>"
                                data-name="<?= $full_name ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#archiveAccountModal">
                          <i class="fa-solid fa-box-archive me-1"></i> Archive
                        </button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>

              </table>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <i class="fa-solid fa-users fs-48 text-muted mb-3"></i>
              <h4 class="text-muted">No Active Accounts</h4>
              <p class="text-muted">There are no active user accounts at the moment.</p>
            </div>
          <?php endif; ?>

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
              <p id="modal-name"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Phone Number</p>
              <p id="modal-phone"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Email</p>
              <p id="modal-email"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Date Created</p>
              <p id="modal-created"></p>
            </div>

            <div class="col-md-12">
              <p class="light-text mb-1">Street Name, Bldg, House No</p>
              <p id="modal-house"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">Barangay</p>
              <p id="modal-brgy"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">City</p>
              <p id="modal-city"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">Province</p>
              <p id="modal-province"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Zip Code</p>
              <p id="modal-zip"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Status</p>
              <p id="modal-status"></p>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>

  <!-- ARCHIVE ACCOUNT MODAL -->
  <div class="modal fade" id="archiveAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Archive Account</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="archive_id" id="archiveAccountId" value="">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this account?</h3>
            <p class="text-center text-muted">Archived accounts can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-archive-button" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery & DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

<script>
  $(document).ready(function() {
    // Only initialize DataTable if table has data
    <?php if ($accounts_result->num_rows > 0): ?>
    $('#messagesTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      order: [[3, 'desc']], // Sort by Date Created column (index 3) descending
      columnDefs: [{
        orderable: false,
        targets: [5] // Action buttons cannot be sorted
      }]
    });
    <?php endif; ?>

    // View Account Modal
    $('.view-account-btn').on('click', function() {
      const firstname = $(this).data('firstname');
      const lastname = $(this).data('lastname');
      const email = $(this).data('email');
      const phone = $(this).data('phone');
      const house = $(this).data('house');
      const brgy = $(this).data('brgy');
      const city = $(this).data('city');
      const province = $(this).data('province');
      const zip = $(this).data('zip');
      const created = $(this).data('created');
      const status = $(this).data('status');

      $('#modal-name').text(firstname + ' ' + lastname);
      $('#modal-email').text(email);
      $('#modal-phone').text(phone);
      $('#modal-house').text(house || 'N/A');
      $('#modal-brgy').text(brgy || 'N/A');
      $('#modal-city').text(city || 'N/A');
      $('#modal-province').text(province || 'N/A');
      $('#modal-zip').text(zip || 'N/A');
      $('#modal-created').text(created);
      $('#modal-status').text(status);
    });

    $('.archive-account-btn').on('click', function() {
      const userId = $(this).data('id');
      const userName = $(this).data('name');

      $('#archiveAccountId').val(userId);
    });
  });
</script>

</html>