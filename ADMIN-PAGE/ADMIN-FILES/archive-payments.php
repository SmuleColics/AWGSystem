<?php
session_start();
require_once '../../INCLUDES/db-con.php';
require_once '../../INCLUDES/log-activity.php';
require_once '../../INCLUDES/notifications.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['employee_id'])) {
  $_SESSION['error'] = 'Unauthorized access';
  header('Location: login.php');
  exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get project ID
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($project_id === 0) {
  $_SESSION['error'] = 'Invalid project ID';
  header('Location: admin-projects.php');
  exit;
}

// Get project details
$project_sql = "SELECT * FROM projects WHERE project_id = $project_id AND is_archived = 0";
$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  $_SESSION['error'] = 'Project not found';
  header('Location: admin-projects.php');
  exit;
}

$project = mysqli_fetch_assoc($project_result);

// Check if project is fully paid
if (floatval($project['remaining_balance']) > 0) {
  $_SESSION['error'] = 'Cannot archive payments. Project still has remaining balance of ₱' . number_format($project['remaining_balance'], 2);
  header("Location: admin-projects-detail.php?id=$project_id");
  exit;
}

if (!mysqli_begin_transaction($conn)) {
  $_SESSION['error'] = 'Database transaction error';
  header("Location: admin-projects-detail.php?id=$project_id");
  exit;
}

try {
  // Check if project_payments table has is_archived column, if not add it
  $check_column_sql = "SHOW COLUMNS FROM project_payments LIKE 'is_archived'";
  $check_result = mysqli_query($conn, $check_column_sql);

  if (mysqli_num_rows($check_result) === 0) {
    // Add is_archived column if it doesn't exist
    $add_column_sql = "ALTER TABLE project_payments ADD COLUMN is_archived TINYINT(1) DEFAULT 0 AFTER payment_notes";
    if (!mysqli_query($conn, $add_column_sql)) {
      throw new Exception('Failed to add is_archived column: ' . mysqli_error($conn));
    }
  }

  // Check if any payments are already archived
  $archived_check_sql = "SELECT COUNT(*) as archived_count FROM project_payments WHERE project_id = $project_id AND is_archived = 1";
  $archived_check_result = mysqli_query($conn, $archived_check_sql);
  $archived_check = mysqli_fetch_assoc($archived_check_result);

  if ($archived_check['archived_count'] > 0) {
    $_SESSION['error'] = 'Payment records are already archived for this project.';
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
  }

  // Get count of payments to archive
  $count_sql = "SELECT COUNT(*) as count FROM project_payments WHERE project_id = $project_id AND is_archived = 0";
  $count_result = mysqli_query($conn, $count_sql);
  $count_row = mysqli_fetch_assoc($count_result);
  $payment_count = $count_row['count'];

  if ($payment_count === 0) {
    $_SESSION['error'] = 'No payment records found to archive.';
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
  }

  // Archive all payments for this project
  $archive_sql = "UPDATE project_payments 
                  SET is_archived = 1 
                  WHERE project_id = $project_id AND is_archived = 0";

  if (!mysqli_query($conn, $archive_sql)) {
    throw new Exception('Failed to archive payments: ' . mysqli_error($conn));
  }

  // Create project update
  $update_title = 'Payments Archived';
  $update_desc = "All payment records ($payment_count payments totaling ₱" . number_format($project['amount_paid'], 2) . ") have been archived. Project is fully paid.";

  $insert_update_sql = "INSERT INTO project_updates 
                        (project_id, update_title, update_description, created_by, is_archived, created_at)
                        VALUES ($project_id, '$update_title', '$update_desc', $employee_id, 0, NOW())";

  if (!mysqli_query($conn, $insert_update_sql)) {
    throw new Exception('Failed to create payment archive update: ' . mysqli_error($conn));
  }

  // Log activity
  log_activity(
    $conn,
    $employee_id,
    $employee_name,
    'ARCHIVE',
    'PROJECT_PAYMENTS',
    $project_id,
    "Payments - {$project['project_name']}",
    "Archived $payment_count payment records for fully paid project '{$project['project_name']}' (Total: ₱" . number_format($project['amount_paid'], 2) . ")"
  );

  // Get all admins for notification (except the one who archived)
  $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
  $admins_result = mysqli_query($conn, $admins_sql);

  // Send notification to other admins
  if ($admins_result) {
    while ($admin = mysqli_fetch_assoc($admins_result)) {
      create_notification(
        $conn,
        $admin['employee_id'],
        $employee_id,
        $employee_name,
        'payments_archived',
        'Payments Archived',
        "$employee_name archived all payment records for project '{$project['project_name']}'. Total archived: ₱" . number_format($project['amount_paid'], 2) . " ($payment_count payments)",
        "admin-projects-detail.php?id=$project_id",
        $project_id
      );
    }
  }

  mysqli_commit($conn);
  $_SESSION['success'] = "Successfully archived $payment_count payment records for this fully paid project.";
} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['error'] = $e->getMessage();
  error_log("Payment archiving error: " . $e->getMessage());
}

header("Location: admin-projects-detail.php?id=$project_id");
exit;
