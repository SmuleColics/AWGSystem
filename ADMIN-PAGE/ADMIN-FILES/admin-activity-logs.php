<?php

ob_start();
include 'admin-header.php';

// Get filter parameters
$module_filter = $_GET['module'] ?? 'ALL';
$action_filter = $_GET['action'] ?? 'ALL';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build SQL query with filters
$where_conditions = [];

// If employee (non-admin), only show their own activities
if (!$is_admin) {
  $where_conditions[] = "employee_id = $employee_id";
}

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

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get activity logs
$sql = "SELECT * FROM activity_logs $where_clause ORDER BY created_at DESC LIMIT 500";
$logs_result = mysqli_query($conn, $sql);

// Get statistics based on user role
if ($is_admin) {
  $total_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs"));
  $today_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE DATE(created_at) = CURDATE()"));
  $inventory_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE module = 'INVENTORY'"));
  $assessment_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE module = 'ASSESSMENTS'"));
  $task_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE module = 'TASKS'"));
  $project_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE module = 'PROJECTS'"));
} else {
  // Employee only sees their own stats
  $total_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id"));
  $today_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id AND DATE(created_at) = CURDATE()"));
  $inventory_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id AND module = 'INVENTORY'"));
  $assessment_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id AND module = 'ASSESSMENTS'"));
  $task_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id AND module = 'TASKS'"));
  $project_logs = mysqli_num_rows(mysqli_query($conn, "SELECT log_id FROM activity_logs WHERE employee_id = $employee_id AND module = 'PROJECTS'"));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Logs</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
</head>

<body>
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="fs-36 mobile-fs-32"><?= $is_admin ? 'Activity Logs' : 'My Activity Logs' ?></h1>
        <p class="admin-top-desc"><?= $is_admin ? 'Track all system activities and changes' : 'Track your activities and actions' ?></p>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0"><?= $is_admin ? 'Total Activities' : 'My Total Activities' ?></p>
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
          <p class="light-text fs-14 mb-0">Inventory Activities</p>
          <p class="fw-bold fs-24 mb-0 green-text"><?= number_format($inventory_logs) ?></p>
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
          <p class="light-text fs-14 mb-0">Task Activities</p>
          <p class="fw-bold fs-24 mb-0 text-warning"><?= number_format($task_logs) ?></p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 inventory-category rounded">
          <p class="light-text fs-14 mb-0">Project Activities</p>
          <p class="fw-bold fs-24 mb-0 text-purple"><?= number_format($project_logs) ?></p>
        </div>
      </div>
    </div>

    <div class="border bg-white rounded-3">
      <div class="table-responsive p-4">
        <table id="logsTable" class="table table-hover mb-0">
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <p class="fs-24 mobile-fs-22 mb-0"><?= $is_admin ? 'Activity Logs' : 'My Activity Logs' ?></p>
            <div class="d-flex gap-2 flex-wrap">
              <form method="GET" class="d-flex gap-2 flex-wrap" id="filterForm">

                <div>
                  <select name="module" class="form-select filter-input">
                    <option value="ALL" <?= $module_filter === 'ALL' ? 'selected' : '' ?>>All Modules</option>
                    <option value="SYSTEM" <?= $module_filter === 'SYSTEM' ? 'selected' : '' ?>>System</option>
                    <option value="INVENTORY" <?= $module_filter === 'INVENTORY' ? 'selected' : '' ?>>Inventory</option>
                    <option value="EMPLOYEES" <?= $module_filter === 'EMPLOYEES' ? 'selected' : '' ?>>Employees</option>
                    <option value="TASKS" <?= $module_filter === 'TASKS' ? 'selected' : '' ?>>Tasks</option>
                    <option value="ASSESSMENTS" <?= $module_filter === 'ASSESSMENTS' ? 'selected' : '' ?>>Assessments</option>
                    <option value="PROJECTS" <?= $module_filter === 'PROJECTS' ? 'selected' : '' ?>>Projects</option>
                  </select>
                </div>

                <div>
                  <select name="action" class="form-select filter-input">
                    <option value="ALL" <?= $action_filter === 'ALL' ? 'selected' : '' ?>>All Actions</option>
                    <option value="LOGIN" <?= $action_filter === 'LOGIN' ? 'selected' : '' ?>>Login</option>
                    <option value="LOGOUT" <?= $action_filter === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                    <option value="CREATE" <?= $action_filter === 'CREATE' ? 'selected' : '' ?>>Create</option>
                    <option value="UPDATE" <?= $action_filter === 'UPDATE' ? 'selected' : '' ?>>Update</option>
                    <option value="ACCEPT" <?= $action_filter === 'ACCEPT' ? 'selected' : '' ?>>Accept</option>
                    <option value="REJECT" <?= $action_filter === 'REJECT' ? 'selected' : '' ?>>Reject</option>
                    <option value="ARCHIVE" <?= $action_filter === 'ARCHIVE' ? 'selected' : '' ?>>Archive</option>
                    <option value="RESTORE" <?= $action_filter === 'RESTORE' ? 'selected' : '' ?>>Restore</option>
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
              <?php if ($is_admin): ?>
                <th>User Name</th>
              <?php endif; ?>
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
                  <?php if ($is_admin): ?>
                    <td><?= htmlspecialchars($log['employee_name']) ?></td>
                  <?php endif; ?>
                  <td>
                    <?php
                    $action_class = match ($log['action']) {
                      'LOGIN' => 'badge-pill priority-low',
                      'LOGOUT' => 'badge-pill priority-medium',
                      'CREATE' => 'badge-pill action-create',
                      'UPDATE' => 'badge-pill taskstatus-inprogress',
                      'ACCEPT' => 'badge-pill taskstatus-completed',
                      'REJECT' => 'badge-pill priority-high',
                      'ARCHIVE' => 'badge-pill bg-warning text-white',
                      'RESTORE' => 'badge-pill priority-low',
                      'DELETE' => 'badge-pill bg-danger text-white',
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
                <td colspan="<?= $is_admin ? '7' : '6' ?>" class="text-center py-5">
                  <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                  <p class="text-muted"><?= $is_admin ? 'No activity logs found' : 'You have no activity logs yet' ?></p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

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
          emptyTable: "<?= $is_admin ? 'No activity logs found' : 'You have no activity logs yet' ?>"
        }
      });
    });
  </script>


</body>

</html>