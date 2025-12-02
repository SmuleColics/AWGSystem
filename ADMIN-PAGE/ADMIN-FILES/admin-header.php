<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineVault Admin</title>
  <!-- ========== CSS LINK ========== -->
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../ADMIN-CSS/admin-header.css">
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css">


  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="icon" href="../../INCLUDES/LP-IMAGES/awegreen-logo.png">
</head>

<body>
  <nav class="navbar green-bg fixed-top">
    <div class="container-fluid">
      <header class="d-flex align-items-center justify-content-between px-3 w-100">

        <div class="left-header d-flex align-items-center">
          <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#dashboard-offcanvas" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <a href="#" class="fw-bold text-decoration-none flex gap-1 fs-24 awegreen-admin-logo">
            <img class="awegreen-logo " src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Logo" />
            <span class="text-white admin-text">
              <span id="hide-admin-text">A We Green</span> Admin</span>
          </a>

        </div>

        <div class="right-header text-light d-flex align-items-center gap-1 gap-md-2">
          <!-- ========== SEARCH ========== -->
          <div class="search-container position-relative">
            <label id="search-icon" for="dashboard-search">
              <i class="fas fa-search light-text"></i>
            </label>
            <input id="dashboard-search" type="text" class="form-control bg-light text-dark"
              placeholder="Search...">
            <div id="search-results" style="display:none;"></div>
          </div>

          <!-- ========== NOTIFICATIONS ========== -->
          <div class="d-flex dropdown-center">
            <div id="bell-container">
              <button class="btn border-0 p-0 flex" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <div id="button-bell" class="position-relative flex" data-bs-toggle="tooltip"
                  data-bs-placement="bottom" data-bs-title="Notifications">
                  <i class="fa-solid fa-bell fs-5 text-light"></i>
                  <span class="badge bg-danger rounded-pill badge-bell position-absolute">
                    3
                  </span>
                </div>
              </button>

              <ul class="dropdown-menu mt-1 notif-dropdown" style="transform: translateX(-154px);">
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
              <div class="header-user bg-white rounded-circle flex ms-1" style="height: 40px; width: 40px;">
                <i class="fa-solid fa-user fs-18 green-text" style="position: relative; top: -2px;"></i>
              </div>
            </button>
            <ul class="dropdown-menu  mt-1 profile-dropdown" style="transform: translateX(-130px);">
              <li class="dropdown-profile-top d-flex mb-1 ">
                <a class="dropdown-item d-flex align-items-center" href="#">
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
              <!-- SETTINGS  -->
              <!-- <li class="mb-1">
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
                <a class="dropdown-item d-flex align-items-center" href="../../LANDING-PAGE/LP-FILES/LandingPage.php">
                  <i class="fa-solid fa-right-from-bracket me-2 fs-22"></i>
                  <span class="fs-18 d-inline-block">Log out</span>
                </a>
              </li>
            </ul>
          </div>

        </div>

      </header>
      <!-- ========== OFFCANVAS SIDEBAR ========== -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="dashboard-offcanvas"
        aria-labelledby="offcanvasDarkNavbarLabel" style="background-color: #16A249">
        <div class="offcanvas-header" style="background-color: #16A249">
          <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-dismiss="offcanvas">
            <span class="navbar-toggler-icon"></span>
          </button>
          <a href="#">
            <img class="me-2" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="CineVault Logo"
              style="width: 36px;">
          </a>
          <a class="navbar-brand fw-semibold" href="#">
            <span class="text-white">
              <span id="hide-admin-text">A We Green</span> Admin
            </span>

          </a>
        </div>
        <div class="offcanvas-body" style="background-color: #16A249">
          <ul class="offcanvas-content list-unstyled fs-20">
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-dashboard.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined text-center">
                  dashboard
                </span>
                <span class="sidebar-text ms-2">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-inventory.php" class="sidebar-anchor">
                <span class="material-symbols-outlined">
                  inventory_2
                </span>
                <span class="sidebar-text" style="margin-left: 10px">Inventory</span>
              </a>
            </li>
            <li
              class="sidebar-content-item sidebar-collapse  d-flex align-items-center  mb-1 tasks-db"
              aria-expanded="true">
              <a href="admin-tasks.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    task_alt
                  </span>
                  <span class="sidebar-text ms-2">Tasks</span>
                </div>
              </a>
            </li>
            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-assessments.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    assignment
                  </span>
                  <span class="sidebar-text ms-2">Assessments</span>
                </div>
              </a>
            </li>
            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-attendance.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    event_available
                  </span>
                  <span class="sidebar-text ms-2">Attendance</span>
                </div>
              </a>
            </li>
            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-payroll.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    payments
                  </span>
                  <span class="sidebar-text ms-2">Payroll</span>
                </div>
              </a>
            </li>
            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-messages.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    mail
                  </span>
                  <span class="sidebar-text ms-2">Messages</span>
                </div>
              </a>
            </li>


            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-employees.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    people
                  </span>
                  <span class="sidebar-text ms-2">Employees</span>
                </div>
              </a>
            </li>

            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-projects.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    folder
                  </span>
                  <span class="sidebar-text ms-2">Projects</span>
                </div>
              </a>
            </li>
            <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
              <a href="admin-manage-accounts.php" class="sidebar-anchor">
                <div class="d-flex align-items-center">
                  <span class="aside-icon material-symbols-outlined">
                    manage_accounts
                  </span>
                  <span class="sidebar-text ms-2">Users</span>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <!-- ========== END OF HEADER ========== -->

  <!-- ========== START OF SIDEBAR ========== -->
  <aside class="sidebar green-bg">
    <div class="sidebar-container">
      <ul class="sidebar-content list-unstyled fs-20">
        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-dashboard.php" class="sidebar-anchor">
            <span class="aside-icon material-symbols-outlined text-center">
              dashboard
            </span>
            <span class="sidebar-text ms-2">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-inventory.php" class="sidebar-anchor">
            <span class="material-symbols-outlined">
              inventory_2
            </span>
            <span class="sidebar-text" style="margin-left: 10px">Inventory</span>
          </a>
        </li>
        <li
          class="sidebar-content-item sidebar-collapse  d-flex align-items-center  mb-1 tasks-db"
          aria-expanded="true">
          <a href="admin-tasks.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                task_alt
              </span>
              <span class="sidebar-text ms-2">Tasks</span>
            </div>
          </a>
        </li>
        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-assessments.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                assignment
              </span>
              <span class="sidebar-text ms-2">Assessments</span>
            </div>
          </a>
        </li>
        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-attendance.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                event_available
              </span>
              <span class="sidebar-text ms-2">Attendance</span>
            </div>
          </a>
        </li>
        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-payroll.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                payments
              </span>
              <span class="sidebar-text ms-2">Payroll</span>
            </div>
          </a>
        </li>
        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-messages.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                mail
              </span>
              <span class="sidebar-text ms-2">Messages</span>
            </div>
          </a>
        </li>


        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-employees.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                people
              </span>
              <span class="sidebar-text ms-2">Employees</span>
            </div>
          </a>
        </li>

        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-projects.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                folder
              </span>
              <span class="sidebar-text ms-2">Projects</span>
            </div>
          </a>
        </li>

        <li class="sidebar-content-item sidebar-collapse d-flex align-items-center justify-content-between mb-1">
          <a href="admin-manage-accounts.php" class="sidebar-anchor">
            <div class="d-flex align-items-center">
              <span class="aside-icon material-symbols-outlined">
                manage_accounts
              </span>
              <span class="sidebar-text ms-2">Users</span>
            </div>
          </a>
        </li>


      </ul>
    </div>
  </aside>
  <!-- ========== END OF SIDEBAR ========== -->

</body>

<!-- ========== SCRIPTS ========== -->
<script>
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));

  // SIDEBAR AUTOHIDE ON LARGE SCREEN
  let sidebar = document.querySelector(".sidebar");
  let sidebarText = document.querySelectorAll(".sidebar-text");
  let asideIcon = document.querySelectorAll(".aside-icon");

  window.addEventListener("load", function() {
    const offcanvasElement = document.getElementById("dashboard-offcanvas");
    let offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);

    function toggleOffcanvas() {
      if (window.innerWidth >= 992) {
        if (offcanvasElement.classList.contains("show")) {
          offcanvasInstance.hide();
        }
      }
    }
    toggleOffcanvas();
    window.addEventListener("resize", function() {
      toggleOffcanvas();
    });
  });
</script>

</html>