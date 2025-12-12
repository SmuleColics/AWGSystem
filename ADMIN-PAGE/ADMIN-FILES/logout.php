<?php
session_start();
include '../../INCLUDES/db-con.php';
include '../../INCLUDES/log-activity.php';

if (isset($_SESSION['employee_id']) && $_SESSION['user_type'] === 'employee') {
    $employee_id = $_SESSION['employee_id'];
    $employee_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

    log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'LOGOUT',
        'AUTHENTICATION',
        null,
        null,
        'Employee logged out of the system'
    );

    session_unset();
    session_destroy();
}

echo "<script>
        alert('You have been logged out successfully.');
        window.location.href = '../../LOGS/LOGS-FILES/login.php';
      </script>";
exit;
?>
