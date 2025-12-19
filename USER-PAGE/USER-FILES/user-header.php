<?php 
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  date_default_timezone_set('Asia/Manila');
  include '../../INCLUDES/db-con.php';
  include '../../INCLUDES/log-activity.php';

  // Get user info from session
  $user_id = $_SESSION['user_id'] ?? null;
  $first_name = $_SESSION['first_name'] ?? 'Guest';
  $last_name = $_SESSION['last_name'] ?? '';
  $email = $_SESSION['email'] ?? null;

  // Get user notifications (ONLY client-side types)
  $unread_count = 0;
  $notifications = [];

  if ($user_id) {
    // Get unread count - ONLY include client-side notification types
    $unread_sql = "SELECT COUNT(*) as count FROM notifications 
                  WHERE recipient_id = $user_id 
                  AND is_read = 0
                  AND type IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')";
    $unread_result = mysqli_query($conn, $unread_sql);
    $unread_row = mysqli_fetch_assoc($unread_result);
    $unread_count = $unread_row['count'] ?? 0;

    // Get recent notifications (limit 5 for dropdown) - ONLY client-side notification types
    $notif_sql = "SELECT * FROM notifications 
                  WHERE recipient_id = $user_id 
                  AND type IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')
                  ORDER BY created_at DESC 
                  LIMIT 5";
    $notif_result = mysqli_query($conn, $notif_sql);
    if ($notif_result) {
      while ($notif = mysqli_fetch_assoc($notif_result)) {
        $notifications[] = $notif;
      }
    }
  }
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
                <?php if ($unread_count > 0): ?>
                  <span class="badge bg-danger rounded-pill badge-bell position-absolute">
                    <?= $unread_count > 9 ? '9+' : $unread_count ?>
                  </span>
                <?php endif; ?>
              </div>
            </button>

            <ul class="dropdown-menu mt-1 notif-dropdown" style="transform: translateX(-154px); width: 300px; max-height: 450px; overflow-y: auto;">
              <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <p class="fs-5 mb-0 notif-text">Notifications</p>
                <?php if ($unread_count > 0): ?>
                  <a href="mark-notifications-read.php" class="btn btn-sm text-decoration-none p-0 green-text">
                    <span class="green-text" style="font-size: 12px;">Mark all as read</span>
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
                      onclick="markAsRead(<?= $notif['notification_id'] ?>)">
                      <div class="d-flex gap-2">
                        <div class="notif-icon">
                          <?php
                          $icon = match ($notif['type']) {
                            'ASSESSMENT_ACCEPTED' => '<i class="fas fa-check-circle" style="color: #16A249;"></i>',
                            'ASSESSMENT_REJECTED' => '<i class="fas fa-times-circle" style="color: #dc3545;"></i>',
                            'QUOTATION_CREATED' => '<i class="fas fa-file-invoice" style="color: #16A249;"></i>',
                            default => '<i class="fas fa-bell" style="color: #16A249;"></i>'
                          };
                          echo $icon;
                          ?>
                        </div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-semibold small"><?= htmlspecialchars($notif['title']) ?></p>
                          <p class="mb-1 small text-muted text-truncate" style="max-width: 220px;">
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
                  <a href="user-all-notifications.php" class="dropdown-item text-center" style="color: #16A249; text-decoration: none; font-size: 12px; padding: 8px 0; display: block; margin-top: 8px;">
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
                  <p class="m-0 fs-14 db-text-secondary">Client</p>
                </div>
              </a>
            </li>
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
      </ul>
    </div>
  </div>

</body>

<!-- ========== SCRIPTS ========== -->
<script>
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));

  // Mark notification as read and navigate
  function markAsRead(notificationId) {
    // Send AJAX request to mark as read
    fetch('mark-notification-read.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'notification_id=' + notificationId
    })
    .then(response => response.text())
    .catch(error => console.error('Error:', error));
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>