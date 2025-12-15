<?php


include 'admin-header.php';
// include '../../INCLUDES/notifications.php';

// Fetch all notifications for admin - EXCLUDE client-side notification types
$notif_sql = "SELECT * FROM notifications 
              WHERE recipient_id = $employee_id 
              AND type NOT IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')
              ORDER BY created_at DESC";
$notif_result = mysqli_query($conn, $notif_sql);

$all_notifications = [];
if ($notif_result) {
  while ($notif = mysqli_fetch_assoc($notif_result)) {
    $all_notifications[] = $notif;
  }
}

if (isset($_GET['mark_read'])) {
  $notif_id = intval($_GET['mark_read']);
  $mark_read_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = $notif_id AND recipient_id = $employee_id";
  mysqli_query($conn, $mark_read_sql);
  
  // Get the link from the notification
  $link_sql = "SELECT link FROM notifications WHERE notification_id = $notif_id";
  $link_result = mysqli_query($conn, $link_sql);
  if ($link_result && $link_row = mysqli_fetch_assoc($link_result)) {
    header('Location: ' . $link_row['link']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Notifications</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .notification-card {
      transition: all 0.2s;
      cursor: pointer;
    }

    .notification-card:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .unread-card {
      background-color: #e3f2fd;
      border-left: 4px solid #2196f3;
    }
  </style>
</head>

<body>
  <main id="main" class="container-xxl text-dark px-4 py-4 min-vh-100">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
      <div>
        <h1 class="fs-36 mobile-fs-32">All Notifications</h1>
        <p class="admin-top-desc">View all your notifications</p>
      </div>
      <?php if ($unread_count > 0): ?>
        <a href="mark-all-read.php" class="btn btn-green text-white">
          <i class="fas fa-check-double me-1"></i> Mark all as read
        </a>
      <?php endif; ?>
    </div>

    <div class="row">
      <div class="col-12">
        <?php if (empty($all_notifications)): ?>
          <div class="text-center py-5 bg-white rounded-3">
            <i class="fas fa-bell-slash fa-4x light-text mb-3"></i>
            <h4 class="light-text">No Notifications</h4>
            <p class="light-text">You're all caught up!</p>
          </div>
        <?php else: ?>
          <?php foreach ($all_notifications as $notif): ?>
            <div class="notification-card p-3 mb-3 bg-white rounded-3 border <?= $notif['is_read'] ? '' : 'unread-card' ?>"
              onclick="window.location='<?= $notif['link'] ? htmlspecialchars($notif['link']) . '?mark_read=' . $notif['notification_id'] : '#' ?>'">
              <div class="d-flex gap-3 align-items-start">
                <div class="notif-icon" style="width: 48px; height: 48px; background-color: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                  <?php
                  $icon = match ($notif['type']) {
                    'TASK_ASSIGNED' => '<i class="fas fa-tasks green-text fa-lg"></i>',
                    'TASK_UPDATED' => '<i class="fas fa-edit green-text fa-lg"></i>',
                    'TASK_COMPLETED' => '<i class="fas fa-check-circle green-text fa-lg"></i>',
                    default => '<i class="fas fa-bell green-text fa-lg"></i>'
                  };
                  echo $icon;
                  ?>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h5 class="mb-0"><?= htmlspecialchars($notif['title']) ?></h5>
                    <?php if (!$notif['is_read']): ?>
                      <span class="badge-pill priority-low">New</span>
                    <?php endif; ?>
                  </div>
                  <p class="mb-2 light-text"><?= htmlspecialchars($notif['message']) ?></p>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="light-text">
                      <i class="far fa-clock me-1"></i>
                      <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                    </small>
                    <?php if ($notif['sender_name']): ?>
                      <small class="light-text">
                        <i class="far fa-user me-1"></i>
                        <?= htmlspecialchars($notif['sender_name']) ?>
                      </small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </main>
</body>

</html>