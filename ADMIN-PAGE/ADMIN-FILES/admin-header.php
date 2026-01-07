<?php
session_start();
date_default_timezone_set('Asia/Manila');
include '../../INCLUDES/db-con.php';
include '../../INCLUDES/log-activity.php';

// Check if user is logged in as employee
if (!isset($_SESSION['employee_id']) || $_SESSION['user_type'] !== 'employee') {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

// Store employee info in variables for easy access - MUST BE BEFORE NOTIFICATIONS
$employee_id = $_SESSION['employee_id'];
$employee_first_name = $_SESSION['first_name'];
$employee_last_name = $_SESSION['last_name'];
$employee_full_name = $employee_first_name . ' ' . $employee_last_name;
$employee_email = $_SESSION['email'];
$employee_position = trim($_SESSION['position']); // TRIM to remove extra spaces!

// Check if user is Super Admin or Admin (has full permissions)
// IMPORTANT: Check exact matches to distinguish between Super Admin and Admin
$is_super_admin = ($employee_position === 'Super Admin');
$is_admin = ($employee_position === 'Admin' || 
             $employee_position === 'Admin/Secretary' || 
             $employee_position === 'Super Admin');


// Check if user is logged in as employee
if (file_exists('../../INCLUDES/notifications.php')) {
  include_once '../../INCLUDES/notifications.php';

  // Get unread count - EXCLUDE client-side notification types
  $unread_sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE recipient_id = $employee_id 
                AND is_read = 0
                AND type NOT IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')";
  $unread_result = mysqli_query($conn, $unread_sql);
  $unread_row = mysqli_fetch_assoc($unread_result);
  $unread_count = $unread_row['count'];

  // Get recent notifications (limit 5 for dropdown) - EXCLUDE client-side notification types
  $notif_sql = "SELECT * FROM notifications 
                WHERE recipient_id = $employee_id 
                AND type NOT IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')
                ORDER BY created_at DESC 
                LIMIT 5";
  $notif_result = mysqli_query($conn, $notif_sql);
  $notifications = [];
  if ($notif_result) {
    while ($notif = mysqli_fetch_assoc($notif_result)) {
      $notifications[] = $notif;
    }
  }
} else {
  $unread_count = 0;
  $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>A We Green Admin</title>
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
            <img class="awegreen-logo" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Logo" />
            <span class="text-white admin-text fs-18">
              A We Green <span id="hide-admin-text">Enterprise</span>
            </span>
          </a>
        </div>

        <div class="right-header text-light d-flex align-items-center gap-1 gap-md-2">
          <!-- ========== NOTIFICATIONS ========== -->
          <div class="d-flex dropdown-center">
            <div id="bell-container">
              <button class="btn border-0 p-0 flex" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <div id="button-bell" class="position-relative flex" data-bs-toggle="tooltip"
                  data-bs-placement="bottom" data-bs-title="Notifications">
                  <i class="fa-solid fa-bell fs-5 text-light"></i>
                  <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger rounded-pill badge-bell position-absolute">
                      <?= $unread_count > 9 ? '9+' : $unread_count ?>
                    </span>
                  <?php endif; ?>
                </div>
              </button>

              <ul class="dropdown-menu mt-1 notif-dropdown">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                  <p class="fs-5 mb-0 notif-text">Notifications</p>
                  <?php if ($unread_count > 0): ?>
                    <a href="admin-mark-all-read.php" class="btn btn-sm text-decoration-none p-0 green-text">
                      <span class="green-text"> Mark all as read</span>
                    </a>
                  <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                  <li class="text-center py-4">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0 small">No notifications</p>
                  </li>
                <?php else: ?>
                  <?php foreach ($notifications as $notif): ?>
                    <li>
                      <a class="dropdown-item notification-item <?= $notif['is_read'] ? '' : 'unread-notif' ?>"
                        href="<?= htmlspecialchars($notif['link'] ?? '#') ?>"
                        data-id="<?= $notif['notification_id'] ?>"
                        onclick="markAdminNotifRead(event, this)">
                        <div class="d-flex gap-2">
                          <div class="notif-icon">
                            <?php
                            $icon = match ($notif['type']) {
                              'TASK_ASSIGNED' => '<i class="fas fa-tasks green-text"></i>',
                              'TASK_UPDATED' => '<i class="fas fa-edit green-text"></i>',
                              'TASK_COMPLETED' => '<i class="fas fa-check-circle green-text"></i>',
                              'ASSESSMENT_ACCEPTED_ADMIN' => '<i class="fas fa-user-check green-text"></i>',
                              'ASSESSMENT_REJECTED_ADMIN' => '<i class="fas fa-user-times green-text"></i>',
                              default => '<i class="fas fa-bell green-text"></i>'
                            };
                            echo $icon;
                            ?>
                          </div>
                          <div class="flex-grow-1">
                            <p class="mb-0 fw-semibold small"><?= htmlspecialchars($notif['title']) ?></p>
                            <p class="mb-1 small text-muted text-truncate"
                              style="max-width: 240px;"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="<?= htmlspecialchars($notif['message']) ?>">
                              <?= htmlspecialchars($notif['message']) ?>
                            </p>

                            <small class="text-muted" style="font-size: 11px;">
                              <?php
                              $time_diff = time() - strtotime($notif['created_at']);
                              if ($time_diff < 60) {
                                echo 'Just now';
                              } elseif ($time_diff < 3600) {
                                echo floor($time_diff / 60) . ' min ago';
                              } elseif ($time_diff < 86400) {
                                echo floor($time_diff / 3600) . ' hr ago';
                              } else {
                                echo date('M d, Y', strtotime($notif['created_at']));
                              }
                              ?>
                            </small>
                          </div>
                        </div>
                      </a>
                    </li>
                  <?php endforeach; ?>

                  <li class="border-top">
                    <a href="admin-all-notifications.php" class="dropdown-item text-center green-text small py-2 mt-2">
                      <i class="fas fa-list me-1"></i> See all notifications
                    </a>
                  </li>
                <?php endif; ?>
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
            <ul class="dropdown-menu mt-1 profile-dropdown" style="transform: translateX(-150px);">
              <li class="dropdown-profile-top d-flex mb-1">
                <a class="dropdown-item d-flex align-items-center" href="my-profile.php">
                  <div class="green-bg rounded-circle flex ms-1"
                    style="padding: 9px; transform: translateX(-9px);">
                    <i class="fa-solid fa-user text-white fs-18" style="position: relative; top: -2px;"></i>
                  </div>
                  <div class="dropdown-profile-text" style="margin-left: -4px;">
                    <p class="fs-18 view-profile-text mb-0"><?= htmlspecialchars($employee_full_name) ?></p>
                    <p class="m-0 fs-14 db-text-secondary"><?= htmlspecialchars($employee_position) ?></p>
                  </div>
                </a>
              </li>
              <li class="mb-1">
                <a class="dropdown-item d-flex align-items-center" href="admin-activity-logs.php">
                  <i class="fa-solid fa-clock-rotate-left me-2 fs-22"></i>
                  <span class="fs-18 d-inline-block">Activity Logs</span>
                </a>
              </li>
              <?php if ($is_super_admin): ?>
              <li class="mb-1">
                <a class="dropdown-item d-flex align-items-center" href="admin-settings.php">
                  <i class="fa-solid fa-gear me-2 fs-22"></i>
                  <span class="fs-18 d-inline-block">Settings</span>
                </a>
              </li>
              <?php endif; ?>
              <li class="mb-1">
                <a class="dropdown-item d-flex align-items-center" href="logout.php">
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
            <img class="me-2" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="Logo" style="width: 36px;">
          </a>
          <a class="navbar-brand fw-semibold" href="#">
            <span class="text-white fs-18">
              A We Green <span id="hide-admin-text">Enterprise</span>
            </span>
          </a>
        </div>
        <div class="offcanvas-body" style="background-color: #16A249">
          <ul class="offcanvas-content list-unstyled fs-20">
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-dashboard.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined text-center">dashboard</span>
                <span class="sidebar-text ms-2">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-inventory.php" class="sidebar-anchor">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="sidebar-text" style="margin-left: 10px">Inventory</span>
              </a>
            </li>
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-tasks.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined">task_alt</span>
                <span class="sidebar-text ms-2">Tasks</span>
              </a>
            </li>
            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-assessments.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined">assignment</span>
                <span class="sidebar-text ms-2">Assessments</span>
              </a>
            </li>


            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-employees.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined">people</span>
                <span class="sidebar-text ms-2">Employees</span>
              </a>
            </li>


            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-projects.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined">folder</span>
                <span class="sidebar-text ms-2">Projects</span>
              </a>
            </li>


            <li class="sidebar-content-item d-flex align-items-center mb-1">
              <a href="admin-manage-accounts.php" class="sidebar-anchor">
                <span class="aside-icon material-symbols-outlined">manage_accounts</span>
                <span class="sidebar-text ms-2">Users</span>
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
            <span class="aside-icon material-symbols-outlined text-center">dashboard</span>
            <span class="sidebar-text ms-2">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-inventory.php" class="sidebar-anchor">
            <span class="material-symbols-outlined">inventory_2</span>
            <span class="sidebar-text" style="margin-left: 10px">Inventory</span>
          </a>
        </li>
        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-tasks.php" class="sidebar-anchor">
            <span class="aside-icon material-symbols-outlined">task_alt</span>
            <span class="sidebar-text ms-2">Tasks</span>
          </a>
        </li>
        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-assessments.php" class="sidebar-anchor">
            <span class="aside-icon material-symbols-outlined">assignment</span>
            <span class="sidebar-text ms-2">Assessments</span>
          </a>
        </li>

  
          <li class="sidebar-content-item d-flex align-items-center mb-1">
            <a href="admin-employees.php" class="sidebar-anchor">
              <span class="aside-icon material-symbols-outlined">people</span>
              <span class="sidebar-text ms-2">Employees</span>
            </a>
          </li>
  

        <li class="sidebar-content-item d-flex align-items-center mb-1">
          <a href="admin-projects.php" class="sidebar-anchor">
            <span class="aside-icon material-symbols-outlined">folder</span>
            <span class="sidebar-text ms-2">Projects</span>
          </a>
        </li>

  
          <li class="sidebar-content-item d-flex align-items-center mb-1">
            <a href="admin-manage-accounts.php" class="sidebar-anchor">
              <span class="aside-icon material-symbols-outlined">manage_accounts</span>
              <span class="sidebar-text ms-2">Users</span>
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
    window.addEventListener("resize", toggleOffcanvas);
  });

  window.addEventListener('scroll', function(e) {
    e.stopImmediatePropagation();
  }, true);

  function markAdminNotifRead(event, el) {
    event.preventDefault();

    const notifId = el.dataset.id;
    const link = el.getAttribute('href');

    fetch('admin-mark-notification-read.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'notification_id=' + notifId
      })
      .finally(() => {
        if (link && link !== '#') {
          window.location.href = link;
        }
      });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.forEach(function(el) {
      new bootstrap.Tooltip(el);
    });
  });
</script>


</html>