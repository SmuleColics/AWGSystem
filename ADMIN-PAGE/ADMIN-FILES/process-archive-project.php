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

// Handle ARCHIVE Project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_project'])) {
    $project_id = intval($_POST['project_id']);
    
    // Get project details
    $project_sql = "SELECT p.*, u.user_id 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.user_id
                    WHERE p.project_id = $project_id AND p.is_archived = 0";
    $project_result = mysqli_query($conn, $project_sql);
    
    if (mysqli_num_rows($project_result) === 0) {
        $_SESSION['error'] = 'Project not found';
        header('Location: admin-projects.php');
        exit;
    }
    
    $project = mysqli_fetch_assoc($project_result);
    
    // Archive the project
    $archive_sql = "UPDATE projects SET is_archived = 1 WHERE project_id = $project_id";
    
    if (mysqli_query($conn, $archive_sql)) {
        // Log activity
        log_activity(
            $conn,
            $employee_id,
            $employee_name,
            'ARCHIVE',
            'PROJECT',
            $project_id,
            $project['project_name'],
            "Archived project '{$project['project_name']}'"
        );
        
        // Get all admins for notification
        $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
        $admins_result = mysqli_query($conn, $admins_sql);
        
        $_SESSION['success'] = 'Project archived successfully!';
    } else {
        $_SESSION['error'] = 'Failed to archive project.';
    }
    
    header('Location: admin-projects.php');
    exit;
}

// Handle RESTORE Project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_project'])) {
    $project_id = intval($_POST['project_id']);
    
    // Get project details
    $project_sql = "SELECT p.*, u.user_id 
                    FROM projects p 
                    JOIN users u ON p.user_id = u.user_id
                    WHERE p.project_id = $project_id AND p.is_archived = 1";
    $project_result = mysqli_query($conn, $project_sql);
    
    if (mysqli_num_rows($project_result) === 0) {
        $_SESSION['error'] = 'Archived project not found';
        header('Location: admin-archive-project.php');
        exit;
    }
    
    $project = mysqli_fetch_assoc($project_result);
    
    // Restore the project
    $restore_sql = "UPDATE projects SET is_archived = 0 WHERE project_id = $project_id";
    
    if (mysqli_query($conn, $restore_sql)) {
        // Log activity
        log_activity(
            $conn,
            $employee_id,
            $employee_name,
            'RESTORE',
            'PROJECT',
            $project_id,
            $project['project_name'],
            "Restored project '{$project['project_name']}' from archive"
        );
        
        // Get all admins for notification
        $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
        $admins_result = mysqli_query($conn, $admins_sql);
      
        $_SESSION['success'] = 'Project restored successfully!';
    } else {
        $_SESSION['error'] = 'Failed to restore project.';
    }
    
    header('Location: admin-archive-project.php');
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>