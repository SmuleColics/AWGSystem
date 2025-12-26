<?php
ob_start();
include 'admin-header.php';

if (isset($_POST['modal-restore-button'])) {
  if (!isset($is_admin) || !$is_admin) {
    echo "<script>alert('You do not have permission to restore accounts.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $restore_id = (int)$_POST['restore_id'];

  $user_info_query = "SELECT first_name, last_name FROM users WHERE user_id = $restore_id";
  $user_info_result = $conn->query($user_info_query);
  $user_info = $user_info_result ? $user_info_result->fetch_assoc() : null;
  $user_name = $user_info ? ($user_info['first_name'] . ' ' . $user_info['last_name']) : '';

  // Restore the account
  $sql = "UPDATE users SET is_archived = 0 WHERE user_id = $restore_id";
  if ($conn->query($sql)) {
    // Optional: log activity if function exists and employee info available
    if (function_exists('log_activity') && isset($employee_id, $employee_full_name)) {
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'RESTORE',
        'USERS',
        $restore_id,
        $user_name,
        "Restored user account '$user_name' from archive"
      );
    }

    echo "<script>alert('Account restored successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error restoring account: " . addslashes($conn->error) . "');</script>";
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

// Fetch all archived accounts
$accounts_query = "SELECT * FROM users WHERE is_archived = 1 ORDER BY created_at DESC";
$accounts_result = $conn->query($accounts_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Accounts</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
            <p class="mb-0 fs-24 mobile-fs-22 text-warning fw-bold"><?= $archived_accounts ?></p>
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

          <?php if ($accounts_result->num_rows > 0): ?>
            <div class="table-responsive bg-white rounded">
              <table id="accountsTable" class="table table-hover mb-0">
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
                    $status = $account['is_archived'] == 1 ? 'Archived' : 'Active';
                    $class = $status == 'Active' ? 'status-badge taskstatus-completed' : 'status-badge priority-high';
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
                        <?php if ($is_admin): ?>
                        <button class="btn btn-sm btn-success text-white restore-account-btn" 
                                data-id="<?= $account['user_id'] ?>"
                                data-name="<?= $full_name ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#restoreAccountModal">
                          <i class="fa-solid fa-rotate-left me-1"></i> Return
                        </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>

              </table>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <i class="fa-solid fa-archive fs-48 text-muted mb-3"></i>
              <h4 class="text-muted">No Archived Accounts</h4>
              <p class="text-muted">All your accounts are active.</p>
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

  <!-- RESTORE ACCOUNT MODAL  -->
  <div class="modal fade" id="restoreAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Account
          </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="restore_id" id="restoreAccountId">
          <div class="modal-body">
            <h3 class="fs-20 text-center m-0 py-3">Are you sure you want to restore this account?</h3>
            <p class="text-center text-muted">The account will be moved back to active accounts.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-restore-button" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery & DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</body>

<script>
  $(document).ready(function() {
    // Only initialize DataTable if table has data
    <?php if ($accounts_result->num_rows > 0): ?>
    $('#accountsTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      order: [[3, 'desc']], // Sort by Date Created column (index 3) descending
      columnDefs: [{
        orderable: false,
        targets: [5] // Actions column cannot be sorted
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

    $('.restore-account-btn').on('click', function() {
      const userId = $(this).data('id');
      const userName = $(this).data('name');

      $('#restoreAccountId').val(userId);
    });

  });
</script>

</html>