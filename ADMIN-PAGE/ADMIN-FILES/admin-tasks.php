<?php
ob_start();
include 'admin-header.php';

// Initialize variables
$errors = [];
$success = false;

// Fetch all active employees for the dropdown
$employees = [];
$employee_query = "SELECT employee_id, first_name, last_name, position FROM employees WHERE is_archived = 0 AND POSITION NOT IN ('Admin', 'Admin/Secretary') ORDER BY first_name ASC";
$employee_result = mysqli_query($conn, $employee_query);
if ($employee_result) {
  while ($emp = mysqli_fetch_assoc($employee_result)) {
    $employees[] = $emp;
  }
}

// Handle form submission for ADD TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {

  if (!$is_admin) {
    echo "<script>alert('You do not have permission to add tasks.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $task_title = trim($_POST['task_title'] ?? '');
  $task_desc = trim($_POST['task_desc'] ?? '');
  $priority = trim($_POST['priority'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $assigned_to_id = intval($_POST['assigned_to'] ?? 0);  // CONVERT TO INT HERE
  $project_name = trim($_POST['project_name'] ?? '');
  $due_date = trim($_POST['due_date'] ?? '');

  // Validation
  if (empty($task_title)) {
    $errors['task_title'] = 'Task title is required';
  }

  if (empty($task_desc)) {
    $errors['task_desc'] = 'Task description is required';
  }

  if (empty($priority)) {
    $errors['priority'] = 'Priority is required';
  }

  if (empty($status)) {
    $errors['status'] = 'Status is required';
  }

  if ($assigned_to_id === 0) {
    $errors['assigned_to'] = 'Assigned to is required';
  }

  if (empty($project_name)) {
    $errors['project_name'] = 'Project name is required';
  }

  if (empty($due_date)) {
    $errors['due_date'] = 'Due date is required';
  }

  // If no errors, insert into database
  if (empty($errors)) {
    // Get assigned employee name
    $assigned_employee_name = '';
    foreach ($employees as $emp) {
      if ($emp['employee_id'] == $assigned_to_id) {
        $assigned_employee_name = $emp['first_name'] . ' ' . $emp['last_name'];
        break;
      }
    }

    $task_title = mysqli_real_escape_string($conn, $task_title);
    $task_desc = mysqli_real_escape_string($conn, $task_desc);
    $priority = mysqli_real_escape_string($conn, $priority);
    $status = mysqli_real_escape_string($conn, $status);
    $assigned_to_name = mysqli_real_escape_string($conn, $assigned_employee_name);
    $project_name = mysqli_real_escape_string($conn, $project_name);
    $due_date = mysqli_real_escape_string($conn, $due_date);

    $sql = "INSERT INTO tasks (task_title, task_desc, priority, status, assigned_to_id, assigned_to, project_name, due_date, is_archived) 
                VALUES ('$task_title', '$task_desc', '$priority', '$status', $assigned_to_id, '$assigned_to_name', '$project_name', '$due_date', 0)";

    if (mysqli_query($conn, $sql)) {
      $task_id = mysqli_insert_id($conn);
      
      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'CREATE',
        'TASKS',
        $task_id,
        $task_title,
        "Created task '$task_title' assigned to $assigned_employee_name"
      );

      // CREATE NOTIFICATION FOR ASSIGNED EMPLOYEE
      create_notification(
        $conn,
        $assigned_to_id,           // recipient_id (INTEGER)
        $employee_id,              // sender_id (INTEGER)
        $employee_full_name,       // sender_name (STRING)
        'TASK_ASSIGNED',           // type (STRING)
        'New Task Assigned',       // title (STRING)
        "You have been assigned a new task: $task_title", // message (STRING)
        'admin-tasks.php',         // link (STRING)
        $task_id                   // related_id (INTEGER)
      );

      // CREATE NOTIFICATION FOR ADMIN WHO CREATED THE TASK (to confirm creation)
      create_notification(
        $conn,
        $employee_id,              // recipient_id - the admin who created it (INTEGER)
        $employee_id,              // sender_id (INTEGER)
        $employee_full_name,       // sender_name (STRING)
        'TASK_ASSIGNED',           // type (STRING)
        'Task Created',            // title (STRING)
        "You created task: $task_title and assigned it to $assigned_employee_name", // message (STRING)
        'admin-tasks.php',         // link (STRING)
        $task_id                   // related_id (INTEGER)
      );

      $success = true;
      echo "<script>
                alert('Task added successfully!');
                window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
              </script>";
      exit;
    } else {
      $errors['database'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

// Handle form submission for EDIT TASK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {

  if (!$is_admin) {
    echo "<script>alert('You do not have permission to edit tasks.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $task_id = intval($_POST['task_id']);
  $task_title = trim($_POST['task_title'] ?? '');
  $task_desc = trim($_POST['task_desc'] ?? '');
  $priority = trim($_POST['priority'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $assigned_to_id = intval($_POST['assigned_to'] ?? 0);  // CONVERT TO INT HERE
  $project_name = trim($_POST['project_name'] ?? '');
  $due_date = trim($_POST['due_date'] ?? '');

  // Validation
  if (empty($task_title)) {
    $errors['task_title'] = 'Task title is required';
  }

  if (empty($task_desc)) {
    $errors['task_desc'] = 'Task description is required';
  }

  if (empty($priority)) {
    $errors['priority'] = 'Priority is required';
  }

  if (empty($status)) {
    $errors['status'] = 'Status is required';
  }

  if ($assigned_to_id === 0) {
    $errors['assigned_to'] = 'Assigned to is required';
  }

  if (empty($project_name)) {
    $errors['project_name'] = 'Project name is required';
  }

  if (empty($due_date)) {
    $errors['due_date'] = 'Due date is required';
  }

  // If no errors, update the database
  if (empty($errors)) {
    // Get assigned employee name
    $assigned_employee_name = '';
    foreach ($employees as $emp) {
      if ($emp['employee_id'] == $assigned_to_id) {
        $assigned_employee_name = $emp['first_name'] . ' ' . $emp['last_name'];
        break;
      }
    }

    $task_title = mysqli_real_escape_string($conn, $task_title);
    $task_desc = mysqli_real_escape_string($conn, $task_desc);
    $priority = mysqli_real_escape_string($conn, $priority);
    $status = mysqli_real_escape_string($conn, $status);
    $assigned_to_name = mysqli_real_escape_string($conn, $assigned_employee_name);
    $project_name = mysqli_real_escape_string($conn, $project_name);
    $due_date = mysqli_real_escape_string($conn, $due_date);

    $sql = "UPDATE tasks SET 
                task_title = '$task_title',
                task_desc = '$task_desc',
                priority = '$priority',
                status = '$status',
                assigned_to_id = $assigned_to_id,
                assigned_to = '$assigned_to_name',
                project_name = '$project_name',
                due_date = '$due_date'
            WHERE task_id = $task_id";

    if (mysqli_query($conn, $sql)) {
      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'UPDATE',
        'TASKS',
        $task_id,
        $task_title,
        "Updated task '$task_title'"
      );

      // CREATE NOTIFICATION FOR ASSIGNED EMPLOYEE
      create_notification(
        $conn,
        $assigned_to_id,           // recipient_id (INTEGER)
        $employee_id,              // sender_id (INTEGER)
        $employee_full_name,       // sender_name (STRING)
        'TASK_UPDATED',            // type (STRING)
        'Task Updated',            // title (STRING)
        "Task has been updated: $task_title", // message (STRING)
        'admin-tasks.php',         // link (STRING)
        $task_id                   // related_id (INTEGER)
      );

      // CREATE NOTIFICATION FOR ADMIN WHO UPDATED THE TASK
      create_notification(
        $conn,
        $employee_id,              // recipient_id - the admin who updated it (INTEGER)
        $employee_id,              // sender_id (INTEGER)
        $employee_full_name,       // sender_name (STRING)
        'TASK_UPDATED',            // type (STRING)
        'Task Updated',            // title (STRING)
        "You updated task: $task_title", // message (STRING)
        'admin-tasks.php',         // link (STRING)
        $task_id                   // related_id (INTEGER)
      );

      echo "<script>
                alert('Task updated successfully!');
                window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
              </script>";
      exit;
    } else {
      $errors['database'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

// Handle ARCHIVE
if (isset($_POST['modal-archive-button'])) {
  
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to archive tasks.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $archive_id = (int)$_POST['archive_id'];

  // Get task info before archiving
  $task_info_query = "SELECT task_title FROM tasks WHERE task_id = $archive_id";
  $task_info_result = mysqli_query($conn, $task_info_query);
  $task_info = mysqli_fetch_assoc($task_info_result);
  $task_title = $task_info['task_title'];

  $sql = "UPDATE tasks SET is_archived = 1 WHERE task_id = $archive_id";
  if (mysqli_query($conn, $sql)) {
    // LOG ACTIVITY
    log_activity(
      $conn,
      $employee_id,
      $employee_full_name,
      'ARCHIVE',
      'TASKS',
      $archive_id,
      $task_title,
      "Archived task '$task_title'"
    );

    echo "<script>alert('Task archived successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error archiving task: " . mysqli_error($conn) . "');</script>";
  }
}

// Get statistics (only non-archived tasks)
$stats = [
  'total_tasks' => 0,
  'pending' => 0,
  'in_progress' => 0,
  'completed' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_tasks'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Pending' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['pending'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'In Progress' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['in_progress'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Completed' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['completed'] = $row['count'];
}

// Get all tasks (only non-archived)
$tasks = [];
$sql = "SELECT * FROM tasks WHERE is_archived = 0 ORDER BY due_date ASC";
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
  <title>Admin Dashboard - Tasks</title>
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
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Tasks</h1>
        <p class="admin-top-desc">Manage and assign tasks to employees</p>
      </div>
      <?php if ($is_admin): ?>
      <div class="d-flex gap-2 flex-column flex-md-row">
        <button class="btn green-bg text-white add-item-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addTasksModal">
          <i class="fa-solid fa-plus me-1"></i> Add <span class="d-none d-md-block ms-1">Task</span>
        </button>
        <a href="admin-archive-tasks.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Tasks</span>
        </a>
      </div>
      <?php endif; ?>
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
            <i class="fa-solid fa-tasks fs-32 text-primary"></i>
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

    <!-- TASKS LIST -->
    <div class="row g-3 mb-2">
      <div class="col-12">
        <div class="tasks-container rounded-3 bg-white">
          <div class="tasks-top p-4 border-bottom">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Tasks</h2>
          </div>

          <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
              <i class="fa-solid fa-tasks fs-48 text-muted mb-3"></i>
              <h4 class="text-muted">No Tasks Available</h4>
              <p class="text-muted">Add a new task to get started.</p>
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

                    <p class="light-text mb-3"><?= htmlspecialchars($task['task_desc']) ?></p>

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

                  <div class="tasks-actions d-flex flex-column gap-2">
                    <button class="btn btn-light border edit-task"
                      data-id="<?= $task['task_id'] ?>"
                      data-title="<?= htmlspecialchars($task['task_title']) ?>"
                      data-desc="<?= htmlspecialchars($task['task_desc']) ?>"
                      data-priority="<?= htmlspecialchars($task['priority']) ?>"
                      data-status="<?= htmlspecialchars($task['status']) ?>"
                      data-assigned="<?= $task['assigned_to_id'] ?>"
                      data-project="<?= htmlspecialchars($task['project_name']) ?>"
                      data-due="<?= $task['due_date'] ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#editTaskModal">
                      Edit
                    </button>
                    <?php if ($is_admin): ?>
                    <button class="btn btn-danger border archive-task"
                      data-id="<?= $task['task_id'] ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#archiveTaskModal">
                      Archive
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

  <!-- ADD TASK MODAL -->
  <div class="modal fade" id="addTasksModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add New Task</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" action="">
          <div class="modal-body">

            <div class="mb-3">
              <label class="form-label">Task Title</label>
              <input type="text" name="task_title" class="form-control" placeholder="Install CCTV Cameras" value="<?= $_POST['task_title'] ?? '' ?>">
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['task_title']) ? 'block' : 'none' ?>">
                <?= $errors['task_title'] ?? 'This field is required' ?>
              </p>
            </div>

            <div class="mb-3">
              <label class="form-label">Task Description</label>
              <textarea name="task_desc" class="form-control" rows="3" placeholder="Complete Installation at Building A"><?= $_POST['task_desc'] ?? '' ?></textarea>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['task_desc']) ? 'block' : 'none' ?>">
                <?= $errors['task_desc'] ?? 'This field is required' ?>
              </p>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                  <option value="">Select Priority</option>
                  <option value="High" <?= (isset($_POST['priority']) && $_POST['priority'] == 'High') ? 'selected' : '' ?>>High</option>
                  <option value="Medium" <?= (isset($_POST['priority']) && $_POST['priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                  <option value="Low" <?= (isset($_POST['priority']) && $_POST['priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['priority']) ? 'block' : 'none' ?>">
                  <?= $errors['priority'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="">Select Status</option>
                  <option value="Pending" <?= (isset($_POST['status']) && $_POST['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                  <option value="In Progress" <?= (isset($_POST['status']) && $_POST['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                  <option value="Completed" <?= (isset($_POST['status']) && $_POST['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['status']) ? 'block' : 'none' ?>">
                  <?= $errors['status'] ?? 'This field is required' ?>
                </p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Assign To Employee</label>
                <select name="assigned_to" class="form-select">
                  <option value="">Select Employee</option>
                  <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['employee_id'] ?>" <?= (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $emp['employee_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?> - <?= htmlspecialchars($emp['position']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['assigned_to']) ? 'block' : 'none' ?>">
                  <?= $errors['assigned_to'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Project</label>
                <input type="text" name="project_name" class="form-control" placeholder="Security System Installation" value="<?= $_POST['project_name'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['project_name']) ? 'block' : 'none' ?>">
                  <?= $errors['project_name'] ?? 'This field is required' ?>
                </p>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" class="form-control" value="<?= $_POST['due_date'] ?? '' ?>">
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['due_date']) ? 'block' : 'none' ?>">
                <?= $errors['due_date'] ?? 'This field is required' ?>
              </p>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_task" class="btn btn-green text-white">Save Task</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- EDIT TASK MODAL -->
  <div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Edit Task</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" action="">
          <input type="hidden" name="task_id" id="editTaskId">

          <div class="modal-body">

            <div class="mb-3">
              <label class="form-label">Task Title</label>
              <input type="text" name="task_title" id="editTaskTitle" class="form-control" placeholder="Install CCTV Cameras">
            </div>

            <div class="mb-3">
              <label class="form-label">Task Description</label>
              <textarea name="task_desc" id="editTaskDesc" class="form-control" rows="3" placeholder="Complete Installation at Building A"></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Priority</label>
                <select name="priority" id="editPriority" class="form-select">
                  <option value="High">High</option>
                  <option value="Medium">Medium</option>
                  <option value="Low">Low</option>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="editStatus" class="form-select">
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Assign To Employee</label>
                <select name="assigned_to" id="editAssignedTo" class="form-select">
                  <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['employee_id'] ?>">
                      <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?> - <?= htmlspecialchars($emp['position']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Project</label>
                <input type="text" name="project_name" id="editProjectName" class="form-control" placeholder="Security System Installation">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" id="editDueDate" class="form-control">
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="edit_task" class="btn btn-green text-white">Update Task</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- ARCHIVE MODAL -->
  <div class="modal fade" id="archiveTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Archive Task</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="archive_id" id="archiveTaskId">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this task?</h3>
            <p class="text-center text-muted">Archived tasks can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-archive-button" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function() {
      // Edit Task Modal - Populate data
      $('.edit-task').on('click', function() {
        $('#editTaskId').val($(this).data('id'));
        $('#editTaskTitle').val($(this).data('title'));
        $('#editTaskDesc').val($(this).data('desc'));
        $('#editPriority').val($(this).data('priority'));
        $('#editStatus').val($(this).data('status'));
        $('#editAssignedTo').val($(this).data('assigned'));
        $('#editProjectName').val($(this).data('project'));
        $('#editDueDate').val($(this).data('due'));
      });

      // Archive Task Modal - Set ID
      $('.archive-task').on('click', function() {
        let taskId = $(this).data('id');
        $('#archiveTaskId').val(taskId);
      });

      <?php if (!empty($errors)): ?>
        // If there are errors, reopen the modal
        $('#addTasksModal').modal('show');
      <?php endif; ?>
    });
  </script>

</body>

</html>