<?php
ob_start();
include 'admin-header.php';

// Get filter values from GET parameters
$filter_project = isset($_GET['project_filter']) ? intval($_GET['project_filter']) : 0;
$filter_employee = isset($_GET['employee_filter']) ? intval($_GET['employee_filter']) : 0;

// Fetch all active employees for the dropdown
$employees = [];
$employee_query = "SELECT employee_id, first_name, last_name, position FROM employees WHERE is_archived = 0 AND POSITION NOT IN ('Admin', 'Admin/Secretary') ORDER BY first_name ASC";
$employee_result = mysqli_query($conn, $employee_query);
if ($employee_result) {
  while ($emp = mysqli_fetch_assoc($employee_result)) {
    $employees[] = $emp;
  }
}

// Fetch all active projects for the dropdown
$projects = [];
$project_query = "SELECT project_id, project_name FROM projects WHERE is_archived = 0 ORDER BY project_name ASC";
$project_result = mysqli_query($conn, $project_query);
if ($project_result) {
  while ($proj = mysqli_fetch_assoc($project_result)) {
    $projects[] = $proj;
  }
}

// Handle RESTORE (unarchive)
if (isset($_POST['modal-restore-button'])) {
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to restore tasks.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $restore_id = (int)$_POST['restore_id'];

  // Get task info before restoring
  $task_info_query = "SELECT task_title FROM tasks WHERE task_id = $restore_id";
  $task_info_result = mysqli_query($conn, $task_info_query);
  $task_info = mysqli_fetch_assoc($task_info_result);
  $task_title = $task_info['task_title'];

  // Restore the task by setting is_archived = 0
  $sql = "UPDATE tasks SET is_archived = 0 WHERE task_id = $restore_id";
  if (mysqli_query($conn, $sql)) {
    // LOG ACTIVITY
    log_activity(
      $conn,
      $employee_id,
      $employee_full_name,
      'RESTORE',
      'TASKS',
      $restore_id,
      $task_title,
      "Restored task '$task_title' from archive"
    );

    echo "<script>alert('Task restored successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error restoring task: " . mysqli_error($conn) . "');</script>";
  }
}

// Handle PERMANENT DELETE
if (isset($_POST['modal-permanent-delete-button'])) {
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to delete tasks.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $delete_id = (int)$_POST['delete_id'];

  // Get task info before deleting
  $task_info_query = "SELECT task_title, proof_of_completion FROM tasks WHERE task_id = $delete_id";
  $task_info_result = mysqli_query($conn, $task_info_query);
  $task_info = mysqli_fetch_assoc($task_info_result);
  $task_title = $task_info['task_title'];
  $proof_file = $task_info['proof_of_completion'];

  // Delete proof file if exists
  if (!empty($proof_file)) {
    $proof_path = '../uploads/task_proofs/' . $proof_file;
    if (file_exists($proof_path)) {
      unlink($proof_path);
    }
  }

  // Permanently delete the task
  $sql = "DELETE FROM tasks WHERE task_id = $delete_id";
  if (mysqli_query($conn, $sql)) {
    // LOG ACTIVITY
    log_activity(
      $conn,
      $employee_id,
      $employee_full_name,
      'DELETE',
      'TASKS',
      $delete_id,
      $task_title,
      "Permanently deleted task '$task_title'"
    );

    echo "<script>alert('Task permanently deleted!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error deleting task: " . mysqli_error($conn) . "');</script>";
  }
}

// Build WHERE conditions for statistics and tasks query
$where_conditions = ["is_archived = 1"];

// Apply project filter
if ($filter_project > 0) {
  $where_conditions[] = "project_id = $filter_project";
}

// Apply employee filter (admin only)
if ($is_admin && $filter_employee > 0) {
  $where_conditions[] = "assigned_to_id = $filter_employee";
}

$where_clause = implode(" AND ", $where_conditions);

// Get statistics for ARCHIVED tasks with filters
$stats = [
  'total_tasks' => 0,
  'pending' => 0,
  'in_progress' => 0,
  'completed' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE $where_clause");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_tasks'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Pending' AND $where_clause");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['pending'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'In Progress' AND $where_clause");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['in_progress'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Completed' AND $where_clause");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['completed'] = $row['count'];
}

