<?php
session_start();
require_once '../../INCLUDES/db-con.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Handle Update Visibility
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = intval($_POST['project_id']);
    $visibility = mysqli_real_escape_string($conn, $_POST['visibility']);
    
    // Validate visibility value
    if (!in_array($visibility, ['Private', 'Public'])) {
        $_SESSION['error'] = 'Invalid visibility option';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    // Update project visibility
    $update_sql = "UPDATE projects 
                  SET visibility = '$visibility',
                      updated_at = NOW()
                  WHERE project_id = $project_id AND is_archived = 0";
    
    if (mysqli_query($conn, $update_sql)) {
        // Get project name for logging
        $project_sql = "SELECT project_name FROM projects WHERE project_id = $project_id";
        $project_result = mysqli_query($conn, $project_sql);
        $project = mysqli_fetch_assoc($project_result);
        
        // Log activity
        if (function_exists('log_activity')) {
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'UPDATE',
                'PROJECT_VISIBILITY',
                $project_id,
                $project['project_name'],
                "Changed project visibility to: $visibility"
            );
        }
        
        $_SESSION['success'] = "Project visibility updated to $visibility";
    } else {
        $_SESSION['error'] = 'Failed to update visibility: ' . mysqli_error($conn);
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>