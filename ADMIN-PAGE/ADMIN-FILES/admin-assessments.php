<?php
include 'admin-header.php';

// Fetch all assessments with user information and assigned employees
$sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, 
        u.house_no, u.brgy, u.city, u.province, u.zip_code,
        e1.first_name as employee1_first_name, e1.last_name as employee1_last_name,
        e2.first_name as employee2_first_name, e2.last_name as employee2_last_name
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        LEFT JOIN employees e1 ON a.assigned_to_id = e1.employee_id
        LEFT JOIN employees e2 ON a.assigned_to_id_2 = e2.employee_id
        WHERE a.is_archived = 0
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
$assessments = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $assessments[] = $row;
  }
}

// Fetch active employees excluding Admin and Admin/Secretary
$employees_sql = "SELECT employee_id, first_name, last_name, position 
                  FROM employees 
                  WHERE is_archived = 0 
                  AND position NOT IN ('Admin', 'Admin/Secretary')
                  ORDER BY first_name ASC";
$employees_result = mysqli_query($conn, $employees_sql);
$employees = [];
if ($employees_result) {
  while ($row = mysqli_fetch_assoc($employees_result)) {
    $employees[] = $row;
  }
}

// HANDLE ACCEPT AND ASSIGN ASSESSMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_and_assign'])) {
  $assessment_id = intval($_POST['assessment_id']);
  $assigned_to_id = intval($_POST['assigned_employee_1']);
  $assigned_to_id_2 = intval($_POST['assigned_employee_2']);

  if ($assigned_to_id > 0 && $assigned_to_id_2 > 0) {
    // Check if same employee is selected twice
    if ($assigned_to_id === $assigned_to_id_2) {
      echo "<script>alert('Please select two different employees.');</script>";
    } else {
      // Update assessment status and assign both employees
      $accept_sql = "UPDATE assessments 
            SET status = 'Accepted', 
                assigned_to_id = $assigned_to_id,
                assigned_to_id_2 = $assigned_to_id_2
            WHERE assessment_id = $assessment_id";

      if (mysqli_query($conn, $accept_sql)) {
        // Get assessment and employee details - INCLUDE USER ADDRESS FIELDS
        $assessment_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, 
              u.first_name, u.last_name, u.house_no, u.brgy, u.city, u.province, u.zip_code
              FROM assessments a 
              LEFT JOIN users u ON a.user_id = u.user_id 
              WHERE assessment_id = $assessment_id"));

        $employee1_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name, last_name 
              FROM employees 
              WHERE employee_id = $assigned_to_id"));
        $employee2_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name, last_name 
              FROM employees 
              WHERE employee_id = $assigned_to_id_2"));

        $user_name = $assessment_info['first_name'] . ' ' . $assessment_info['last_name'];
        $employee1_name = $employee1_info['first_name'] . ' ' . $employee1_info['last_name'];
        $employee2_name = $employee2_info['first_name'] . ' ' . $employee2_info['last_name'];
        $service_type = $assessment_info['service_type'];
        $preferred_date = $assessment_info['preferred_date'];
        $user_id = $assessment_info['user_id'];

        // CREATE TASKS FOR BOTH EMPLOYEES
        $task_title = "Assessment: " . $service_type . " - " . $user_name;

        // BUILD FULL ADDRESS FROM USER TABLE
        $address_parts = array_filter([
          $assessment_info['house_no'],
          $assessment_info['brgy'],
          $assessment_info['city'],
          $assessment_info['province'],
          $assessment_info['zip_code']
        ]);
        $location = !empty($address_parts) ? implode(', ', $address_parts) : 'Address not provided';

        $task_desc  = "Conduct on-site assessment for " . $user_name . "<br>";
        $task_desc .= "Location: " . $location . "<br>";
        $task_desc .= "Preferred time: " . $assessment_info['preferred_time'] . "<br>";

        if (!empty($assessment_info['notes'])) {
          $task_desc .= "Notes: " . $assessment_info['notes'];
        }

        // Task for Employee 1
        $task1_sql = "INSERT INTO tasks (
                        task_title, 
                        task_desc, 
                        priority, 
                        status, 
                        assigned_to_id, 
                        assigned_to, 
                        project_name, 
                        due_date, 
                        is_archived
                      ) VALUES (
                        '" . mysqli_real_escape_string($conn, $task_title) . "',
                        '" . mysqli_real_escape_string($conn, $task_desc) . "',
                        'High',
                        'Assigned',
                        $assigned_to_id,
                        '" . mysqli_real_escape_string($conn, $employee1_name) . "',
                        'Assessment - $service_type',
                        '$preferred_date',
                        0
                      )";

        // Task for Employee 2
        $task2_sql = "INSERT INTO tasks (
                        task_title, 
                        task_desc, 
                        priority, 
                        status, 
                        assigned_to_id, 
                        assigned_to, 
                        project_name, 
                        due_date, 
                        is_archived
                      ) VALUES (
                        '" . mysqli_real_escape_string($conn, $task_title) . "',
                        '" . mysqli_real_escape_string($conn, $task_desc) . "',
                        'High',
                        'Assigned',
                        $assigned_to_id_2,
                        '" . mysqli_real_escape_string($conn, $employee2_name) . "',
                        'Assessment - $service_type',
                        '$preferred_date',
                        0
                      )";

        mysqli_query($conn, $task1_sql);
        mysqli_query($conn, $task2_sql);

        // Log activity
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'ACCEPT',
          'ASSESSMENTS',
          $assessment_id,
          $service_type,
          "Accepted assessment from $user_name and assigned to $employee1_name and $employee2_name | Service: $service_type"
        );

        // CREATE NOTIFICATIONS FOR BOTH ASSIGNED EMPLOYEES
        create_notification(
          $conn,
          $assigned_to_id,
          $employee_id,
          $employee_name,
          'ASSESSMENT_ASSIGNED',
          'New Assessment Assigned',
          "You have been assigned to an assessment: $service_type for $user_name",
          'admin-assessments.php',
          $assessment_id
        );

        create_notification(
          $conn,
          $assigned_to_id_2,
          $employee_id,
          $employee_name,
          'ASSESSMENT_ASSIGNED',
          'New Assessment Assigned',
          "You have been assigned to an assessment: $service_type for $user_name",
          'admin-assessments.php',
          $assessment_id
        );

        // CREATE NOTIFICATION FOR USER (CLIENT SIDE)
        $user_notif_title = 'Assessment Request Accepted';
        $user_notif_message = 'Hello ' . $user_name . ', your ' . $service_type . ' assessment request has been accepted. Our team will conduct the assessment soon.';
        $user_notif_link = 'user-assessments.php';

        $user_notif_sql = "INSERT INTO notifications (recipient_id, sender_id, sender_name, type, title, message, link, related_id, is_read) 
                          VALUES ($user_id, $employee_id, 
                                '" . mysqli_real_escape_string($conn, $employee_name) . "',
                                'ASSESSMENT_ACCEPTED', 
                                '" . mysqli_real_escape_string($conn, $user_notif_title) . "',
                                '" . mysqli_real_escape_string($conn, $user_notif_message) . "',
                                '" . mysqli_real_escape_string($conn, $user_notif_link) . "',
                                $assessment_id,
                                0)";
        mysqli_query($conn, $user_notif_sql);

        // CREATE NOTIFICATION FOR ADMIN (ADMIN SIDE) - Only if current user is not admin
        if (!$is_admin) {
          // Get all admin employee IDs
          $admin_query = "SELECT employee_id FROM employees WHERE position IN ('Admin', 'Admin/Secretary') AND is_archived = 0 AND employee_id != $employee_id";
          $admin_result = mysqli_query($conn, $admin_query);

          while ($admin_row = mysqli_fetch_assoc($admin_result)) {
            $admin_id = $admin_row['employee_id'];

            $admin_notif_title = 'Assessment Accepted';
            $admin_notif_message = $user_name . '\'s ' . $service_type . ' assessment request has been accepted and assigned to ' . $employee1_name . ' and ' . $employee2_name . '.';
            $admin_notif_link = 'admin-assessments.php';

            $admin_notif_sql = "INSERT INTO notifications (recipient_id, sender_id, sender_name, type, title, message, link, related_id, is_read) 
                              VALUES ($admin_id, $employee_id,
                                    '" . mysqli_real_escape_string($conn, $employee_name) . "',
                                    'ASSESSMENT_ACCEPTED_ADMIN', 
                                    '" . mysqli_real_escape_string($conn, $admin_notif_title) . "',
                                    '" . mysqli_real_escape_string($conn, $admin_notif_message) . "',
                                    '" . mysqli_real_escape_string($conn, $admin_notif_link) . "',
                                    $assessment_id,
                                    0)";
            mysqli_query($conn, $admin_notif_sql);
          }
        }

        echo "<script>
          alert('Assessment accepted and assigned to both employees successfully! Tasks have been created.');
          window.location.href = 'admin-assessments.php';
        </script>";
        exit;
      } else {
        echo "<script>alert('Error accepting assessment.');</script>";
      }
    }
  } else {
    echo "<script>alert('Please select both employees to assign.');</script>";
  }
}