// Get all ARCHIVED tasks with filters
$tasks = [];
$sql = "SELECT * FROM tasks WHERE $where_clause ORDER BY due_date ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = $row;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Tasks - Admin Dashboard</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />

  <style>
    .sidebar-content-item:nth-child(3) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(3) .sidebar-anchor,
    .sidebar-content-item:nth-child(3) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>

  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">
    <!-- BACK BUTTON -->
    <a href="admin-tasks.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back to Tasks
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Tasks</h1>
        <p class="admin-top-desc">View, restore, or permanently delete archived tasks</p>
      </div>
    </div>

    <!-- STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Tasks</p>
            <p class="mb-0 fs-24 text-primary"><?= $stats['total_tasks'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box-archive fs-32 text-primary"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Pending</p>
            <p class="mb-0 fs-24 text-warning"><?= $stats['pending'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-clock fs-32 text-warning"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">In Progress</p>
            <p class="mb-0 fs-24 text-info"><?= $stats['in_progress'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-spinner fs-32 text-info"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Completed</p>
            <p class="mb-0 fs-24 green-text"><?= $stats['completed'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-check-circle fs-32 green-text"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- ARCHIVED TASKS LIST -->
    <div class="row g-3 mb-2 pb-5">
      <div class="col-12">
        <div class="tasks-container rounded-3 bg-white">
          <div class="tasks-top p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 class="fs-24 mobile-fs-22 mb-0">Archived Tasks</h2>
            <div class="d-flex gap-2 flex-wrap">
              <select name="project_filter" id="projectFilter" class="form-select" style="width: auto; min-width: 150px;">
                <option value="0">All Projects</option>
                <?php foreach ($projects as $proj): ?>
                  <option value="<?= $proj['project_id'] ?>" <?= $filter_project == $proj['project_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($proj['project_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <?php if ($is_admin): ?>
                <select name="employee_filter" id="employeeFilter" class="form-select" style="width: auto; min-width: 150px;">
                  <option value="0">All Employees</option>
                  <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['employee_id'] ?>" <?= $filter_employee == $emp['employee_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>

              <?php if ($filter_project > 0 || $filter_employee > 0): ?>
                <button class="btn btn-secondary" onclick="clearFilters()">
                  <i class="fa-solid fa-times me-1"></i> Clear
                </button>
              <?php endif; ?>
            </div>
          </div>

          <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
              <i class="fa-solid fa-box-archive fs-48 text-muted mb-3"></i>
              <h4 class="text-muted">No Archived Tasks</h4>
              <p class="text-muted"><?= ($filter_project > 0 || $filter_employee > 0) ? 'No archived tasks match the selected filters.' : 'All your tasks are active.' ?></p>
            </div>
          <?php else: ?>
            <div class="px-4 pb-4 pt-3">
              <?php foreach ($tasks as $task): ?>
                <div class="tasks-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4 mb-3">
                  <div class="tasks-details w-100">
                    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                      <h3 class="fs-18 mb-0"><?= htmlspecialchars($task['task_title']) ?></h3>

                      <?php
                      $priority = $task['priority'];
                      $priorityClass = match ($priority) {
                        "High"   => "badge-pill priority-high",
                        "Medium" => "badge-pill priority-medium",
                        "Low"    => "badge-pill priority-low",
                        default  => "badge-pill"
                      };
                      ?>
                      <span class="<?= $priorityClass ?>"><?= $priority ?> Priority</span>

                      <?php
                      $taskStatus = $task['status'];
                      $taskStatusClass = match ($taskStatus) {
                        "Pending"      => "badge-pill taskstatus-pending",
                        "In Progress"  => "badge-pill taskstatus-inprogress",
                        "Completed"    => "badge-pill taskstatus-completed",
                        default        => "badge-pill"
                      };
                      ?>
                      <span class="<?= $taskStatusClass ?>"><?= $taskStatus ?></span>

                    </div>

                    <p class="light-text mb-3"><?= str_replace(['&lt;br&gt;', '&lt;br/&gt;'], '<br>', htmlspecialchars($task['task_desc'])) ?></p>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                      <p class="fs-14 mb-0">
                        <span class="light-text">Assigned to: </span>
                        <?= htmlspecialchars($task['assigned_to']) ?>
                      </p>
                      <p class="fs-14 mb-0">
                        <span class="light-text">Due date: </span>
                        <?= date('m/d/Y', strtotime($task['due_date'])) ?>
                      </p>
                      <p class="fs-14 mb-0">
                        <span class="light-text">Project: </span>
                        <?= htmlspecialchars($task['project_name']) ?>
                      </p>
                    </div>
                  </div>

                  <div class="tasks-actions d-flex flex-column gap-2" style="min-width: 150px; width: 150px;">
                    <button class="btn btn-light border view-task"
                      data-id="<?= $task['task_id'] ?>"
                      data-title="<?= htmlspecialchars($task['task_title']) ?>"
                      data-desc="<?= htmlspecialchars($task['task_desc']) ?>"
                      data-priority="<?= htmlspecialchars($task['priority']) ?>"
                      data-status="<?= htmlspecialchars($task['status']) ?>"
                      data-assigned="<?= htmlspecialchars($task['assigned_to']) ?>"
                      data-project="<?= htmlspecialchars($task['project_name']) ?>"
                      data-due="<?= date('m/d/Y', strtotime($task['due_date'])) ?>"
                      data-proof="<?= htmlspecialchars($task['proof_of_completion'] ?? '') ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#viewTaskModal">
                      View
                    </button>
                    <?php if ($is_admin): ?>
                      <button class="btn btn-success restore-task"
                        data-id="<?= $task['task_id'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#restoreTaskModal">
                        <i class="fa-solid fa-rotate-left me-1"></i> Restore
                      </button>

                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- VIEW TASK MODAL -->
  <div class="modal fade" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">View Archived Task</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-bold">Task Title</label>
            <input type="text" id="viewTaskTitle" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Task Description</label>
            <textarea id="viewTaskDesc" class="form-control" rows="4" readonly></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Priority</label>
              <input type="text" id="viewPriority" class="form-control" readonly>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <input type="text" id="viewStatus" class="form-control" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Assigned To</label>
              <input type="text" id="viewAssignedTo" class="form-control" readonly>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Project</label>
              <input type="text" id="viewProjectName" class="form-control" readonly>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Due Date</label>
            <input type="text" id="viewDueDate" class="form-control" readonly>
          </div>

          <!-- Proof of Completion Section -->
          <div id="proofSection" style="display: none;">
            <hr>
            <h5 class="mb-3">Proof of Completion</h5>
            <div id="proofContent" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>

  <!-- RESTORE MODAL -->
  <div class="modal fade" id="restoreTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Task
          </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="restore_id" id="restoreTaskId">
          <div class="modal-body">
            <h3 class="fs-20 text-center m-0 py-3">Are you sure you want to restore this task?</h3>
            <p class="text-center text-muted mb-0">This task will be moved back to active tasks.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-restore-button" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>



  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function() {
      // Filter functionality
      $('#projectFilter, #employeeFilter').on('change', function() {
        const projectFilter = $('#projectFilter').val();
        const employeeFilter = $('#employeeFilter').val() || 0;

        let url = window.location.pathname + '?';
        const params = [];

        if (projectFilter > 0) {
          params.push('project_filter=' + projectFilter);
        }

        if (employeeFilter > 0) {
          params.push('employee_filter=' + employeeFilter);
        }

        if (params.length > 0) {
          url += params.join('&');
        }

        window.location.href = url;
      });

      // View Task Modal - Populate data
      $('.view-task').on('click', function() {
        $('#viewTaskTitle').val($(this).data('title'));
        $('#viewTaskDesc').val(
          $(this).data('desc').replace(/<br\s*\/?>/gi, '\n')
        );
        $('#viewPriority').val($(this).data('priority'));
        $('#viewStatus').val($(this).data('status'));
        $('#viewAssignedTo').val($(this).data('assigned'));
        $('#viewProjectName').val($(this).data('project'));
        $('#viewDueDate').val($(this).data('due'));

        // Handle proof of completion
        const proof = $(this).data('proof');
        if (proof && proof.trim() !== '') {
          const filepath = '../uploads/task_proofs/' + proof;
          const fileExt = proof.split('.').pop().toLowerCase();

          $('#proofSection').show();

          // Display proof based on file type
          let contentHTML = '';
          if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
            contentHTML = '<img src="' + filepath + '" class="img-fluid" style="max-height: 400px;" />';
          } else if (fileExt === 'pdf') {
            contentHTML = '<iframe src="' + filepath + '" style="width: 100%; height: 400px; border: none;"></iframe>';
          } else if (['docx', 'xlsx'].includes(fileExt)) {
            contentHTML = '<div class="alert alert-info"><i class="fa-solid fa-file me-2"></i>File: <strong>' + proof + '</strong><br><p class="mb-0 mt-2">Click Download to view the file</p></div>';
          } else {
            contentHTML = '<div class="alert alert-warning"><i class="fa-solid fa-file me-2"></i>File: <strong>' + proof + '</strong></div>';
          }

          $('#proofContent').html(contentHTML);
        } else {
          $('#proofSection').hide();
        }
      });

      // Restore Task Modal - Set ID
      $('.restore-task').on('click', function() {
        let taskId = $(this).data('id');
        $('#restoreTaskId').val(taskId);
      });

    });

    // Clear filters function
    function clearFilters() {
      window.location.href = window.location.pathname;
    }
  </script>

</body>

</html>