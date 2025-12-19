<?php
session_start();
include '../../INCLUDES/db-con.php';

if (!isset($_SESSION['employee_id'])) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
  $employee_id = $_SESSION['employee_id'];
  $notification_id = intval($_POST['notification_id']);

  $update = mysqli_query(
    $conn,
    "UPDATE notifications 
    SET is_read = 1 
    WHERE notification_id = $notification_id 
    AND recipient_id = $employee_id"
  );

  if ($update) {
    echo json_encode(['status' => 'success']);
  } else {
    http_response_code(500);
    echo json_encode(['status' => 'error']);
  }
} else {
  http_response_code(400);
  echo json_encode(['status' => 'invalid']);
}
