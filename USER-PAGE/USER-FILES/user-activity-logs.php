<?php
ob_start();
include 'user-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

// Get filter parameters
$module_filter = $_GET['module'] ?? 'ALL';
$action_filter = $_GET['action'] ?? 'ALL';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build SQL query with filters - user can ONLY see their own activities
$where_conditions = ["user_id = $user_id"];

if ($module_filter !== 'ALL') {
  $where_conditions[] = "module = '" . mysqli_real_escape_string($conn, $module_filter) . "'";
}

if ($action_filter !== 'ALL') {
  $where_conditions[] = "action = '" . mysqli_real_escape_string($conn, $action_filter) . "'";
}

if (!empty($date_from)) {
  $where_conditions[] = "DATE(created_at) >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}

if (!empty($date_to)) {
  $where_conditions[] = "DATE(created_at) <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get activity logs for this user only (check if user_id column exists)
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM activity_logs LIKE 'user_id'");
$has_user_column = mysqli_num_rows($column_check) > 0;

if ($has_user_column) {
  $sql = "SELECT * FROM activity_logs $where_clause ORDER BY created_at DESC LIMIT 500";
  $logs_result = mysqli_query($conn, $sql);

  // Get statistics for this user only
  $total_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id"));
  $today_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id AND DATE(created_at) = CURDATE()"));
  $assessment_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id AND module = 'ASSESSMENTS'"));
  $project_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id AND module = 'PROJECTS'"));
  $quotation_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id AND module = 'QUOTATIONS'"));
  $system_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE user_id = $user_id AND module = 'SYSTEM'"));
} else {
  // Table not yet updated, return empty results
  $logs_result = mysqli_query($conn, "SELECT * FROM activity_logs WHERE 1=0");
  $total_logs = 0;
  $today_logs = 0;
  $assessment_logs = 0;
  $project_logs = 0;
  $quotation_logs = 0;
  $system_logs = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Activity Logs</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <style>
    .text-purple {
      color: #6f42c1;
    }
  </style>
</head>

<body>
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="fs-36 mobile-fs-32">My Activity Logs</h1>
        <p class="admin-top-desc">Track your activities and actions</p>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">My Total Activities</p>
          <p class="fw-bold fs-24 mb-0"><?= number_format($total_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Today's Activities</p>
          <p class="fw-bold fs-24 mb-0 text-primary"><?= number_format($today_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Assessment Activities</p>
          <p class="fw-bold fs-24 mb-0 text-info"><?= number_format($assessment_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Project Activities</p>
          <p class="fw-bold fs-24 mb-0 text-purple"><?= number_format($project_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Quotation Activities</p>
          <p class="fw-bold fs-24 mb-0 text-warning"><?= number_format($quotation_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">System Activities</p>
          <p class="fw-bold fs-24 mb-0 green-text"><?= number_format($system_logs) ?></p>
        </div>
      </div>
    </div>

    <div class="border bg-white rounded-3">
      <div class="table-responsive p-4">
        <table id="logsTable" class="table table-hover mb-0">
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <p class="fs-24 mobile-fs-22 mb-0">My Activity Logs</p>
            <div class="d-flex gap-2 flex-wrap">
              <form method="GET" class="d-flex gap-2 flex-wrap" id="filterForm">

                <div>
                  <select name="module" class="form-select filter-input">
                    <option value="ALL" <?= $module_filter === 'ALL' ? 'selected' : '' ?>>All Modules</option>
                    <option value="SYSTEM" <?= $module_filter === 'SYSTEM' ? 'selected' : '' ?>>System</option>
                    <option value="ASSESSMENTS" <?= $module_filter === 'ASSESSMENTS' ? 'selected' : '' ?>>Assessments</option>
                    <option value="PROJECTS" <?= $module_filter === 'PROJECTS' ? 'selected' : '' ?>>Projects</option>
                    <option value="QUOTATIONS" <?= $module_filter === 'QUOTATIONS' ? 'selected' : '' ?>>Quotations</option>
                  </select>
                </div>

                <div>
                  <select name="action" class="form-select filter-input">
                    <option value="ALL" <?= $action_filter === 'ALL' ? 'selected' : '' ?>>All Actions</option>
                    <option value="LOGIN" <?= $action_filter === 'LOGIN' ? 'selected' : '' ?>>Login</option>
                    <option value="LOGOUT" <?= $action_filter === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                    <option value="CREATE" <?= $action_filter === 'CREATE' ? 'selected' : '' ?>>Create</option>
                    <option value="UPDATE" <?= $action_filter === 'UPDATE' ? 'selected' : '' ?>>Update</option>
                    <option value="VIEW" <?= $action_filter === 'VIEW' ? 'selected' : '' ?>>View</option>
                  </select>
                </div>

              </form>

            </div>
          </div>

          <div class="divider my-3"></div>

          <thead>
            <tr>
              <th>#</th>
              <th>Timestamp</th>
              <th>Action</th>
              <th>Module</th>
              <th>Subject</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($logs_result) > 0): ?>
              <?php $index = 1; ?>
              <?php while ($log = mysqli_fetch_assoc($logs_result)): ?>
                <tr>
                  <td><?= $index++ ?></td>
                  <td class="text-nowrap">
                    <small><?= date('M d, Y', strtotime($log['created_at'])) ?></small><br>
                    <small class="text-muted"><?= date('h:i A', strtotime($log['created_at'])) ?></small>
                  </td>
                  <td>
                    <?php
                    $action_class = match ($log['action']) {
                      'LOGIN' => 'badge-pill priority-low',
                      'LOGOUT' => 'badge-pill priority-medium',
                      'CREATE' => 'badge-pill action-create',
                      'UPDATE' => 'badge-pill taskstatus-inprogress',
                      'VIEW' => 'badge-pill taskstatus-completed',
                      default => 'badge-pill bg-secondary text-white'
                    };
                    ?>
                    <span class="<?= $action_class ?>"><?= htmlspecialchars($log['action']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($log['module']) ?></td>
                  <td><?= $log['item_name'] ? htmlspecialchars($log['item_name']) : '-' ?></td>
                  <td><small><?= htmlspecialchars($log['description']) ?></small></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center py-5">
                  <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                  <p class="text-muted">You have no activity logs yet</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <script>
    // Disable DataTables alert popups globally
    $.fn.dataTable.ext.errMode = 'none';

    $(document).ready(function() {
      $('.filter-input').on('change', function() {
        $('#filterForm').submit();
      });

      $('#logsTable').DataTable({
        order: [
          [1, 'desc']
        ],
        pageLength: 25,
        language: {
          emptyTable: "You have no activity logs yet"
        }
      });
    });
  </script>

</body>

</html>