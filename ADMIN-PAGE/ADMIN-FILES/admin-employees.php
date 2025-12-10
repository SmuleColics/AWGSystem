<?php
ob_start();
include '../../INCLUDES/db-con.php';

$errors = [];
$success = '';

// ========== ADD EMPLOYEE ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $position = $_POST['position'] ?? '';
  $daily_salary = $_POST['daily_salary'] ?? '';
  $date_hired = $_POST['date_hired'] ?? '';

  // ========== VALIDATION ==========
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email is required';
  }

  if (empty($password) || strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters';
  }

  if (empty($first_name)) {
    $errors['first_name'] = 'First name is required';
  }

  if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required';
  }

  if (empty($position)) {
    $errors['position'] = 'Position is required';
  }

  if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
  } elseif (!preg_match('/^\d{11}$/', $phone)) {
    $errors['phone'] = 'Phone number must be exactly 11 digits';
  }

  if ($position !== "Admin" && (empty($daily_salary) || $daily_salary <= 0)) {
    $errors['daily_salary'] = 'Valid daily salary is required';
  }

  if ($position !== "Admin" && empty($date_hired)) {
    $errors['date_hired'] = 'Date hired is required';
  }

  // Check if email exists
  if (empty($errors)) {
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $check_email = mysqli_query($conn, "SELECT employee_id FROM employees WHERE email = '$email_escaped'");
    if (mysqli_num_rows($check_email) > 0) {
      $errors['email'] = 'Email already exists';
    }
  }

  // Insert employee
  if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $first_name_escaped = mysqli_real_escape_string($conn, $first_name);
    $last_name_escaped = mysqli_real_escape_string($conn, $last_name);
    $phone_escaped = mysqli_real_escape_string($conn, $phone);
    $position_escaped = mysqli_real_escape_string($conn, $position);
    $date_hired_escaped = mysqli_real_escape_string($conn, $date_hired);

    $sql = "
            INSERT INTO employees (email, password, first_name, last_name, phone, position, daily_salary, date_hired, is_archived)
            VALUES ('$email_escaped', '$hashed_password', '$first_name_escaped', '$last_name_escaped', '$phone_escaped', '$position_escaped', '$daily_salary', '$date_hired_escaped', 0)
        ";

    if (mysqli_query($conn, $sql)) {
      echo "<script>
                alert('Employee added successfully!');
                window.location='admin-employees.php';
            </script>";
      exit;
    } else {
      $errors['status'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

// ========== ARCHIVE EMPLOYEE (FROM MODAL) ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal-archive-button'])) {
  $employee_id = intval($_POST['archive_id']);
  $sql = "UPDATE employees SET is_archived = 1 WHERE employee_id = $employee_id";
  if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('Employee archived successfully!');
            window.location='admin-employees.php';
        </script>";
    exit;
  }
}

// ========== STATISTICS ==========
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0");
$total_employees = mysqli_fetch_assoc($total_query)['total'];

$admin_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0 AND (position LIKE '%Admin%' OR position = 'Admin')");
$total_admins = mysqli_fetch_assoc($admin_query)['total'];

$employee_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees WHERE is_archived = 0 AND position NOT LIKE '%Admin%'");
$total_staff = mysqli_fetch_assoc($employee_query)['total'];

// ========== ACTIVE EMPLOYEES ==========
$employees_sql = "SELECT * FROM employees WHERE is_archived = 0 ORDER BY created_at DESC";
$employees_result = mysqli_query($conn, $employees_sql);

