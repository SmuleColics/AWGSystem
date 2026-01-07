<?php
ob_start();
include 'admin-header.php';

// Initialize variables
$errors = [];
$success = false;

// Fetch all active employees for the dropdown
$employees = [];
$employee_query = "SELECT employee_id, first_name, last_name, position FROM employees WHERE is_archived = 0 AND POSITION NOT IN ('Admin', 'Admin/Secretary', 'Super Admin') ORDER BY first_name ASC";
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

// Get filter values from GET parameters
$filter_project = isset($_GET['project_filter']) ? intval($_GET['project_filter']) : 0;
$filter_employee = isset($_GET['employee_filter']) ? intval($_GET['employee_filter']) : 0;

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
  $assigned_to_id = intval($_POST['assigned_to'] ?? 0);
  $project_id = intval($_POST['project_id'] ?? 0);
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

  if ($project_id === 0) {
    $errors['project_id'] = 'Project is required';
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

    // Get project name from projects table
    $project_name = '';
    foreach ($projects as $proj) {
      if ($proj['project_id'] == $project_id) {
        $project_name = $proj['project_name'];
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

    // UPDATED: Include project_id in INSERT
    $sql = "INSERT INTO tasks (task_title, task_desc, priority, status, assigned_to_id, assigned_to, project_id, project_name, due_date, is_archived) 
            VALUES ('$task_title', '$task_desc', '$priority', '$status', $assigned_to_id, '$assigned_to_name', $project_id, '$project_name', '$due_date', 0)";

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
        $assigned_to_id,
        $employee_id,
        $employee_full_name,
        'TASK_ASSIGNED',
        'New Task Assigned',
        "You have been assigned a new task: $task_title",
        'admin-tasks.php',
        $task_id
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

// REPLACE the EDIT TASK section with this:
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
  $assigned_to_id = intval($_POST['assigned_to'] ?? 0);
  $project_id = intval($_POST['project_id'] ?? 0);
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

  if ($project_id === 0) {
    $errors['project_id'] = 'Project is required';
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

    // Get project name from projects table
    $project_name = '';
    foreach ($projects as $proj) {
      if ($proj['project_id'] == $project_id) {
        $project_name = $proj['project_name'];
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

    // UPDATED: Include project_id in UPDATE
    $sql = "UPDATE tasks SET 
            task_title = '$task_title',
            task_desc = '$task_desc',
            priority = '$priority',
            status = '$status',
            assigned_to_id = $assigned_to_id,
            assigned_to = '$assigned_to_name',
            project_id = $project_id,
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
        $assigned_to_id,
        $employee_id,
        $employee_full_name,
        'TASK_UPDATED',
        'Task Updated',
        "Task has been updated: $task_title",
        'admin-tasks.php',
        $task_id
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

// Handle form submission for EDIT TASK (Admin only)
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
  $assigned_to_id = intval($_POST['assigned_to'] ?? 0);
  $project_id = intval($_POST['project_id'] ?? 0);
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

  if ($project_id === 0) {
    $errors['project_id'] = 'Project is required';
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

    // Get project name from projects table
    $project_name = '';
    foreach ($projects as $proj) {
      if ($proj['project_id'] == $project_id) {
        $project_name = $proj['project_name'];
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
        $assigned_to_id,
        $employee_id,
        $employee_full_name,
        'TASK_UPDATED',
        'Task Updated',
        "Task has been updated: $task_title",
        'admin-tasks.php',
        $task_id
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

// Handle form submission for UPDATE TASK STATUS (Employee)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_status'])) {

  $task_id = intval($_POST['task_id']);
  $status = trim($_POST['status'] ?? '');
  $proof_file = $_FILES['proof_of_completion'] ?? null;

  // Check if this task is assigned to the current employee
  $check_query = "SELECT assigned_to_id FROM tasks WHERE task_id = $task_id";
  $check_result = mysqli_query($conn, $check_query);
  $task_check = mysqli_fetch_assoc($check_result);

  if ($task_check['assigned_to_id'] != $employee_id) {
    echo "<script>alert('You can only update tasks assigned to you.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  // Validation
  if (empty($status)) {
    $errors['status'] = 'Status is required';
  }

  // Handle file upload
  $proof_filename = null;
  if ($proof_file && $proof_file['size'] > 0) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];
    $file_ext = strtolower(pathinfo($proof_file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
      $errors['proof_of_completion'] = 'Invalid file type. Allowed: jpg, jpeg, png, pdf, docx, xlsx';
    }

    if ($proof_file['size'] > 5 * 1024 * 1024) { // 5MB limit
      $errors['proof_of_completion'] = 'File size exceeds 5MB limit';
    }

    if (empty($errors['proof_of_completion'])) {
      $upload_dir = '../uploads/task_proofs/';
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }

      $proof_filename = 'task_' . $task_id . '_' . time() . '.' . $file_ext;
      if (!move_uploaded_file($proof_file['tmp_name'], $upload_dir . $proof_filename)) {
        $errors['proof_of_completion'] = 'Failed to upload file';
      }
    }
  }

  // If no errors, update the database
  if (empty($errors)) {
    $status = mysqli_real_escape_string($conn, $status);
    $proof_filename = $proof_filename ? mysqli_real_escape_string($conn, $proof_filename) : null;

    // Build update query
    if ($proof_filename) {
      $sql = "UPDATE tasks SET status = '$status', proof_of_completion = '$proof_filename' WHERE task_id = $task_id";
    } else {
      $sql = "UPDATE tasks SET status = '$status' WHERE task_id = $task_id";
    }

    if (mysqli_query($conn, $sql)) {
      // Get task details for notification
      $task_query = "SELECT task_title FROM tasks WHERE task_id = $task_id";
      $task_result = mysqli_query($conn, $task_query);
      $task = mysqli_fetch_assoc($task_result);

      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'UPDATE',
        'TASKS',
        $task_id,
        $task['task_title'],
        "Updated task status to '$status'"
      );

      echo "<script>
                alert('Task status updated successfully!');
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

// Get statistics
$stats = ['total_tasks' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

// Modify stats query based on user role
if ($is_admin) {
  $stats_condition = "is_archived = 0";
} else {
  $stats_condition = "is_archived = 0 AND assigned_to_id = $employee_id";
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE $stats_condition");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_tasks'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Pending' AND $stats_condition");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['pending'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'In Progress' AND $stats_condition");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['in_progress'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status = 'Completed' AND $stats_condition");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['completed'] = $row['count'];
}

// Get all tasks based on role with filters
$tasks = [];

// Build WHERE conditions
$where_conditions = ["is_archived = 0"];

if (!$is_admin) {
  // Employees only see tasks assigned to them
  $where_conditions[] = "assigned_to_id = $employee_id";
}

// Apply project filter
if ($filter_project > 0) {
  $where_conditions[] = "project_id = $filter_project";
}

// Apply employee filter (admin only)
if ($is_admin && $filter_employee > 0) {
  $where_conditions[] = "assigned_to_id = $filter_employee";
}

// Build final query
$where_clause = implode(" AND ", $where_conditions);
$sql = "SELECT * FROM tasks WHERE $where_clause ORDER BY due_date DESC";
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

    .current-proof-container {
      margin-top: 15px;
      padding: 10px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border: 1px solid #dee2e6;
    }

    .current-proof-img {
      width: 100%;
      border-radius: 5px;
      margin-top: 10px;
    }

    .tasks-actions {
      width: 100%;
    }

    @media (min-width: 768px) {
      .tasks-actions {
        max-width: 150px;
        flex-shrink: 0;
      }
    }
  </style>
</head>

<body>
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Tasks</h1>
        <p class="admin-top-desc">Manage and assign tasks to employees</p>
      </div>
      <div class="d-flex gap-2 flex-column flex-md-row">
        <?php if ($is_admin): ?>
          <button class="btn green-bg text-white add-item-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addTasksModal">
            <i class="fa-solid fa-plus me-1"></i> Add <span class="d-none d-md-block ms-1">Task</span>
          </button>
        <?php endif; ?>
        <a href="admin-archive-tasks.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Tasks</span>
        </a>
        <?php if ($is_admin): ?>
          <button class="btn btn-primary text-white d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#generateReportModal">
            <i class="fa-solid fa-file-lines me-1"></i> Generate <span class="d-none d-md-block ms-1">Report</span>
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- STATISTICS -->
    <div class="row g-3 mb-4">
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text"><?= $is_admin ? 'Total Tasks' : 'My Tasks' ?></p>
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
          <div class="tasks-top p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 class="fs-24 mobile-fs-22 mb-0"><?= $is_admin ? 'All Tasks' : 'My Assigned Tasks' ?></h2>
            <div class="d-flex gap-2 flex-wrap">

              <select name="status_filter" id="statusFilter" class="form-select" style="width: auto; min-width: 150px;">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>

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

            </div>
          </div>

          <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
              <i class="fa-solid fa-tasks fs-48 text-muted mb-3"></i>
              <h4 class="text-muted">No Tasks Available</h4>
              <p class="text-muted"><?= $is_admin ? 'Add a new task to get started.' : 'You have no tasks assigned yet.' ?></p>
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
                        default        => "badge-pill bg-secondary text-white"
                      };
                      ?>
                      <span class="<?= $taskStatusClass ?>"><?= $taskStatus ?></span>
                    </div>

                    <p class="light-text mb-3">
                      <?= str_replace(['&lt;br&gt;', '&lt;br/&gt;'], '<br>', htmlspecialchars($task['task_desc'])) ?>
                    </p>

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

                  <div class="tasks-actions d-flex flex-column gap-2 flex-fill">
                    <?php if ($is_admin): ?>

                      <button class="btn btn-light border edit-task w-100"
                        data-id="<?= $task['task_id'] ?>"
                        data-title="<?= htmlspecialchars($task['task_title']) ?>"
                        data-desc="<?= htmlspecialchars($task['task_desc']) ?>"
                        data-priority="<?= htmlspecialchars($task['priority']) ?>"
                        data-status="<?= htmlspecialchars($task['status']) ?>"
                        data-assigned="<?= $task['assigned_to_id'] ?>"
                        data-project="<?= htmlspecialchars($task['project_name']) ?>"
                        data-project-id="<?= $task['project_id'] ?? '' ?>"
                        data-due="<?= $task['due_date'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#editTaskModal">
                        Edit
                      </button>

                      <?php if (!empty($task['proof_of_completion'])): ?>
                        <button class="btn btn-green border view-proof w-100"
                          data-id="<?= $task['task_id'] ?>"
                          data-filename="<?= htmlspecialchars($task['proof_of_completion']) ?>"
                          data-title="<?= htmlspecialchars($task['task_title']) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewProofModal">
                          <i class="fa-solid fa-file-check me-1"></i> View Proof
                        </button>
                      <?php endif; ?>
                      <button class="btn btn-danger border archive-task"
                        data-id="<?= $task['task_id'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#archiveTaskModal">
                        Archive
                      </button>
                    <?php else: ?>
                      <button class="btn btn-green border update-task-status w-100"
                        data-id="<?= $task['task_id'] ?>"
                        data-title="<?= htmlspecialchars($task['task_title']) ?>"
                        data-status="<?= htmlspecialchars($task['status']) ?>"
                        data-proof="<?= htmlspecialchars($task['proof_of_completion'] ?? '') ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#updateTaskStatusModal">
                        Update Status
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
                <select name="project_id" class="form-select">
                  <option value="">Select Project</option>
                  <?php foreach ($projects as $proj): ?>
                    <option value="<?= $proj['project_id'] ?>" <?= (isset($_POST['project_id']) && $_POST['project_id'] == $proj['project_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($proj['project_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['project_id']) ? 'block' : 'none' ?>">
                  <?= $errors['project_id'] ?? 'This field is required' ?>
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

  <!-- EDIT TASK MODAL (Admin only) -->
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
              <textarea name="task_desc" id="editTaskDesc" class="form-control" rows="4" placeholder="Complete Installation at Building A"></textarea>
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
                <select name="project_id" id="editProjectId" class="form-select">
                  <?php foreach ($projects as $proj): ?>
                    <option value="<?= $proj['project_id'] ?>">
                      <?= htmlspecialchars($proj['project_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
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

  <!-- UPDATE TASK STATUS MODAL (Employee) -->
  <div class="modal fade" id="updateTaskStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Task Status</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
          <input type="hidden" name="task_id" id="updateTaskId">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Task Title </label>
              <input type="text" id="updateTaskTitle" class="form-control" disabled>
            </div>

            <div class="mb-3">
              <label class="form-label">Current Status</label>
              <input type="text" id="updateTaskCurrentStatus" class="form-control" disabled>
            </div>

            <div class="mb-3">
              <label class="form-label">Update Status</label>
              <select name="status" class="form-select">
                <option value="">Select New Status</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['status']) ? 'block' : 'none' ?>">
                <?= $errors['status'] ?? 'Status is required' ?>
              </p>
            </div>

            <div class="mb-3">
              <label class="form-label">Proof of Completion</label>
              <input type="file" name="proof_of_completion" id="proofFileInput" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx">
              <small class="text-muted">Allowed: jpg, jpeg, png, pdf, docx, xlsx (Max 5MB)</small>
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['proof_of_completion']) ? 'block' : 'none' ?>">
                <?= $errors['proof_of_completion'] ?? 'Invalid file' ?>
              </p>

              <!-- Show current proof if exists -->
              <div id="currentProofContainer" class="current-proof-container" style="display: none;">
                <p class="mb-2 fw-bold"><i class="fa-solid fa-file-check me-2"></i>Current Proof:</p>
                <div id="currentProofDisplay" class="w-100"></div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_task_status" class="btn btn-green text-white">Update Status</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VIEW PROOF MODAL (Admin & Employee) -->
  <div class="modal fade" id="viewProofModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Proof of Completion - <span id="proofTaskTitle"></span></h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div id="proofContent" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <a id="proofDownloadLink" href="#" class="btn btn-green" download>
            <i class="fa-solid fa-download me-1"></i> Download
          </a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
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

  <!-- GENERATE REPORT MODAL -->
  <div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Generate Task Report</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form id="reportForm">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Date From</label>
              <input type="date" name="date_from" class="form-control" value="<?= date('Y-m-01') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Date To</label>
              <input type="date" name="date_to" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Status Filter</label>
              <select name="status_filter" class="form-select">
                <option value="all" selected>All Status</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Employee Filter</label>
              <select name="employee_filter" class="form-select">
                <option value="all">All Employees</option>
                <?php foreach ($employees as $emp): ?>
                  <option value="<?= $emp['employee_id'] ?>">
                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-green" onclick="generateTaskReportPDF()">Generate Report</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
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

    // Status filter functionality
    $('#statusFilter').on('change', function() {
      const statusValue = $(this).val();

      if (statusValue === 'all') {
        $('.tasks-con').show();
      } else {
        $('.tasks-con').each(function() {
          // Get the status badge element
          const $statusBadge = $(this).find('.taskstatus-pending, .taskstatus-inprogress, .taskstatus-completed, .bg-secondary');
          const taskStatus = $statusBadge.text().trim();

          if (taskStatus === statusValue) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }
    });

    // Function to generate Task Report PDF with filters
    function generateTaskReportPDF() {
      const dateFrom = document.querySelector('#generateReportModal input[name="date_from"]').value;
      const dateTo = document.querySelector('#generateReportModal input[name="date_to"]').value;
      const statusFilter = document.querySelector('#generateReportModal select[name="status_filter"]').value;
      const employeeFilter = document.querySelector('#generateReportModal select[name="employee_filter"]').value;

      if (!dateFrom || !dateTo) {
        alert('Please select both date from and date to.');
        return;
      }

      const url = `admin-task-completion-report.php?date_from=${dateFrom}&date_to=${dateTo}&status_filter=${statusFilter}&employee_filter=${employeeFilter}&auto=1`;
      const reportWindow = window.open(url, '_blank', 'width=1200,height=800');

      reportWindow.addEventListener('load', function() {
        setTimeout(async function() {
          try {
            const {
              jsPDF
            } = reportWindow.jspdf;
            const content = reportWindow.document.getElementById('report-content');

            const canvas = await reportWindow.html2canvas(content, {
              scale: 2,
              useCORS: true,
              logging: false,
              backgroundColor: '#ffffff'
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF({
              orientation: 'portrait',
              unit: 'mm',
              format: 'a4'
            });

            const imgWidth = 210;
            const pageHeight = 297;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;

            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft > 0) {
              position = heightLeft - imgHeight;
              pdf.addPage();
              pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
              heightLeft -= pageHeight;
            }

            const filename = 'Task_Report_' + dateFrom + '_to_' + dateTo + '.pdf';
            pdf.save(filename);

            reportWindow.close();
          } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
            reportWindow.close();
          }
        }, 1500);
      });

      // Close the modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportModal'));
      if (modal) {
        modal.hide();
      }
    }

    $(document).ready(function() {
      // Edit Task Modal - Populate data (Admin)
      $('.edit-task').on('click', function() {
        const taskId = $(this).data('id');
        const title = $(this).data('title');
        const desc = $(this).data('desc');
        const priority = $(this).data('priority');
        const status = $(this).data('status');
        const assignedId = $(this).data('assigned');
        const projectId = $(this).data('project-id');
        const dueDate = $(this).data('due');

        // Set values
        $('#editTaskId').val(taskId);
        $('#editTaskTitle').val(title);
        $('#editTaskDesc').val(desc.replace(/<br\s*\/?>/gi, '\n'));
        $('#editPriority').val(priority);
        $('#editStatus').val(status);
        $('#editAssignedTo').val(assignedId);

        if (projectId) {
          $('#editProjectId').val(projectId);
        }

        $('#editDueDate').val(dueDate);
      });

      // View Proof Modal - Display proof file
      $('.view-proof').on('click', function() {
        const filename = $(this).data('filename');
        const taskTitle = $(this).data('title');
        const filepath = '../uploads/task_proofs/' + filename;
        const fileExt = filename.split('.').pop().toLowerCase();

        $('#proofTaskTitle').text(taskTitle);
        $('#proofDownloadLink').attr('href', filepath).attr('download', filename);

        let contentHTML = '';

        if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
          contentHTML = '<img src="' + filepath + '" class="img-fluid" style="max-height: 500px;" />';
        } else if (fileExt === 'pdf') {
          contentHTML = '<iframe src="' + filepath + '" style="width: 100%; height: 500px; border: none;"></iframe>';
        } else if (['docx', 'xlsx'].includes(fileExt)) {
          contentHTML = '<div class="alert alert-info"><i class="fa-solid fa-file me-2"></i>File: <strong>' + filename + '</strong><br><p class="mb-0 mt-2">Click Download to view the file</p></div>';
        } else {
          contentHTML = '<div class="alert alert-warning"><i class="fa-solid fa-file me-2"></i>File: <strong>' + filename + '</strong></div>';
        }

        $('#proofContent').html(contentHTML);
      });

      // Update Task Status Modal - Populate data (Employee)
      $('.update-task-status').on('click', function() {
        const taskId = $(this).data('id');
        const taskTitle = $(this).data('title');
        const currentStatus = $(this).data('status');
        const currentProof = $(this).data('proof');

        $('#updateTaskId').val(taskId);
        $('#updateTaskTitle').val(taskTitle);
        $('#updateTaskCurrentStatus').val(currentStatus);
        $('#proofFileInput').val('');

        if (currentProof && currentProof !== '') {
          const filepath = '../uploads/task_proofs/' + currentProof;
          const fileExt = currentProof.split('.').pop().toLowerCase();
          let proofHTML = '';

          if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
            proofHTML = '<img src="' + filepath + '" class="current-proof-img" />';
          } else {
            proofHTML = '<p class="mb-0"><i class="fa-solid fa-file me-2"></i>' + currentProof + '</p>';
          }

          $('#currentProofDisplay').html(proofHTML);
          $('#currentProofContainer').show();
        } else {
          $('#currentProofContainer').hide();
        }
      });

      // Archive Task Modal - Set ID
      $('.archive-task').on('click', function() {
        let taskId = $(this).data('id');
        $('#archiveTaskId').val(taskId);
      });

      <?php if (!empty($errors) && isset($_POST['add_task'])): ?>
        $('#addTasksModal').modal('show');
      <?php endif; ?>

      <?php if (!empty($errors) && isset($_POST['update_task_status'])): ?>
        $('#updateTaskStatusModal').modal('show');
      <?php endif; ?>
    });
  </script>
</body>

</html>


</body>

</html>