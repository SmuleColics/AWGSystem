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

// Check if it's a single payment restore or restore all
$payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
$restore_all = isset($_POST['restore_all']) ? true : false;

if ($project_id === 0) {
  $_SESSION['error'] = 'Invalid project ID';
  header('Location: admin-projects.php');
  exit;
}

// Get project details
$project_sql = "SELECT * FROM projects WHERE project_id = $project_id";
$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  $_SESSION['error'] = 'Project not found';
  header('Location: admin-projects.php');
  exit;
}

$project = mysqli_fetch_assoc($project_result);

if (!mysqli_begin_transaction($conn)) {
  $_SESSION['error'] = 'Database transaction error';
  header("Location: admin-archived-payments.php?project_id=$project_id");
  exit;
}

try {
  if ($restore_all) {
    // Restore all archived payments
    $restore_sql = "UPDATE project_payments 
                    SET is_archived = 0 
                    WHERE project_id = $project_id AND is_archived = 1";
    
    if (!mysqli_query($conn, $restore_sql)) {
      throw new Exception('Failed to restore payments: ' . mysqli_error($conn));
    }
    
    $affected_rows = mysqli_affected_rows($conn);
    
    // Create project update
    $update_title = 'Payments Restored';
    $update_desc = "All archived payment records ($affected_rows payments) have been restored to active status.";
    $update_desc = mysqli_real_escape_string($conn, $update_desc);
    
    $insert_update_sql = "INSERT INTO project_updates 
                          (project_id, update_title, update_description, created_by, is_archived, created_at)
                          VALUES ($project_id, '$update_title', '$update_desc', $employee_id, 0, NOW())";
    
    if (!mysqli_query($conn, $insert_update_sql)) {
      throw new Exception('Failed to create restore update: ' . mysqli_error($conn));
    }
    
    // Log activity
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'RESTORE',
      'PROJECT_PAYMENTS',
      $project_id,
      "Payments - {$project['project_name']}",
      "Restored $affected_rows archived payment records for project '{$project['project_name']}'"
    );
    
    $_SESSION['success'] = "Successfully restored $affected_rows payment records.";
    
  } else {
    // Restore single payment
    if ($payment_id === 0) {
      throw new Exception('Invalid payment ID');
    }
    
    // Get payment details
    $payment_sql = "SELECT * FROM project_payments WHERE payment_id = $payment_id";
    $payment_result = mysqli_query($conn, $payment_sql);
    
    if (mysqli_num_rows($payment_result) === 0) {
      throw new Exception('Payment not found');
    }
    
    $payment = mysqli_fetch_assoc($payment_result);
    
    // Restore payment
    $restore_sql = "UPDATE project_payments 
                    SET is_archived = 0 
                    WHERE payment_id = $payment_id";
    
    if (!mysqli_query($conn, $restore_sql)) {
      throw new Exception('Failed to restore payment: ' . mysqli_error($conn));
    }
    
    // Create project update
    $update_title = 'Payment Restored';
    $update_desc = "Payment of ₱" . number_format($payment['payment_amount'], 2) . " (dated " . date('M d, Y', strtotime($payment['payment_date'])) . ") has been restored to active status.";
    $update_desc = mysqli_real_escape_string($conn, $update_desc);
    
    $insert_update_sql = "INSERT INTO project_updates 
                          (project_id, update_title, update_description, created_by, is_archived, created_at)
                          VALUES ($project_id, '$update_title', '$update_desc', $employee_id, 0, NOW())";
    
    if (!mysqli_query($conn, $insert_update_sql)) {
      throw new Exception('Failed to create restore update: ' . mysqli_error($conn));
    }
    
    // Log activity
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'RESTORE',
      'PROJECT_PAYMENT',
      $payment_id,
      "Payment - ₱" . number_format($payment['payment_amount'], 2),
      "Restored archived payment for project '{$project['project_name']}'"
    );
    
    $_SESSION['success'] = "Payment successfully restored.";
  }
  
  // Get all admins for notification (except the one who restored)
  $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
  $admins_result = mysqli_query($conn, $admins_sql);
  
  // Send notification to other admins
  if ($admins_result) {
    $action_text = $restore_all ? "restored all archived payment records" : "restored a payment record";
    while ($admin = mysqli_fetch_assoc($admins_result)) {
      create_notification(
        $conn,
        $admin['employee_id'],
        $employee_id,
        $employee_name,
        'payment_restored',
        'Payment Restored',
        "$employee_name $action_text for project '{$project['project_name']}'",
        "admin-projects-detail.php?id=$project_id",
        $project_id
      );
    }
  }
  
  mysqli_commit($conn);
  
} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['error'] = $e->getMessage();
  error_log("Payment restore error: " . $e->getMessage());
}

header("Location: admin-archived-payments.php?project_id=$project_id");
exit;
?>