include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Management</title>
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

  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <!-- Top Section -->
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Employee Management</h1>
        <p class="admin-top-desc">Manage your team members and their roles</p>
      </div>
      <div class="d-flex flex-column flex-md-row gap-2">
        <button class="btn green-bg text-white add-item-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
          <i class="fa-solid fa-plus me-1"></i> Add <span class="d-none d-md-block ms-1">Employees</span>
        </button>
        <a href="admin-archive-employees.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Employees</span>
        </a>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-2">
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Employees</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0"><?= $total_employees ?></p>
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

    <!-- Employees List -->
    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">All Employees</p>
          </div>
          <div class="divider my-3"></div>
          <div class="employee-container d-flex flex-wrap gap-4 justify-content-center">
            <?php if (mysqli_num_rows($employees_result) > 0): ?>
              <?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
                <div class="employee-con p-4 border rounded-3">
                  <div class="w-100 flex mb-2">
                    <div class="employee-pfp">
                      <span class="pfp"><?= strtoupper(substr($employee['first_name'], 0, 1)) ?></span>
                    </div>
                  </div>
                  <h2 class="fs-18 text-center mb-1"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h2>
                  <p class="light-text fs-12 text-center mb-1"><?= htmlspecialchars($employee['position']) ?></p>
                  <p class="light-text fs-14 text-center">
                    <i class="fas fa-envelope me-1"></i>
                    <?= htmlspecialchars($employee['email']) ?>
                  </p>
                  <div class="emp-btn-con d-flex w-100 gap-2">
                    <a href="admin-employee-profile.php?id=<?= $employee['employee_id'] ?>" class="btn btn-sm btn-outline-secondary text-nowrap fs-14 w-50">View Profile</a>
                    <button onclick="openArchiveModal(<?= $employee['employee_id'] ?>)" class="btn btn-sm btn-danger text-nowrap fs-14 w-50">Archive</button>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-5 w-100">
                <i class="fas fa-users fa-3x text-secondary mb-3"></i>
                <p class="text-secondary">No employees found</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- ADD EMPLOYEE MODAL -->
  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" id="addEmployeeForm">
          <div class="modal-body">

            <!-- LOGIN INFO -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5>Login Information</h5>
              <div>
                <select name="position" class="form-select" id="positionSelect">
                  <option selected disabled>Select Position</option>
                  <option value="Driver" <?= ($_POST['position'] ?? '') === 'Driver' ? 'selected' : '' ?>>Driver</option>
                  <option value="Technician" <?= ($_POST['position'] ?? '') === 'Technician' ? 'selected' : '' ?>>Technician</option>
                  <option value="Driver/Technician" <?= ($_POST['position'] ?? '') === 'Driver/Technician' ? 'selected' : '' ?>>Driver / Technician</option>
                  <option value="Admin/Secretary" <?= ($_POST['position'] ?? '') === 'Admin/Secretary' ? 'selected' : '' ?>>Admin / Secretary</option>
                  <option value="Admin" <?= ($_POST['position'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['position']) ? 'block' : 'none' ?>"><?= $errors['position'] ?? '' ?></p>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="awegreen@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['email']) ? 'block' : 'none' ?>"><?= $errors['email'] ?? '' ?></p>
              </div>

              <div class="col-md-6">
                <label>Password</label>
                <div class="input-group">
                  <input type="password" name="password" class="form-control" id="empPassword" placeholder="********" minlength="6">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fa-solid fa-eye" id="passwordIcon"></i>
                  </button>
                </div>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['password']) ? 'block' : 'none' ?>"><?= $errors['password'] ?? '' ?></p>
              </div>
            </div>

            <!-- PERSONAL INFO -->
            <h6 class="mb-3">Personal Information</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="Juan">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['first_name']) ? 'block' : 'none' ?>"><?= $errors['first_name'] ?? '' ?></p>
              </div>
              <div class="col-md-4">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Dela Cruz">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['last_name']) ? 'block' : 'none' ?>"><?= $errors['last_name'] ?? '' ?></p>
              </div>
              <div class="col-md-4">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="09*********">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['phone']) ? 'block' : 'none' ?>"><?= $errors['phone'] ?? '' ?></p>
              </div>
            </div>

            <!-- EMPLOYMENT DETAILS -->
            <div id="employmentDetails">
              <hr>
              <h6 class="mb-3">Employment Details</h6>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label>Daily Salary (Php)</label>
                  <input type="number" name="daily_salary" class="form-control" min="1" step="0.01" value="<?= htmlspecialchars($_POST['daily_salary'] ?? '') ?>" placeholder="600">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['daily_salary']) ? 'block' : 'none' ?>"><?= $errors['daily_salary'] ?? '' ?></p>
                </div>
                <div class="col-md-6">
                  <label>Date Hired</label>
                  <input type="date" name="date_hired" class="form-control" value="<?= htmlspecialchars($_POST['date_hired'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['date_hired']) ? 'block' : 'none' ?>"><?= $errors['date_hired'] ?? '' ?></p>
                </div>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_employee" class="btn btn-green text-white">Add Employee</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ARCHIVE EMPLOYEE MODAL -->
  <div class="modal fade" id="archiveEmployeeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="archiveEmployeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="archiveEmployeeLabel">Archive Employee</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="post">
          <input type="hidden" name="archive_id" id="archiveEmployeeId">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this employee?</h3>
            <p class="text-center text-muted">Archived employees can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="modal-archive-button" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Toggle password
    document.getElementById("togglePassword").addEventListener("click", function() {
      const passwordField = document.getElementById("empPassword");
      const passwordIcon = document.getElementById("passwordIcon");
      if (passwordField.type === "password") {
        passwordField.type = "text";
        passwordIcon.classList.remove("fa-eye");
        passwordIcon.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        passwordIcon.classList.remove("fa-eye-slash");
        passwordIcon.classList.add("fa-eye");
      }
    });

    // Show/hide employment details
    const positionSelect = document.getElementById("positionSelect");
    const employmentDetails = document.getElementById("employmentDetails");

    function toggleEmploymentDetails() {
      if (positionSelect.value === "Admin") {
        employmentDetails.style.display = "none";
      } else {
        employmentDetails.style.display = "block";
      }
    }
    toggleEmploymentDetails();
    positionSelect.addEventListener("change", toggleEmploymentDetails);

    // Open archive modal and set employee ID
    function openArchiveModal(employeeId) {
      document.getElementById('archiveEmployeeId').value = employeeId;
      var archiveModal = new bootstrap.Modal(document.getElementById('archiveEmployeeModal'));
      archiveModal.show();
    }
  </script>

</body>

</html>