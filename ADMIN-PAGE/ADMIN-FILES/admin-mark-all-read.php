<?php
session_start();
include '../../INCLUDES/db-con.php';

if (!isset($_SESSION['employee_id'])) {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

$employee_id = $_SESSION['employee_id'];

mysqli_query(
  $conn,
  "UPDATE notifications 
  SET is_read = 1 
  WHERE recipient_id = $employee_id
  AND is_read = 0
  AND type NOT IN ('ASSESSMENT_ACCEPTED','ASSESSMENT_REJECTED','QUOTATION_CREATED')"
);

$referrer = $_SERVER['HTTP_REFERER'] ?? 'admin-dashboard.php';
header("Location: $referrer");
exit;
