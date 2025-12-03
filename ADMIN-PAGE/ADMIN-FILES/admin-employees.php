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
    .sidebar-content-item:nth-child(8) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(8) .sidebar-anchor,
    .sidebar-content-item:nth-child(8) .sidebar-anchor span {
      color: #16A249 !important;

    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Employee Management</h1>
        <p class="admin-top-desc">Manage your team members and their roles</p>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Total Employees</p>
          <p class="fw-bold fs-24 mobile-fs-22 mb-0">5</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Administrators</p>
          <p class="fw-bold fs-24 mb-0">1</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Employees</p>
          <p class="fw-bold fs-24 mb-0">4</p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">All Employees</p>
            <div>
              <div>
                <button class="btn green-bg text-white add-item-btn" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                  <i class="fa-solid fa-plus me-1"></i> Add Employee
                </button>
              </div>
            </div>
          </div>
          <div class="divider my-3"></div>

          <div class="employee-container d-flex flex-wrap gap-4 justify-content-center">
            <div class="employee-con p-4 border rounded-3">
              <div class="w-100 flex mb-2">
                <div class="employee-pfp">
                  <span class="pfp">A</span>
                </div>
              </div>
              <h2 class="fs-18 text-center mb-1">Admin</h2>
              <p class="light-text fs-12 text-center mb-1">admin</p>
              <p class="light-text fs-14 text-center ">
                <i class="fas fa-envelope me-1"></i>
                admin@gmail.com
              </p>
              <div class="emp-btn-con w-100 d-flex gap-2">
                <a href="my-profile.php" class="btn btn-sm btn-outline-secondary w-50 text-nowrap fs-14" type="button">View Profile</a>
                <a href="my-profile.php" class="btn btn-sm btn-outline-secondary w-50 fs-14" type="button">Edit</a>
              </div>
            </div>
            <div class="employee-con p-4 border rounded-3">
              <div class="w-100 flex mb-2">
                <div class="employee-pfp">
                  <span class="pfp">A</span>
                </div>
              </div>
              <h2 class="fs-18 text-center mb-1">Admin</h2>
              <p class="light-text fs-12 text-center mb-1">admin</p>
              <p class="light-text fs-14 text-center ">
                <i class="fas fa-envelope me-1"></i>
                admin@gmail.com
              </p>
              <div class="emp-btn-con w-100 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary w-50 text-nowrap fs-14" type="button">View Profile</button>
                <button class="btn btn-sm btn-outline-secondary w-50 fs-14" type="button">Edit</button>
              </div>
            </div>
            <div class="employee-con p-4 border rounded-3">
              <div class="w-100 flex mb-2">
                <div class="employee-pfp">
                  <span class="pfp">A</span>
                </div>
              </div>
              <h2 class="fs-18 text-center mb-1">Admin</h2>
              <p class="light-text fs-12 text-center mb-1">admin</p>
              <p class="light-text fs-14 text-center ">
                <i class="fas fa-envelope me-1"></i>
                admin@gmail.com
              </p>
              <div class="emp-btn-con w-100 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary w-50 text-nowrap fs-14" type="button">View Profile</button>
                <button class="btn btn-sm btn-outline-secondary w-50 fs-14" type="button">Edit</button>
              </div>
            </div>
            <div class="employee-con p-4 border rounded-3 mb-3">
              <div class="w-100 flex mb-2">
                <div class="employee-pfp">
                  <span class="pfp">A</span>
                </div>
              </div>
              <h2 class="fs-18 text-center mb-1">Admin</h2>
              <p class="light-text fs-12 text-center mb-1">admin</p>
              <p class="light-text fs-14 text-center ">
                <i class="fas fa-envelope me-1"></i>
                admin@gmail.com
              </p>
              <div class="emp-btn-con w-100 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary w-50 text-nowrap fs-14" type="button">View Profile</button>
                <button class="btn btn-sm btn-outline-secondary w-50 fs-14" type="button">Edit</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->
  <!-- ADD EMPLOYEE MODAL -->
  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="addEmployeeForm">
          <div class="modal-body">

            <!-- LOGIN INFORMATION -->
            <h6 class="mb-3">Login Information</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" placeholder="employee@example.com" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="empPassword" placeholder="Enter password" required>
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fa-solid fa-eye" id="passwordIcon"></i>
                  </button>
                </div>
              </div>
            </div>

            <hr>

            <!-- PERSONAL INFORMATION -->
            <h6 class="mb-3">Personal Information</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" placeholder="Juan Dela Cruz" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" placeholder="09XXXXXXXXX">
              </div>
            </div>

            <hr>

            <!-- EMPLOYMENT DETAILS -->
            <h6 class="mb-3">Employment Details</h6>
            <div class="row g-3 mb-3">

              <div class="col-md-6">
                <label class="form-label">Position</label>
                <select class="form-select" required>
                  <option selected disabled>Select Position</option>
                  <option value="Driver">Driver</option>
                  <option value="Technician">Technician</option>
                  <option value="Driver/Technician">Driver / Technician</option>
                  <option value="Admin/Secretary">Admin / Secretary</option>
                  <option value="Admin">Admin</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Date Hired</label>
                <input type="date" class="form-control" required>
              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green text-white">Add Employee</button>
          </div>

        </form>

      </div>
    </div>
  </div>


</body>

<script>
    // Toggle Password Visibility
    document.getElementById("togglePassword").addEventListener("click", function () {
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
  </script>



</html>