<?php
session_start();
require_once '../../INCLUDES/db-con.php';
require_once '../../INCLUDES/log-activity.php';
require_once '../../INCLUDES/notifications.php';

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
    $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, trim($_POST['payment_method'])) : '';
    $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($conn, trim($_POST['reference_number'])) : null;
    $gcash_number = isset($_POST['gcash_number']) ? mysqli_real_escape_string($conn, trim($_POST['gcash_number'])) : null;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : null;
    
    // Validate inputs
    if ($amount <= 0) {
        $_SESSION['error'] = 'Payment amount must be greater than 0';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    if (empty($payment_method) || !in_array($payment_method, ['Cash', 'GCash'])) {
        $_SESSION['error'] = 'Invalid payment method selected';
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
    
    if (!mysqli_begin_transaction($conn)) {
        $_SESSION['error'] = 'Database transaction error';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    try {
        // Insert payment record with gcash_number support
        $insert_payment_sql = "INSERT INTO project_payments 
                              (project_id, payment_amount, payment_method, reference_number, gcash_number, payment_notes, payment_date, processed_by, created_at)
                              VALUES (
                                  $project_id, 
                                  $amount, 
                                  '$payment_method', 
                                  " . ($reference_number ? "'$reference_number'" : "NULL") . ", 
                                  " . ($gcash_number ? "'$gcash_number'" : "NULL") . ", 
                                  " . ($notes ? "'$notes'" : "NULL") . ", 
                                  CURDATE(), 
                                  $employee_id, 
                                  NOW()
                              )";
        
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
        if ($notes) {
            $payment_update_desc .= " Notes: $notes";
        }
        
        $insert_update_sql = "INSERT INTO project_updates 
                             (project_id, update_title, update_description, created_by, is_archived, created_at)
                             VALUES ($project_id, '$payment_update_title', '$payment_update_desc', $employee_id, 0, NOW())";
        
        if (!mysqli_query($conn, $insert_update_sql)) {
            throw new Exception('Failed to create payment update: ' . mysqli_error($conn));
        }
        
        // Log activity
        $payment_description = "Processed payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' via $payment_method";
        if ($reference_number) {
            $payment_description .= " (Ref: $reference_number)";
        }
        $payment_description .= ". Remaining balance: ₱" . number_format($new_remaining_balance, 2);
        
        log_activity(
            $conn,
            $employee_id,
            $employee_name,
            'CREATE',
            'PROJECT_PAYMENT',
            $payment_id,
            "Payment - {$project['project_name']}",
            $payment_description
        );
        
        // Get all admins for notification (except the one who processed the payment)
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
                    'payment_received',
                    'Payment Processed',
                    "$employee_name processed a payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' via $payment_method. Remaining balance: ₱" . number_format($new_remaining_balance, 2),
                    "admin-projects-detail.php?id=$project_id",
                    $project_id
                );
            }
        }
        
        // Send notification to client/user
        $user_notification_message = "Your payment of ₱" . number_format($amount, 2) . " for project '{$project['project_name']}' has been successfully processed";
        if ($new_remaining_balance > 0) {
            $user_notification_message .= ". Remaining balance: ₱" . number_format($new_remaining_balance, 2);
        } else {
            $user_notification_message .= ". Your project is now fully paid!";
        }
        
        create_notification(
            $conn,
            $project['user_id'],
            $employee_id,
            $employee_name,
            'payment_received',
            'Payment Received',
            $user_notification_message,
            "user-project-details.php?id=$project_id",
            $project_id
        );
        
        // Check if project is fully paid and create additional notification
        if ($new_remaining_balance <= 0) {
            // Log full payment
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'UPDATE',
                'PROJECT',
                $project_id,
                $project['project_name'],
                "Project '{$project['project_name']}' is now fully paid"
            );
            
            // Notify all admins about full payment
            $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
            $admins_result = mysqli_query($conn, $admins_sql);
            
            if ($admins_result) {
                while ($admin = mysqli_fetch_assoc($admins_result)) {
                    create_notification(
                        $conn,
                        $admin['employee_id'],
                        $employee_id,
                        $employee_name,
                        'project_fully_paid',
                        'Project Fully Paid',
                        "Project '{$project['project_name']}' has been fully paid by the client!",
                        "admin-projects-detail.php?id=$project_id",
                        $project_id
                    );
                }
            }
            
            // Notify client about full payment
            create_notification(
                $conn,
                $project['user_id'],
                $employee_id,
                $employee_name,
                'project_fully_paid',
                'Project Fully Paid!',
                "Congratulations! Your project '{$project['project_name']}' has been fully paid. Thank you for your payment!",
                "user-project-details.php?id=$project_id",
                $project_id
            );
        }
        
        mysqli_commit($conn);
        
        $success_message = 'Payment processed successfully! Amount: ₱' . number_format($amount, 2);
        if ($new_remaining_balance <= 0) {
            $success_message .= ' - Project is now fully paid!';
        }
        $_SESSION['success'] = $success_message;
        
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