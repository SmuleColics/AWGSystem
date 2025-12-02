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
  <main id="main" class="container-xxl text-dark px-4">
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

  





</html>