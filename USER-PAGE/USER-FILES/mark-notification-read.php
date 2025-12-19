<?php
session_start();
include '../../INCLUDES/db-con.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
  $notification_id = intval($_POST['notification_id']);
  
  $update_sql = "UPDATE notifications 
                SET is_read = 1 
                WHERE notification_id = $notification_id 
                AND recipient_id = $user_id";
  
  if (mysqli_query($conn, $update_sql)) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
  } else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
  }
} else {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>