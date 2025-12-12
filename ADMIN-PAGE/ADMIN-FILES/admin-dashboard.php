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
  .sidebar-content-item:nth-child(1) {
    background-color: #f2f2f2 !important;
  }
  .sidebar-content-item:nth-child(1) .sidebar-anchor,
  .sidebar-content-item:nth-child(1) .sidebar-anchor span {
    color: #16A249 !important;
  }
  </style>

</head>

<body class="pt-0">
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 mt-5 min-vh-100">
    <div class="admin-top-text my-3">
      <h1 class="fs-36">Admin Dashboard</h1>
      <p class="admin-top-desc">Monitor stock levels, manage products, and stay updated on inventory status</p>
    </div>
    <!-- DASHBOARD STATUS -->
    <div class="row g-3 ">
      <!-- USERS -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="daily-users d-flex justify-content-between align-items-center">
            <div class="green-bg dashboard-icon-con flex rounded mt-2">
              <i class="fa-solid fa-user dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Users</p>
              <p class="fs-24 mobile-fs-22 light-text">100k</p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock  ms-1"></i> Last 24 hours</p>
        </div>
      </div>
      <!-- REVENUE -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="daily-users d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <span class="text-white dashboard-icon">₱</span>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Revenue</p>
              <p class="fs-24 mobile-fs-22 light-text">$100,000</p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> Last 24 hours</p>
        </div>
      </div>


      <!-- FIXED ISSUES -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <i class="fa-solid fa-circle-exclamation dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Fixed Issues</p>
              <p class="fs-24 mobile-fs-22 light-text">75</p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> Last 1 month</p>
        </div>
      </div>

      <!-- POSTS -->
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-3 dashboard-one rounded">
          <div class="d-flex justify-content-between align-items-center">
            <div class="dashboard-icon-con flex rounded mt-2 green-bg">
              <i class="fa-solid fa-upload dashboard-icon text-white"></i>
            </div>
            <div class="mt-4 text-end">
              <p class="fs-14 mb-1 light-text">Posts</p>
              <p class="fs-24 mobile-fs-22 light-text">10k</p>
            </div>
          </div>
          <div class="divider my-3"></div>
          <p class="fs-12 mb-0 light-text"><i class="fa-regular fa-clock ms-1"></i> Last 24 hours</p>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">
      <!-- CARD 1: Daily Users -->
      <div class="col-md-4 col-sm-6">
        <div class="p-3 rounded dashboard-one">
          <div class="analytics-container light-text">
            <a href="#"><img class="w-100 rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="daily users analytics"></a>
            <p class="mt-3 fs-18 mb-1 green-text">Daily Users</p>
            <p class="fs-14">55% increase in today's users</p>
            <div class="divider my-3"></div>
            <p class="fs-14 mb-0"><i class="fa-regular fa-clock"></i> 4 mins ago</p>
          </div>
        </div>
      </div>

      <!-- CARD 2: Posts -->
      <div class="col-md-4 col-sm-6">
        <div class="p-3 rounded dashboard-one">
          <div class="analytics-container light-text">
            <a href="#"><img class="w-100 rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="posts analytics"></a>
            <p class="mt-3 fs-18 mb-1 green-text">Posts</p>
            <p class="fs-14">Last Campaign Performance</p>
            <div class="divider my-3"></div>
            <p class="fs-14 mb-0"><i class="fa-regular fa-clock"></i> campaign sent 2 days ago</p>
          </div>
        </div>
      </div>

      <!-- CARD 3: Completed Tasks -->
      <div class="col-md-4 col-sm-6">
        <div class="p-3 rounded dashboard-one">
          <div class="analytics-container light-text">
            <a href="#"><img class="w-100 rounded" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="completed tasks analytics"></a>
            <p class="mt-3 fs-18 mb-1 green-text">Completed Tasks</p>
            <p class="fs-14">Last campaign performance</p>
            <div class="divider my-3"></div>
            <p class="fs-14 mb-0"><i class="fa-regular fa-clock"></i> campaign sent 2 days ago</p>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->
</body>

</html>