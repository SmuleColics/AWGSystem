<?php
// session_start();
include '../../INCLUDES/db-con.php';

// ========== RESTORE EMPLOYEE (FROM MODAL) ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal-restore-button'])) {
  $employee_id = intval($_POST['restore_id']);
  $sql = "UPDATE employees SET is_archived = 0 WHERE employee_id = $employee_id";

  if (mysqli_query($conn, $sql)) {
    echo "<script>
      alert('Employee restored successfully!');
      window.location='admin-archive-employees.php';
    </script>";
    exit;
  }
}

// ========== GET ARCHIVED EMPLOYEES ========== //
$employees_sql = "SELECT * FROM employees WHERE is_archived = 1 ORDER BY updated_at DESC";
$employees_result = mysqli_query($conn, $employees_sql);
$archived_count = mysqli_num_rows($employees_result);

$admin_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees WHERE is_archived = 1 AND (position LIKE '%Admin%' OR position = 'Admin')");
$total_admins = mysqli_fetch_assoc($admin_query)['total'];

$employee_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees WHERE is_archived = 1 AND position NOT LIKE '%Admin%'");
$total_staff = mysqli_fetch_assoc($employee_query)['total'];

include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archived Employees</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
</head>
<style>
  .sidebar-content-item:nth-child(5) {
    background-color: #f2f2f2 !important;
  }

  .sidebar-content-item:nth-child(5) .sidebar-anchor,
  .sidebar-content-item:nth-child(5) .sidebar-anchor span {
    color: #16A249 !important;
  }
</style>

<body>

  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">
    <a href="admin-employees.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back to Employees
    </a>

    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Employees</h1>
        <p class="admin-top-desc">View and manage archived team members</p>
      </div>
    </div>

    <div class="row g-3 mb-2">
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Archived</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $archived_count ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Administrators</p>
          <p class="fw-bold fs-24 mb-0"><?= $total_admins ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Employees</p>
          <p class="fw-bold fs-24 mb-0"><?= $total_staff ?></p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">Archived Employees</p>
          </div>
          <div class="divider my-3"></div>

          <div class="employee-container d-flex flex-wrap gap-4 justify-content-center">
            <?php if ($archived_count > 0): ?>
              <?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
                <div class="employee-con p-4 border rounded-3 bg-white">
                  <div class="w-100 flex mb-2">
                    <div class="employee-pfp" style="opacity: 0.6;">
                      <span class="pfp"><?= strtoupper(substr($employee['first_name'], 0, 1)) ?></span>
                    </div>
                  </div>
                  <h2 class="fs-18 text-center mb-1">
                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                  </h2>
                  <p class="light-text fs-12 text-center mb-1"><?= htmlspecialchars($employee['position']) ?></p>
                  <p class="light-text fs-14 text-center">
                    <i class="fas fa-envelope me-1"></i>
                    <?= htmlspecialchars($employee['email']) ?>
                  </p>
                  <?php if ($is_super_admin): ?>
                  <div class="emp-btn-con d-grid gap-2">
                    <button onclick="openRestoreModal(<?= $employee['employee_id'] ?>)" class="btn btn-sm btn-success text-nowrap fs-14">
                      <i class="fas fa-undo me-1"></i> Restore
                    </button>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-5 w-100">
                <i class="fas fa-archive fa-3x text-secondary mb-3"></i>
                <p class="text-secondary">No archived employees</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- RESTORE EMPLOYEE MODAL -->
  <div class="modal fade" id="restoreEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="restoreEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="restoreEmployeeLabel">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Employee
          </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="restore_id" id="restoreEmployeeId">
          <div class="modal-body">
            <h3 class="fs-20 text-center m-0 py-3">Are you sure you want to restore this employee?</h3>
            <p class="text-center text-muted mb-0">This employee will be moved back to active employees.</p>
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

</body>

<script>
  // Open restore modal and set employee ID
  function openRestoreModal(employeeId) {
    document.getElementById('restoreEmployeeId').value = employeeId;
    var restoreModal = new bootstrap.Modal(document.getElementById('restoreEmployeeModal'));
    restoreModal.show();
  }
</script>

</html>