// HANDLE MARK ASSESSMENT AS COMPLETED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_assessment'])) {
  $assessment_id = intval($_POST['assessment_id']);

  $complete_sql = "UPDATE assessments 
                   SET assessment_completed = 1,
                       assessment_completed_at = NOW()
                   WHERE assessment_id = $assessment_id";

  if (mysqli_query($conn, $complete_sql)) {
    // Get assessment details
    $assessment_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, u.first_name, u.last_name 
                                                                 FROM assessments a 
                                                                 LEFT JOIN users u ON a.user_id = u.user_id 
                                                                 WHERE assessment_id = $assessment_id"));

    $user_name = $assessment_info['first_name'] . ' ' . $assessment_info['last_name'];
    $service_type = $assessment_info['service_type'];

    // Log activity
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'UPDATE',
      'ASSESSMENTS',
      $assessment_id,
      $service_type,
      "Marked assessment as completed for $user_name | Service: $service_type"
    );

    echo "<script>
      alert('Assessment marked as completed!');
      window.location.href = 'admin-assessments.php';
    </script>";
    exit;
  } else {
    echo "<script>alert('Error completing assessment.');</script>";
  }
}

// HANDLE ARCHIVE ASSESSMENT 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_assessment'])) {
  $assessment_id = intval($_POST['assessment_id']);

  // Get assessment details before archiving
  $assessment_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, u.first_name, u.last_name 
                                                               FROM assessments a 
                                                               LEFT JOIN users u ON a.user_id = u.user_id 
                                                               WHERE assessment_id = $assessment_id"));

  $user_full_name = $assessment_info['first_name'] . ' ' . $assessment_info['last_name'];
  $service_type = $assessment_info['service_type'];

  $archive_sql = "UPDATE assessments 
                  SET is_archived = 1
                  WHERE assessment_id = $assessment_id";

  if (mysqli_query($conn, $archive_sql)) {
    // Activity log
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'ARCHIVE',
      'ASSESSMENTS',
      $assessment_id,
      $service_type,
      "Archived assessment for $user_full_name | Service: $service_type"
    );

    echo "<script>
      alert('Assessment archived successfully.');
      window.location.href = 'admin-assessments.php';
    </script>";
    exit;
  } else {
    echo "<script>alert('Error archiving assessment.');</script>";
  }
}

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
    .sidebar-content-item:nth-child(4) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(4) .sidebar-anchor,
    .sidebar-content-item:nth-child(4) .sidebar-anchor span {
      color: #16A249 !important;
    }

    .assessment-status-badge {
      font-size: 12px;
      padding: 4px 12px;
      border-radius: 12px;
      font-weight: 500;
    }

    .assessment-completed-badge {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .assessment-pending-badge {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .assigned-team-badge {
      display: inline-block;
      background-color: #e7f3ff;
      color: #004085;
      padding: 3px 8px;
      border-radius: 8px;
      font-size: 12px;
      margin-right: 4px;
      margin-bottom: 4px;
    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center gap-4">

      <div>
        <h1 class="fs-36 mobile-fs-32">Assessments Request</h1>
        <p class="admin-top-desc">Manage customer assessment requests and create quotations</p>
      </div>
      <div>
        <a href="admin-archive-assessment.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Assessments</span>
        </a>
      </div>

    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4 d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Assessments Requests (<?= count($assessments) ?>)</h2>
            <div class="d-flex gap-2">
              <div>
                <select id="serviceFilter" class="form-select">
                  <option value="all">All Services</option>
                  <option value="cctv">CCTV</option>
                  <option value="solar">Solar</option>
                  <option value="renovation">Renovation</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <select id="assessmentFilter" class="form-select">
                  <option value="all">Show All</option>
                  <option value="Pending">Pending</option>
                  <option value="Accepted">Accepted</option>
                  <option value="Rejected">Rejected</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>
          </div>

          <div class="px-4 pb-4 d-flex flex-column gap-4">

            <?php if (empty($assessments)): ?>
              <!-- Empty State -->
              <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No assessment requests found</p>
              </div>
            <?php else: ?>
              <!-- Empty State (for filtering) -->
              <div id="assessmentEmptyState" class="text-center py-5 d-none">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No assessments found for this status</p>
              </div>

              <!-- Assessment Cards Container -->
              <div id="assessmentCardsContainer">
                <?php foreach ($assessments as $assessment): ?>
                  <?php
                  $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
                  $location = trim($assessment['city'] . ', ' . $assessment['province']);
                  $formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));

                  $assigned_employees = [];
                  if ($assessment['assigned_to_id']) {
                    $assigned_employees[] = $assessment['employee1_first_name'] . ' ' . $assessment['employee1_last_name'];
                  }
                  if ($assessment['assigned_to_id_2']) {
                    $assigned_employees[] = $assessment['employee2_first_name'] . ' ' . $assessment['employee2_last_name'];
                  }

                  // Status badge classes
                  $statusClass = match ($assessment['status']) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "Accepted"     => "badge-pill taskstatus-inprogress",
                    "Completed"    => "badge-pill taskstatus-completed",
                    "Rejected"     => "badge-pill priority-high",
                    default        => "badge-pill"
                  };
                  ?>

                  <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4 mb-3"
                    data-status="<?= htmlspecialchars($assessment['status']) ?>"
                    data-service="<?= strtolower(str_replace(' ', '_', $assessment['service_type'])) ?>">

                    <div class="w-100">
                      <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <h3 class="fs-18 mb-0">
                          <?= htmlspecialchars($user_full_name) ?>
                          <span class="fs-14 light-text">(<?= htmlspecialchars($assessment['email']) ?>)</span>
                        </h3>
                        <span class="<?= $statusClass ?>"><?= htmlspecialchars($assessment['status']) ?></span>

                        <?php if ($assessment['status'] === 'Accepted'): ?>
                          <?php if ($assessment['assessment_completed']): ?>
                            <span class="assessment-status-badge assessment-completed-badge">
                              <i class="fas fa-check-circle me-1"></i>Assessment Done
                            </span>
                          <?php else: ?>
                            <span class="assessment-status-badge assessment-pending-badge">
                              <i class="fas fa-clock me-1"></i>Assessment Pending
                            </span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>

                      <div class="row mt-1">
                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="light-text">Service: </span>
                            <?= htmlspecialchars($assessment['service_type']) ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Time: </span>
                            <?= htmlspecialchars($assessment['preferred_time']) ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Location: </span>
                            <?= htmlspecialchars($location) ?>
                          </p>
                          <?php if (!empty($assigned_employees)): ?>
                            <p class="fs-14 mb-2">
                              <span class="light-text">Assigned Team: </span><br>
                              <?php foreach ($assigned_employees as $emp_name): ?>
                                <span class="assigned-team-badge">
                                  <i class="fas fa-user me-1"></i><?= htmlspecialchars($emp_name) ?>
                                </span>
                              <?php endforeach; ?>
                            </p>
                          <?php endif; ?>
                          <?php if (!empty($assessment['notes'])): ?>
                            <p class="fs-14 mb-0">
                              <span class="light-text">Notes: </span><br />
                              <?= htmlspecialchars($assessment['notes']) ?>
                            </p>
                          <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="light-text">Date: </span>
                            <?= $formatted_date ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Phone: </span>
                            <?= htmlspecialchars($assessment['phone']) ?>
                          </p>
                          <?php if (!empty($assessment['estimated_budget'])): ?>
                            <p class="fs-14 mb-2">
                              <span class="light-text">Estimated Budget: </span>
                              ₱<?= number_format($assessment['estimated_budget'], 2) ?>
                            </p>
                          <?php endif; ?>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Requested: </span>
                            <?= date('M d, Y h:i A', strtotime($assessment['created_at'])) ?>
                          </p>
                          <?php if ($assessment['assessment_completed'] && $assessment['assessment_completed_at']): ?>
                            <p class="fs-14 mb-2">
                              <span class="light-text">Completed: </span>
                              <?= date('M d, Y h:i A', strtotime($assessment['assessment_completed_at'])) ?>
                            </p>
                          <?php endif; ?>
                        </div>
                      </div>

                    </div>

                    <div class="assessment-actions d-flex flex-column gap-2">
                      <?php
                      // Check if quotation exists and its status
                      $quotation_check_sql = "SELECT status FROM quotations WHERE assessment_id = {$assessment['assessment_id']}";
                      $quotation_check_result = mysqli_query($conn, $quotation_check_sql);
                      $quotation_check = mysqli_fetch_assoc($quotation_check_result);
                      $quotation_status = $quotation_check ? $quotation_check['status'] : null;
                      ?>

                      <?php if ($is_admin): ?>
                        <!-- ADMIN USER ACTIONS -->

                        <?php if ($assessment['status'] === 'Pending'): ?>
                          <!-- Pending Status Actions -->
                          <button
                            type="button"
                            class="btn btn-green flex w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#assignAssessmentModal"
                            onclick="setAssignAssessmentId(<?= $assessment['assessment_id'] ?>)">
                            <i class="fas fa-user-check me-1"></i>
                            Accept & Assign Team
                          </button>

                          <button
                            class="btn btn-danger border flex"
                            data-bs-toggle="modal"
                            data-bs-target="#rejectAssessmentModal"
                            onclick="setRejectAssessmentId(<?= $assessment['assessment_id'] ?>)">
                            <i class="fas fa-times-circle me-1"></i>
                            Reject
                          </button>

                        <?php elseif ($assessment['status'] === 'Accepted'): ?>
                          <!-- Accepted Status Actions for Admin -->

                          <?php if (!$assessment['assessment_completed']): ?>
                            <!-- Assessment not completed yet -->
                            <button
                              type="button"
                              class="btn btn-warning text-white flex w-100"
                              data-bs-toggle="modal"
                              data-bs-target="#completeAssessmentModal"
                              onclick="setCompleteAssessmentId(<?= $assessment['assessment_id'] ?>)">
                              <i class="fas fa-clipboard-check me-1"></i>
                              Mark Assessment Done
                            </button>
                          <?php else: ?>
                            <!-- Assessment completed, can create quotation -->
                            <?php if ($quotation_status === 'Sent'): ?>
                              <!-- Quotation already sent -->
                              <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                                class="btn btn-success border flex">
                                <i class="fas fa-file-invoice me-1"></i>
                                Manage Quotation
                              </a>
                            <?php else: ?>
                              <!-- Can create quotation -->
                              <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                                class="btn btn-green border flex">
                                <i class="fas fa-plus me-1"></i>
                                Create Quotation
                              </a>
                            <?php endif; ?>
                          <?php endif; ?>

                        <?php elseif ($assessment['status'] === 'Completed'): ?>
                          <!-- Completed Status Actions for Admin -->
                          <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                            class="btn btn-success border flex">
                            <i class="fas fa-file-invoice me-1"></i>
                            Manage Quotation
                          </a>
                        <?php endif; ?>

                        <!-- Archive button for all statuses (Admin only) -->
                        <button
                          type="button"
                          class="btn btn-light border flex w-100"
                          data-bs-toggle="modal"
                          data-bs-target="#archiveAssessmentModal"
                          onclick="setArchiveAssessmentId(<?= $assessment['assessment_id'] ?>)">
                          <i class="fa-solid fa-box-archive me-1"></i>
                          Archive
                        </button>

                      <?php else: ?>
                        <!-- NON-ADMIN USERS (Employees) -->

                        <?php if ($assessment['status'] === 'Pending'): ?>
                          <!-- Show nothing or a disabled button for pending -->
                          <button class="btn btn-secondary border flex w-100" disabled>
                            <i class="fas fa-clock me-1"></i>
                            Pending Approval
                          </button>

                        <?php elseif ($assessment['status'] === 'Accepted'): ?>
                          <?php if (!$assessment['assessment_completed']): ?>
                            <!-- Assessment not completed - show status -->
                            <button class="btn btn-secondary border flex w-100" disabled>
                              <i class="fas fa-hourglass-half me-1"></i>
                              Assessment In Progress
                            </button>
                          <?php elseif ($quotation_status === 'Sent'): ?>
                            <!-- Quotation is sent - employees can only view -->
                            <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>&view_only=1"
                              class="btn btn-green border flex">
                              <i class="fa-solid fa-eye me-1"></i>
                              View Quotation
                            </a>
                          <?php else: ?>
                            <!-- Assessment completed but no quotation yet -->
                            <button class="btn btn-secondary border flex w-100" disabled>
                              <i class="fas fa-hourglass-half me-1"></i>
                              Quotation Pending
                            </button>
                          <?php endif; ?>

                        <?php elseif ($assessment['status'] === 'Completed'): ?>
                          <!-- Completed - employees can view -->
                          <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>&view_only=1"
                            class="btn btn-green border flex">
                            <i class="fa-solid fa-eye me-1"></i>
                            View Quotation
                          </a>

                        <?php elseif ($assessment['status'] === 'Rejected'): ?>
                          <!-- Rejected status -->
                          <button class="btn btn-danger border flex w-100" disabled>
                            <i class="fas fa-times-circle me-1"></i>
                            Rejected
                          </button>
                        <?php endif; ?>

                      <?php endif; ?>
                    </div>
                  </div>

                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- Assign Assessment Modal -->
  <div class="modal fade" id="assignAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3">
        <div class="modal-header">
          <h5 class="modal-title">Accept & Assign Assessment Team</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <div class="modal-body">
            <p class="text-muted mb-3">Select two employees to handle this assessment</p>

            <div class="mb-3">
              <label class="form-label">First Employee *</label>
              <select class="form-select" name="assigned_employee_1" id="assignedEmployee1" required>
                <option value="">Select First Employee</option>
                <?php foreach ($employees as $emp): ?>
                  <option value="<?= $emp['employee_id'] ?>">
                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                    (<?= htmlspecialchars($emp['position']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Second Employee *</label>
              <select class="form-select" name="assigned_employee_2" id="assignedEmployee2" required>
                <option value="">Select Second Employee</option>
                <?php foreach ($employees as $emp): ?>
                  <option value="<?= $emp['employee_id'] ?>">
                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                    (<?= htmlspecialchars($emp['position']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <input type="hidden" name="assessment_id" id="assignAssessmentId" value="">
            <input type="hidden" name="accept_and_assign" value="1">
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="submit" class="btn btn-green">
              Accept & Assign Team
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Complete Assessment Modal -->
  <div class="modal fade" id="completeAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3">
        <div class="modal-header">
          <h5 class="modal-title">Mark Assessment as Completed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <div class="modal-body">
            <p class="text-center mb-3">
              <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
            </p>
            <h6 class="text-center">Confirm that the on-site assessment has been completed?</h6>
            <p class="text-muted text-center mb-0">
              Once marked as done, you can proceed to create a quotation.
            </p>

            <input type="hidden" name="assessment_id" id="completeAssessmentId" value="">
            <input type="hidden" name="complete_assessment" value="1">
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="submit" class="btn btn-success">
              Mark as Done
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reject Assessment Modal -->
  <div class="modal fade" id="rejectAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3">
        <div class="modal-header">
          <h5 class="modal-title">Reject Assessment Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" action="reject-assessment.php">
          <div class="modal-body">

            <!-- Reason Select -->
            <div class="mb-3">
              <label class="form-label">Reason for Rejection</label>
              <select class="form-select" name="reject_reason" id="rejectReason" required>
                <option value="">Select a reason</option>
                <option value="Location is too far away">Location is too far away</option>
                <option value="Budget is too low">Budget is too low</option>
                <option value="Schedule conflict">Schedule conflict</option>
                <option value="Service not available">Service not available</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <!-- Other Reason Input -->
            <div class="mb-3 d-none" id="otherReasonWrapper">
              <label class="form-label">Please specify</label>
              <textarea
                class="form-control"
                name="other_reason"
                rows="3"
                placeholder="Please specify the reason for rejection..."></textarea>
            </div>

            <input type="hidden" name="assessment_id" id="rejectAssessmentId" value="">
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="submit" class="btn btn-danger">
              Confirm Rejection
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Archive Assessment Modal -->
  <div class="modal fade" id="archiveAssessmentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="archiveAssessmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="archiveAssessmentLabel">Archive Assessment</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="assessment_id" id="archiveAssessmentId">
          <input type="hidden" name="archive_assessment" value="1">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this assessment?</h3>
            <p class="text-center text-muted">Archived assessments can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const rejectReason = document.getElementById('rejectReason');
    const otherReasonWrapper = document.getElementById('otherReasonWrapper');

    if (rejectReason) {
      rejectReason.addEventListener('change', function() {
        if (this.value === 'Others') {
          otherReasonWrapper.classList.remove('d-none');
        } else {
          otherReasonWrapper.classList.add('d-none');
        }
      });
    }

    function setAssignAssessmentId(assessmentId) {
      document.getElementById('assignAssessmentId').value = assessmentId;
    }

    function setCompleteAssessmentId(assessmentId) {
      document.getElementById('completeAssessmentId').value = assessmentId;
    }

    function setRejectAssessmentId(assessmentId) {
      document.getElementById('rejectAssessmentId').value = assessmentId;
    }

    function setArchiveAssessmentId(assessmentId) {
      document.getElementById('archiveAssessmentId').value = assessmentId;
    }

    document.addEventListener('DOMContentLoaded', function() {
      const statusFilter = document.getElementById('assessmentFilter');
      const serviceFilter = document.getElementById('serviceFilter');
      const assessmentCons = document.querySelectorAll('.assessment-con');
      const emptyStateMessage = document.getElementById('assessmentEmptyState');

      function applyFilters() {
        const selectedStatus = statusFilter.value;
        const selectedService = serviceFilter.value;
        let visibleCount = 0;

        assessmentCons.forEach(card => {
          const cardStatus = card.dataset.status;
          const cardService = card.dataset.service;

          const statusMatch = selectedStatus === 'all' || cardStatus === selectedStatus;
          const serviceMatch = selectedService === 'all' || cardService.includes(selectedService);

          if (statusMatch && serviceMatch) {
            card.classList.remove('d-none');
            visibleCount++;
          } else {
            card.classList.add('d-none');
          }
        });

        if (emptyStateMessage) {
          emptyStateMessage.classList.toggle('d-none', visibleCount !== 0);
        }
      }

      statusFilter.addEventListener('change', applyFilters);
      serviceFilter.addEventListener('change', applyFilters);
    });
  </script>

</body>

</html>