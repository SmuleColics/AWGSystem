<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Admin Dashboard</title>
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
  <main id="main" class="container-xxl text-dark px-4">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Tasks</h1>
        <p class="admin-top-desc">Manage and assign tasks to employees</p>
      </div>
      <div>
        <button class="btn green-bg text-white add-item-btn" data-bs-toggle="modal" data-bs-target="#addTasksModal">
          <i class="fa-solid fa-plus me-1"></i> Add Tasks
        </button>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-12">
        <div class="tasks-container rounded-3 bg-white">
          <div class="tasks-top p-4">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Tasks</h2>
          </div>
          <div class="px-4 pb-4">
            <div class="tasks-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="tasks-details w-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <h3 class="fs-18 mb-0">Install CCTV cameras</h3>
                  <?php
                  $priority = "High"; // example, dynamic later

                  $priorityClass = match ($priority) {
                    "High"   => "badge-pill priority-high",
                    "Medium" => "badge-pill priority-medium",
                    "Low"    => "badge-pill priority-low",
                    default  => "badge-pill"
                  };
                  ?>

                  <span class="<?= $priorityClass ?>"><?= $priority ?></span>

                  <?php
                  $taskStatus = "In Progress"; // example, dynamic later

                  $taskStatusClass = match ($taskStatus) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "In Progress"  => "badge-pill taskstatus-inprogress",
                    "Completed"    => "badge-pill taskstatus-completed",
                    default        => "badge-pill"
                  };
                  ?>

                  <span class="<?= $taskStatusClass ?>"><?= $taskStatus ?></span>

                </div>
                <p class="light-text">Complete Installation at Building A</p>
                <div class="d-flex justify-content-between align-items-center">
                  <p class="fs-14 mb-0">
                    <span class="light-text">Assigned to: </span>
                    John Smith
                  </p>
                  <p class="fs-14 mb-0">
                    <span class="light-text">Due date: </span>
                    11/15/2024
                  </p>
                  <p class="fs-14 mb-0">
                    <span class="light-text">Project: </span>
                    Security System Installation
                  </p>
                </div>
              </div>
              <div class="tasks-actions d-flex flex-column gap-2">
                <div class="btn btn-light border">Edit</div>
                <div class="btn btn-danger border">Delete</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>


  </main>
  <!-- END OF MAIN -->
  <!-- ADD TASKS MODAL -->
  <div class="modal fade" id="addTasksModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Add New Task</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="add-task.php" method="POST">
          <div class="modal-body">

            <!-- Task Title -->
            <div class="mb-3">
              <label class="form-label">Task Title</label>
              <input type="text" name="task_title" class="form-control" placeholder="Install CCTV Cameras" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
              <label class="form-label">Task Description</label>
              <textarea name="task_desc" class="form-control" rows="3" placeholder="Complete Installation at Building A" required></textarea>
            </div>

            <div class="row">
              <!-- Priority -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select" required>
                  <option value="High">High</option>
                  <option value="Medium">Medium</option>
                  <option value="Low">Low</option>
                </select>
              </div>

              <!-- Status -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>

            <div class="row">
              <!-- Assigned To -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Assigned To</label>
                <select name="employee_id" class="form-select" required>
                  <option selected disabled>Select employee</option>

                  <!-- Dynamically populate: -->
                  <?php
                  // Example PHP query
                  // $result = mysqli_query($conn, "SELECT * FROM employees");
                  // while($row = mysqli_fetch_assoc($result)){
                  // echo "<option value='{$row['id']}'>{$row['name']}</option>";
                  // }
                  ?>
                </select>
              </div>

              <!-- Project -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select" required>
                  <option selected disabled>Select project</option>

                  <!-- Dynamically populate: -->
                  <?php
                  // $projects = mysqli_query($conn, "SELECT * FROM projects");
                  // while($p = mysqli_fetch_assoc($projects)){
                  // echo "<option value='{$p['id']}'>{$p['project_name']}</option>";
                  // }
                  ?>
                </select>
              </div>
            </div>

            <!-- Due Date -->
            <div class="mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" class="form-control" required>
            </div>

          </div>

          <div class="modal-footer">
            <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green text-white">Save Task</button>
          </div>

        </form>

      </div>
    </div>
  </div>


</body>





</html>