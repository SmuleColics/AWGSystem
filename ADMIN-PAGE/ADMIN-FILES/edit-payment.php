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

// Handle Edit Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $project_id = intval($_POST['project_id']);
    $new_amount = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, trim($_POST['payment_method']));
    $payment_date = mysqli_real_escape_string($conn, trim($_POST['payment_date']));
    $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($conn, trim($_POST['reference_number'])) : null;
    $gcash_number = isset($_POST['gcash_number']) ? mysqli_real_escape_string($conn, trim($_POST['gcash_number'])) : null;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : null;
    
    // Validate inputs
    if ($new_amount <= 0) {
        $_SESSION['error'] = 'Payment amount must be greater than 0';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    if (empty($payment_method) || !in_array($payment_method, ['Cash', 'GCash'])) {
        $_SESSION['error'] = 'Invalid payment method selected';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    // Validate GCash details if GCash is selected
    if ($payment_method === 'GCash') {
        if (empty($gcash_number)) {
            $_SESSION['error'] = 'GCash number is required for GCash payments';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
        
        if (!preg_match('/^[0-9]{11}$/', $gcash_number)) {
            $_SESSION['error'] = 'GCash number must be exactly 11 digits';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
        
        if (empty($reference_number)) {
            $_SESSION['error'] = 'Reference number is required for GCash payments';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
    }
    
    // Get old payment details
    $old_payment_sql = "SELECT * FROM project_payments WHERE payment_id = $payment_id";
    $old_payment_result = mysqli_query($conn, $old_payment_sql);
    
    if (mysqli_num_rows($old_payment_result) === 0) {
        $_SESSION['error'] = 'Payment record not found';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    $old_payment = mysqli_fetch_assoc($old_payment_result);
    $old_amount = floatval($old_payment['payment_amount']);
    
    // Get project details
    $project_sql = "SELECT * FROM projects WHERE project_id = $project_id AND is_archived = 0";
    $project_result = mysqli_query($conn, $project_sql);
    
    if (mysqli_num_rows($project_result) === 0) {
        $_SESSION['error'] = 'Project not found';
        header("Location: admin-projects.php");
        exit;
    }
    
    $project = mysqli_fetch_assoc($project_result);
    
    // Calculate new totals
    $current_amount_paid = floatval($project['amount_paid']);
    $new_total_paid = ($current_amount_paid - $old_amount) + $new_amount;
    $new_remaining_balance = floatval($project['total_budget']) - $new_total_paid;
    
    // Validate new amount doesn't exceed total budget
    if ($new_total_paid > floatval($project['total_budget'])) {
        $_SESSION['error'] = 'Total payments cannot exceed project budget of ₱' . number_format($project['total_budget'], 2);
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    if (!mysqli_begin_transaction($conn)) {
        $_SESSION['error'] = 'Database transaction error';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    try {
        // Update payment record
        $update_payment_sql = "UPDATE project_payments 
                              SET payment_amount = $new_amount,
                                  payment_method = '$payment_method',
                                  payment_date = '$payment_date',
                                  reference_number = " . ($reference_number ? "'$reference_number'" : "NULL") . ",
                                  gcash_number = " . ($gcash_number ? "'$gcash_number'" : "NULL") . ",
                                  payment_notes = " . ($notes ? "'$notes'" : "NULL") . "
                              WHERE payment_id = $payment_id";
        
        if (!mysqli_query($conn, $update_payment_sql)) {
            throw new Exception('Failed to update payment: ' . mysqli_error($conn));
        }
        
        // Update project payment totals
        $update_project_sql = "UPDATE projects 
                              SET amount_paid = $new_total_paid,
                                  remaining_balance = $new_remaining_balance,
                                  updated_at = NOW()
                              WHERE project_id = $project_id";
        
        if (!mysqli_query($conn, $update_project_sql)) {
            throw new Exception('Failed to update project payment totals: ' . mysqli_error($conn));
        }
        
        // Create project update for payment edit
        $amount_change = $new_amount - $old_amount;
        $change_text = $amount_change > 0 ? 'increased by' : 'decreased by';
        $payment_update_title = 'Payment Updated';
        $payment_update_desc = "Payment record edited. Amount $change_text ₱" . number_format(abs($amount_change), 2) . ". New payment amount: ₱" . number_format($new_amount, 2) . " via $payment_method.";
        
        if ($reference_number) {
            $payment_update_desc .= " Reference: $reference_number";
        }
        
        $insert_update_sql = "INSERT INTO project_updates 
                             (project_id, update_title, update_description, created_by, is_archived, created_at)
                             VALUES ($project_id, '$payment_update_title', '$payment_update_desc', $employee_id, 0, NOW())";
        
        if (!mysqli_query($conn, $insert_update_sql)) {
            throw new Exception('Failed to create payment update: ' . mysqli_error($conn));
        }
        
        // Log activity
        $payment_description = "Edited payment record for project '{$project['project_name']}'. Changed from ₱" . number_format($old_amount, 2) . " to ₱" . number_format($new_amount, 2) . ". New remaining balance: ₱" . number_format($new_remaining_balance, 2);
        
        log_activity(
            $conn,
            $employee_id,
            $employee_name,
            'UPDATE',
            'PROJECT_PAYMENT',
            $payment_id,
            "Payment - {$project['project_name']}",
            $payment_description
        );
        
        // Get all admins for notification (except the one who edited)
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
                    'payment_updated',
                    'Payment Updated',
                    "$employee_name edited a payment record for project '{$project['project_name']}'. New amount: ₱" . number_format($new_amount, 2) . ". Remaining balance: ₱" . number_format($new_remaining_balance, 2),
                    "admin-projects-detail.php?id=$project_id",
                    $project_id
                );
            }
        }
        
        // Send notification to client/user
        create_notification(
            $conn,
            $project['user_id'],
            $employee_id,
            $employee_name,
            'payment_updated',
            'Payment Record Updated',
            "A payment record for your project '{$project['project_name']}' has been updated. New remaining balance: ₱" . number_format($new_remaining_balance, 2),
            "user-project-details.php?id=$project_id",
            $project_id
        );
        
        mysqli_commit($conn);
        $_SESSION['success'] = 'Payment updated successfully! New amount: ₱' . number_format($new_amount, 2);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        error_log("Payment edit error: " . $e->getMessage());
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>