<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

date_default_timezone_set('Asia/Manila');
include '../../INCLUDES/db-con.php';

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

/* MARK ALL AS READ */
if (isset($_GET['mark_all_read'])) {
  mysqli_query(
    $conn,
  "UPDATE notifications 
    SET is_read = 1 
    WHERE recipient_id = $user_id
    AND type IN ('ASSESSMENT_ACCEPTED','ASSESSMENT_REJECTED','QUOTATION_CREATED')"
  );

  header("Location: user-all-notifications.php");
  exit;
}


/* DATE FILTER */
$date_filter = $_GET['date_filter'] ?? 'ALL';
$date_condition = '';

if ($date_filter === 'TODAY') {
  $date_condition = "AND DATE(created_at) = CURDATE()";
} elseif ($date_filter === 'WEEK') {
  $date_condition = "AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($date_filter === 'MONTH') {
  $date_condition = "AND YEAR(created_at) = YEAR(CURDATE())
                    AND MONTH(created_at) = MONTH(CURDATE())";
}

/* FETCH USER NOTIFICATIONS */
$notif_sql = "
  SELECT * FROM notifications
  WHERE recipient_id = $user_id
  AND type IN ('ASSESSMENT_ACCEPTED','ASSESSMENT_REJECTED','QUOTATION_CREATED')
  $date_condition
  ORDER BY created_at DESC
";
$notif_result = mysqli_query($conn, $notif_sql);

$notifications = [];
if ($notif_result) {
  while ($row = mysqli_fetch_assoc($notif_result)) {
    $notifications[] = $row;
  }
}

/* MARK AS READ + REDIRECT */
if (isset($_GET['mark_read'])) {
  $notif_id = intval($_GET['mark_read']);

  mysqli_query(
    $conn,
    "UPDATE notifications 
    SET is_read = 1 
    WHERE notification_id = $notif_id 
    AND recipient_id = $user_id"
  );

  $link_result = mysqli_query(
    $conn,
    "SELECT link FROM notifications WHERE notification_id = $notif_id"
  );

  if ($link_result && $row = mysqli_fetch_assoc($link_result)) {
    header("Location: " . $row['link']);
    exit;
  }
}

$unread_result = mysqli_query(
  $conn,
  "SELECT COUNT(*) AS unread_count
  FROM notifications
  WHERE recipient_id = $user_id
  AND is_read = 0
  AND type IN ('ASSESSMENT_ACCEPTED','ASSESSMENT_REJECTED','QUOTATION_CREATED')"
);

$unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Notifications</title>

  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <link rel="stylesheet" href="../USER-CSS/user-header.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      padding-top: 80px;
      background-color: #f8f9fa;
    }
    .notification-card {
      transition: all 0.2s;
      cursor: pointer;
    }
    .notification-card:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .unread-card {
      background-color: #e8f5ee;
      border-left: 4px solid #16A249;
    }
  </style>
</head>

<body>

<?php include 'user-header.php'; ?>

<main class="container-xxl px-4 min-vh-100 -mt-5">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h1 class="fs-36 mobile-fs-32">All Notifications</h1>
      <p class="text-muted">View all your notifications</p>
    </div>

    <!-- DATE FILTER -->
    <div class="d-flex gap-2">
      <form method="GET">
        <select name="date_filter" class="form-select" onchange="this.form.submit()">
          <option value="ALL" <?= $date_filter === 'ALL' ? 'selected' : '' ?>>All Dates</option>
          <option value="TODAY" <?= $date_filter === 'TODAY' ? 'selected' : '' ?>>Today</option>
          <option value="WEEK" <?= $date_filter === 'WEEK' ? 'selected' : '' ?>>This Week</option>
          <option value="MONTH" <?= $date_filter === 'MONTH' ? 'selected' : '' ?>>This Month</option>
        </select>
      </form>
        <?php if ($unread_count > 0): ?>
        <a href="?mark_all_read=1" class="btn btn-success text-white">
          <i class="fas fa-check-double me-1"></i> Mark all as read
        </a>
      <?php endif; ?>
    </div>

  </div>

  <!-- NOTIFICATIONS -->
  <?php if (empty($notifications)): ?>
    <div class="text-center py-5 bg-white rounded-3">
      <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
      <h4 class="text-muted">No Notifications</h4>
      <p class="text-muted">You're all caught up!</p>
    </div>
  <?php else: ?>

    <?php foreach ($notifications as $notif): ?>
      <div
        class="notification-card p-3 mb-3 bg-white rounded-3 border <?= $notif['is_read'] ? '' : 'unread-card' ?>"
        onclick="window.location='?mark_read=<?= $notif['notification_id'] ?>'">

        <div class="d-flex gap-3 align-items-start">

          <!-- ICON -->
          <div style="width:48px;height:48px;background:#f0f0f0;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            <?php
              echo match ($notif['type']) {
                'ASSESSMENT_ACCEPTED' =>
                  '<i class="fas fa-check-circle text-success fa-lg"></i>',
                'ASSESSMENT_REJECTED' =>
                  '<i class="fas fa-times-circle text-danger fa-lg"></i>',
                'QUOTATION_CREATED' =>
                  '<i class="fas fa-file-invoice text-success fa-lg"></i>',
                default =>
                  '<i class="fas fa-bell text-success fa-lg"></i>'
              };
            ?>
          </div>

          <!-- CONTENT -->
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <h5 class="mb-0"><?= htmlspecialchars($notif['title']) ?></h5>
              <?php if (!$notif['is_read']): ?>
                <span class="badge bg-success">New</span>
              <?php endif; ?>
            </div>

            <p class="mb-2 text-muted"><?= htmlspecialchars($notif['message']) ?></p>

            <small class="text-muted">
              <i class="far fa-clock me-1"></i>
              <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
            </small>
          </div>

        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>

</main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet"

</body>
</html>
