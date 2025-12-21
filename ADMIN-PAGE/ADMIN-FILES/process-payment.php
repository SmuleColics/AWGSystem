<?php
session_start();
require_once '../../INCLUDES/db-con.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Handle Process Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $project_id = intval($_POST['project_id']);
    $amount = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, trim($_POST['payment_method']));
    $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($conn, trim($_POST['reference_number'])) : null;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : null;
    
    // Validate inputs
    if ($amount <= 0) {
        $_SESSION['error'] = 'Payment amount must be greater than 0';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    if (empty($payment_method)) {
        $_SESSION['error'] = 'Payment method is required';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    // Get project details
    $project_sql = "SELECT * FROM projects WHERE project_id = $project_id AND is_archived = 0";
    $project_result = mysqli_query($conn, $project_sql);
    
    if (mysqli_num_rows($project_result) === 0) {
        $_SESSION['error'] = 'Project not found';
        header("Location: admin-projects.php");
        exit;
    }
    
    $project = mysqli_fetch_assoc($project_result);
    $remaining_balance = floatval($project['remaining_balance']);
    
    // Validate amount doesn't exceed remaining balance
    if ($amount > $remaining_balance) {
        $_SESSION['error'] = 'Payment amount cannot exceed remaining balance of ₱' . number_format($remaining_balance, 2);
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    mysqli_begin_transaction($conn);
    
    try {
        // Insert payment record
        $insert_payment_sql = "INSERT INTO project_payments 
                              (project_id, amount, payment_method, reference_number, notes, payment_date, processed_by, created_at)
                              VALUES ($project_id, $amount, '$payment_method', " .
                              ($reference_number ? "'$reference_number'" : "NULL") . ", " .
                              ($notes ? "'$notes'" : "NULL") . ", NOW(), $employee_id, NOW())";
        
        if (!mysqli_query($conn, $insert_payment_sql)) {
            throw new Exception('Failed to record payment: ' . mysqli_error($conn));
        }
        
        $payment_id = mysqli_insert_id($conn);
        
        // Update project payment totals
        $new_amount_paid = floatval($project['amount_paid']) + $amount;
        $new_remaining_balance = floatval($project['total_budget']) - $new_amount_paid;
        
        $update_project_sql = "UPDATE projects 
                              SET amount_paid = $new_amount_paid,
                                  remaining_balance = $new_remaining_balance,
                                  updated_at = NOW()
                              WHERE project_id = $project_id";
        
        if (!mysqli_query($conn, $update_project_sql)) {
            throw new Exception('Failed to update project payment totals: ' . mysqli_error($conn));
        }
        
        // Create project update for payment
        $payment_update_title = 'Payment Received';
        $payment_update_desc = "Payment of ₱" . number_format($amount, 2) . " received via $payment_method.";
        if ($reference_number) {
            $payment_update_desc .= " Reference: $reference_number";
        }
        
        $insert_update_sql = "INSERT INTO project_updates 
                             (project_id, update_title, update_description, created_by, created_at)
                             VALUES ($project_id, '$payment_update_title', '$payment_update_desc', $employee_id, NOW())";
        mysqli_query($conn, $insert_update_sql);
        
        // Log activity
        if (function_exists('log_activity')) {
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'CREATE',
                'PROJECT_PAYMENT',
                $payment_id,
                $project['project_name'],
                "Processed payment of ₱" . number_format($amount, 2) . " for project: {$project['project_name']} via $payment_method"
            );
        }
        
        // Create notification for user
        $user_id = $project['user_id'];
        $notif_title = 'Payment Received';
        $notif_message = "Your payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' has been received. Remaining balance: ₱" . number_format($new_remaining_balance, 2);
        $notif_link = "user-projects-detail.php?id=$project_id";
        
        $notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, created_at)
                    VALUES ($user_id, 'PAYMENT_RECEIVED', 
                          '" . mysqli_real_escape_string($conn, $notif_title) . "',
                          '" . mysqli_real_escape_string($conn, $notif_message) . "',
                          '" . mysqli_real_escape_string($conn, $notif_link) . "',
                          0, NOW())";
        mysqli_query($conn, $notif_sql);
        
        mysqli_commit($conn);
        $_SESSION['success'] = 'Payment processed successfully. Amount: ₱' . number_format($amount, 2);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        error_log("Payment processing error: " . $e->getMessage());
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>