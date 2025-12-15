<?php 
  session_start();
  include '../../INCLUDES/db-con.php';
  include '../../INCLUDES/log-activity.php';

  $user_id = $_SESSION['user_id'] ?? null;
  $first_name = $_SESSION['first_name'] ?? 'Guest';
  $email = $_SESSION['email'] ?? null;

  // echo("User ID: " . $user_id . "<br>");
  // echo("First Name: " . $first_name . "<br>");
  // echo("Email: " . $email . "<br>");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>A We Green Enterprise</title>
  <!-- ========== CSS LINK ========== -->
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../USER-CSS/user-header.css">
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-responsiveness.css" />

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png">
</head>

<body>
  <nav class="navbar navbar-expand-lg fixed-top navbar-light green-bg" id="mainNavbar">
    <div class="container-fluid mx-md-5 mx-2">

      <a href="#" class="fw-bold text-decoration-none flex gap-1 fs-20">
        <img class="awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Logo" />
        <span class="text-white mobile-fs-18">A We Green Enterprise</span>
      </a>

      <!-- Desktop menu (visible on large screens) -->
      <div class="collapse navbar-collapse d-none d-lg-flex user-header-links" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2 me-2">
          <li class="nav-item">
            <a class="nav-link nav-portal fs-14 fw-semibold" aria-current="page" href="user-portal.php">Portal</a>
          </li>
          <li class="nav-item ">
            <a class="nav-link nav-assessment fs-14 fw-semibold" href="user-assessments.php">Assessment</a>
          </li>
          <li class="nav-item ">
            <a class="nav-link nav-projects fs-14 fw-semibold" href="user-awg-projects.php">Projects</a>
          </li>
          <!-- <li class="nav-item ">
            <a class="nav-link nav-contact fs-14 fw-semibold" href="user-contact-support.php">Contact</a>
          </li> -->
        </ul>
      </div>

      <!-- Right header - notifications and profile -->
      <div class="right-header d-flex align-items-center gap-1 gap-md-2 order-lg-2">

        <!-- ========== NOTIFICATIONS ========== -->
        <div class="d-flex dropdown-center">
          <div id="bell-container">
            <button class="btn border-0 p-0 flex" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <div id="button-bell" class="position-relative flex" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-title="Notifications">
                <i class="fa-solid fa-bell fs-5 text-white" style="color: #fff;"></i>
                <span class="badge bg-danger rounded-pill badge-bell position-absolute">
                  3
                </span>
              </div>
            </button>

            <ul class="dropdown-menu mt-1 notif-dropdown" style="transform: translateX(-124px);">
              <p class="fs-5 ps-3 mb-2 notif-text">New Signups</p>
              <li>
                <a class="dropdown-item" href="signup-accounts.php">
                  <span class="text-secondary small">
                    You have 3 new signups
                  </span>
                </a>
              </li>

              <li><a class="dropdown-item" href="#">No new signups</a></li>
            </ul>
          </div>
        </div>

        <!-- ========== PROFILE MENU ========== -->
        <div class="dropdown-center">
          <button class="btn p-0 border-0" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="header-user rounded-circle flex ms-1">
              <i class="fa-solid fa-user fs-18" style="position: relative; top: -2px; color: #16A249"></i>
            </div>
          </button>

          <ul class="dropdown-menu mt-1 profile-dropdown" style="transform: translateX(-100px);">
            <li class="dropdown-profile-top d-flex mb-1 ">
              <a class="dropdown-item d-flex align-items-center" href="user-profile.php">
                <div class="green-bg rounded-circle flex ms-1"
                  style="padding: 9px; transform: translateX(-9px);">
                  <i class="fa-solid fa-user text-white fs-18" style="position: relative; top: -2px;"></i>
                </div>
                <div class="dropdown-profile-text" style="margin-left: -4px;">
                  <p class="fs-18 view-profile-text">View Profile</p>
                  <p class="m-0 fs-14 db-text-secondary">Administrator</p>
                </div>
              </a>
            </li>
            <!-- SETTINGS
            <li class="mb-1">
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="fa-solid fa-question ms-1 me-2 fs-22"></i>
                <span class="fs-18 d-inline-block ms-1">Help & Support</span>
              </a>
            </li>
            <li class="mb-1">
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="fa-solid fa-gear me-2 fs-22 "></i>
                <span class="fs-18 d-inline-block">Settings</span>
              </a>
            </li> -->
            <li class="mb-1">
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="fa-solid fa-right-from-bracket me-2 fs-22"></i>
                <span class="fs-18 d-inline-block">Log out</span>
              </a>
            </li>
          </ul>
        </div>
        <!-- Toggler button for offcanvas -->
        <button class="navbar-toggler ms-1 d-lg-none" type="button" data-bs-toggle="offcanvas"
          data-bs-target="#navbarOffcanvas" aria-controls="navbarOffcanvas"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>

    </div>
  </nav>

  <!-- Offcanvas menu for mobile (slides from right) -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="navbarOffcanvas" aria-labelledby="navbarOffcanvasLabel">
    <div class="offcanvas-header green-bg">
      <h5 class="offcanvas-title text-white" id="navbarOffcanvasLabel">Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav gap-3">
        <li class="nav-item">
          <a class="nav-link nav-portal fs-18 fw-semibold" aria-current="page" href="user-portal.php">Portal</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-assessment fs-18 fw-semibold" href="user-assessments.php">Assessment</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-projects fs-18 fw-semibold" href="user-awg-projects.php">Projects</a>
        </li>
        <!-- <li class="nav-item">
          <a class="nav-link nav-contact fs-18 fw-semibold" href="user-contact-support.php">Contact</a>
        </li> -->
      </ul>
    </div>
  </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</html>