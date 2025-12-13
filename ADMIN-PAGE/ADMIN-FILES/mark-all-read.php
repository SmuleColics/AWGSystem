<?php

session_start();
include '../../INCLUDES/db-con.php';
include '../../INCLUDES/notifications.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: ../../LOGS-PAGE/LOGS-FILES/login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
mark_all_read($conn, $employee_id);

// Redirect back to previous page or dashboard
$redirect = $_SERVER['HTTP_REFERER'] ?? 'admin-dashboard.php';
header("Location: $redirect");
exit;
?>

