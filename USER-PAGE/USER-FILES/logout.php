<?php
// Save as: ../../USER-PAGE/USER-FILES/user-logout.php
session_start();
include '../../INCLUDES/db-con.php';
include '../../INCLUDES/log-activity.php';

// Check if user is logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'user') {
    // USER LOGOUT
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $email = $_SESSION['email'];
    
    // LOG USER LOGOUT ACTIVITY
    log_activity(
        $conn,
        $user_id,
        $user_name,
        'LOGOUT',
        'SYSTEM',
        null,
        null,
        'User logged out | Email: ' . $email
    );
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to landing page
header("Location: ../../LANDING-PAGE/LP-FILES/LandingPage.php");
exit();
?>