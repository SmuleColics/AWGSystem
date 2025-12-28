<?php
session_start();
require_once '../../INCLUDES/db-con.php';
require_once '../../INCLUDES/log-activity.php';
require_once '../../INCLUDES/notifications.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  $_SESSION['error'] = 'Unauthorized access. Please log in first.';
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['first_name']) && isset($_SESSION['last_name'])
  ? $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
  : 'User';

// Handle Process Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
  $project_id = intval($_POST['project_id']);
  $amount = floatval($_POST['amount']);
  $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, trim($_POST['payment_method'])) : '';
  $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($conn, trim($_POST['reference_number'])) : null;
  $gcash_number = isset($_POST['gcash_number']) ? mysqli_real_escape_string($conn, trim($_POST['gcash_number'])) : null;
  $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : null;

  // Validate project_id
  if ($project_id === 0) {
    $_SESSION['error'] = 'Invalid project ID';
    header("Location: ../../USER-PAGE/USER-FILES/user-awg-projects.php");
    exit;
  }

  // Validate amount
  if ($amount <= 0) {
    $_SESSION['error'] = 'Payment amount must be greater than 0';
    header("Location: ../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
    exit;
  }

  // Validate payment method
  if (empty($payment_method) || !in_array($payment_method, ['Cash', 'GCash'])) {
    $_SESSION['error'] = 'Invalid payment method selected';
    header("Location: ../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
    exit;
  }

  // Validate GCash details if GCash is selected
  if ($payment_method === 'GCash') {
    if (empty($gcash_number)) {
      $_SESSION['error'] = 'GCash number is required for GCash payments';
      header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
      exit;
    }

    if (!preg_match('/^[0-9]{11}$/', $gcash_number)) {
      $_SESSION['error'] = 'GCash number must be exactly 11 digits';
      header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
      exit;
    }

    if (empty($reference_number)) {
      $_SESSION['error'] = 'Reference number is required for GCash payments';
      header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
      exit;
    }
  }

  // Get project details - verify it belongs to the user
  $project_sql = "SELECT * FROM projects WHERE project_id = $project_id AND user_id = $user_id AND is_archived = 0";
  $project_result = mysqli_query($conn, $project_sql);

  if (!$project_result || mysqli_num_rows($project_result) === 0) {
    $_SESSION['error'] = 'Project not found or you do not have permission to access this project';
    header("Location: ../../USER-PAGE/USER-FILES/user-awg-projects.php");
    exit;
  }

  $project = mysqli_fetch_assoc($project_result);
  $remaining_balance = floatval($project['remaining_balance']);

  // Validate amount doesn't exceed remaining balance
  if ($amount > $remaining_balance) {
    $_SESSION['error'] = 'Payment amount cannot exceed remaining balance of ₱' . number_format($remaining_balance, 2);
    header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
    exit;
  }

  // Start transaction
  if (!mysqli_begin_transaction($conn)) {
    $_SESSION['error'] = 'Database transaction error. Please try again.';
    header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
    exit;
  }

  try {
    // Insert payment record
    $insert_payment_sql = "INSERT INTO project_payments 
                              (project_id, payment_amount, payment_method, reference_number, gcash_number, payment_notes, payment_date, created_at)
                              VALUES (
                                  $project_id, 
                                  $amount, 
                                  '$payment_method', 
                                  " . ($reference_number ? "'$reference_number'" : "NULL") . ", 
                                  " . ($gcash_number ? "'$gcash_number'" : "NULL") . ", 
                                  " . ($notes ? "'$notes'" : "NULL") . ", 
                                  CURDATE(), 
                                  NOW()
                              )";

    if (!mysqli_query($conn, $insert_payment_sql)) {
      throw new Exception('Failed to record payment: ' . mysqli_error($conn));
    }

    $payment_id = mysqli_insert_id($conn);

    // Calculate new totals
    $new_amount_paid = floatval($project['amount_paid']) + $amount;
    $new_remaining_balance = floatval($project['total_budget']) - $new_amount_paid;

    // Update project payment totals
    $update_project_sql = "UPDATE projects 
                              SET amount_paid = $new_amount_paid,
                                  remaining_balance = $new_remaining_balance,
                                  updated_at = NOW()
                              WHERE project_id = $project_id";

    if (!mysqli_query($conn, $update_project_sql)) {
      throw new Exception('Failed to update project: ' . mysqli_error($conn));
    }

    // Create project update record
    $payment_update_title = 'Payment Submitted';
    $payment_update_desc = "Payment of ₱" . number_format($amount, 2) . " submitted via $payment_method.";
    if ($reference_number) {
      $payment_update_desc .= " Reference: $reference_number";
    }
    if ($notes) {
      $payment_update_desc .= " Notes: $notes";
    }

    $insert_update_sql = "INSERT INTO project_updates 
                             (project_id, update_title, update_description, created_by, is_archived, created_at)
                             VALUES ($project_id, '$payment_update_title', '$payment_update_desc', NULL, 0, NOW())";

    if (!mysqli_query($conn, $insert_update_sql)) {
      throw new Exception('Failed to create payment update: ' . mysqli_error($conn));
    }

    // Get all admins for notification
    $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 LIMIT 100";
    $admins_result = mysqli_query($conn, $admins_sql);

    if ($admins_result) {
      // Send notification to all admins
      while ($admin = mysqli_fetch_assoc($admins_result)) {
        create_notification(
          $conn,
          $admin['employee_id'],
          $user_id,
          $user_name,
          'payment_submitted',
          'Payment Submitted',
          "$user_name submitted a payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' via $payment_method.",
          "admin-projects-detail.php?id=$project_id",
          $project_id
        );
      }
    }

    // Send confirmation notification to user
    $user_confirmation_message = "Your payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' has been submitted successfully.";
    if ($new_remaining_balance > 0) {
      $user_confirmation_message .= " Remaining balance: ₱" . number_format($new_remaining_balance, 2);
    } else {
      $user_confirmation_message .= " Your project payment is now complete!";
    }

    create_notification(
      $conn,
      $user_id,
      $user_id,
      $user_name,
      'payment_submitted',
      'Payment Submitted',
      $user_confirmation_message,
      "../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id",
      $project_id
    );

    // Check if project is fully paid
    if ($new_remaining_balance <= 0) {
      // Notify all admins about full payment
      $admins_result = mysqli_query($conn, $admins_sql);

      if ($admins_result) {
        while ($admin = mysqli_fetch_assoc($admins_result)) {
          create_notification(
            $conn,
            $admin['employee_id'],
            $user_id,
            $user_name,
            'project_fully_paid',
            'Project Fully Paid',
            "Project '{$project['project_name']}' has been fully paid by the client!",
            "admin-projects-detail.php?id=$project_id",
            $project_id
          );
        }
      }

      // Notify user about full payment
      create_notification(
        $conn,
        $user_id,
        $user_id,
        $user_name,
        'project_fully_paid',
        'Project Payment Complete!',
        "Congratulations! Your payment for project '{$project['project_name']}' is now complete. Thank you!",
        "../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id",
        $project_id
      );
    }

    // Commit transaction
    if (!mysqli_commit($conn)) {
      throw new Exception('Failed to commit transaction: ' . mysqli_error($conn));
    }

    // Set success message
    $success_message = 'Payment submitted successfully! Amount: ₱' . number_format($amount, 2);
    if ($new_remaining_balance <= 0) {
      $success_message .= ' - Project payment is now complete!';
    }
    $_SESSION['success'] = $success_message;
  } catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Payment processing failed: ' . $e->getMessage();
    error_log("User payment processing error for user $user_id: " . $e->getMessage());
  }

  header("Location: ../../USER-PAGE/USER-FILES/../../USER-PAGE/USER-FILES/user-project-monitoring.php?id=$project_id");
  exit;
}

// If not a POST request with process_payment, redirect
$_SESSION['error'] = 'Invalid request. Please try again.';
header('Location: ../../USER-PAGE/USER-FILES/user-awg-projects.php');
exit;
