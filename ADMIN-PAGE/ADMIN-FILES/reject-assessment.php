<?php
// Save as: reject-assessment.php
session_start();
include '../../INCLUDES/db-con.php';
include '../../INCLUDES/log-activity.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_id']) || $_SESSION['user_type'] !== 'employee') {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'])) {
  $assessment_id = intval($_POST['assessment_id']);
  $reject_reason = mysqli_real_escape_string($conn, $_POST['reject_reason']);
  $other_reason = isset($_POST['other_reason']) ? mysqli_real_escape_string($conn, $_POST['other_reason']) : '';
  
  // Use other_reason if "Others" was selected
  $final_reason = ($reject_reason === 'Others' && !empty($other_reason)) ? $other_reason : $reject_reason;
  
  // Get assessment details
  $assessment_sql = "SELECT a.*, u.first_name, u.last_name 
                     FROM assessments a 
                     LEFT JOIN users u ON a.user_id = u.user_id 
                     WHERE a.assessment_id = $assessment_id";
  $assessment_result = mysqli_query($conn, $assessment_sql);
  
  if ($assessment_result && mysqli_num_rows($assessment_result) > 0) {
    $assessment = mysqli_fetch_assoc($assessment_result);
    
    // Update assessment status to Rejected and add rejection reason to notes
    $update_sql = "UPDATE assessments 
                   SET status = 'Rejected', 
                       notes = CONCAT(IFNULL(notes, ''), '\n\nRejection Reason: $final_reason')
                   WHERE assessment_id = $assessment_id";
    
    if (mysqli_query($conn, $update_sql)) {
      $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
      
      // LOG ACTIVITY - Changed action from 'UPDATE' to 'REJECT'
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'REJECT',
        'ASSESSMENTS',
        $assessment_id,
        $assessment['service_type'],
        'Assessment rejected for ' . $user_full_name . ' | Service: ' . $assessment['service_type'] . ' | Reason: ' . $final_reason
      );
      
      // CREATE NOTIFICATION FOR USER (CLIENT SIDE)
      $user_notif_title = 'Assessment Request Rejected';
      $user_notif_message = 'Hello ' . $user_full_name . ', your ' . $assessment['service_type'] . ' assessment request has been rejected. Reason: ' . $final_reason;
      $user_notif_link = 'user-assessments.php';
      
      $user_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read) 
                        VALUES ({$assessment['user_id']}, 'ASSESSMENT_REJECTED', 
                              '" . mysqli_real_escape_string($conn, $user_notif_title) . "',
                              '" . mysqli_real_escape_string($conn, $user_notif_message) . "',
                              '" . mysqli_real_escape_string($conn, $user_notif_link) . "',
                              0)";
      mysqli_query($conn, $user_notif_sql);
      
      // CREATE NOTIFICATION FOR ADMIN (ADMIN SIDE)
      $admin_notif_title = 'Assessment Rejected';
      $admin_notif_message = $user_full_name . '\'s ' . $assessment['service_type'] . ' assessment request has been rejected by ' . $employee_name . '. Reason: ' . $final_reason;
      $admin_notif_link = 'admin-assessments.php';
      
      $admin_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, sender_name) 
                        VALUES ($employee_id, 'ASSESSMENT_REJECTED_ADMIN', 
                              '" . mysqli_real_escape_string($conn, $admin_notif_title) . "',
                              '" . mysqli_real_escape_string($conn, $admin_notif_message) . "',
                              '" . mysqli_real_escape_string($conn, $admin_notif_link) . "',
                              0,
                              '" . mysqli_real_escape_string($conn, $employee_name) . "')";
      mysqli_query($conn, $admin_notif_sql);
      
      echo "<script>
              alert('Assessment rejected successfully!');
              window.location.href = 'admin-assessments.php';
            </script>";
    } else {
      echo "<script>
              alert('Error rejecting assessment: " . mysqli_error($conn) . "');
              window.location.href = 'admin-assessments.php';
            </script>";
    }
  } else {
    echo "<script>
            alert('Assessment not found!');
            window.location.href = 'admin-assessments.php';
          </script>";
  }
} else {
  header('Location: admin-assessments.php');
  exit;
}
?>