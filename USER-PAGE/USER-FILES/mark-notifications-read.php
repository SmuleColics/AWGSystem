<?php
session_start();
include '../../INCLUDES/db-con.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

$update_sql = "UPDATE notifications 
              SET is_read = 1 
              WHERE recipient_id = $user_id 
              AND is_read = 0
              AND type IN ('ASSESSMENT_ACCEPTED', 'ASSESSMENT_REJECTED', 'QUOTATION_CREATED')";

if (mysqli_query($conn, $update_sql)) {
  $referrer = $_SERVER['HTTP_REFERER'] ?? 'user-portal.php';
  header('Location: ' . $referrer);
  exit;
} else {
  echo "Error: " . mysqli_error($conn);
}
?>