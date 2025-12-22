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

// Handle ARCHIVE Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_update'])) {
    $project_id = intval($_POST['project_id']);
    $archive_update_id = intval($_POST['archive_update_id']);
    
    // Get project and update details for notifications
    $details_sql = "SELECT p.project_name, p.user_id, pu.update_title 
                    FROM projects p 
                    JOIN project_updates pu ON pu.project_id = p.project_id
                    WHERE p.project_id = $project_id AND pu.update_id = $archive_update_id";
    $details_result = mysqli_query($conn, $details_sql);
    $details = mysqli_fetch_assoc($details_result);
    
    $archive_sql = "UPDATE project_updates SET is_archived = 1 WHERE update_id = $archive_update_id";
    
    if (mysqli_query($conn, $archive_sql)) {
        // Log activity
        log_activity(
            $conn,
            $employee_id,
            $employee_name,
            'ARCHIVE',
            'PROJECT_UPDATE',
            $archive_update_id,
            $details['update_title'],
            "Archived project update '{$details['update_title']}' for project '{$details['project_name']}'"
        );
        
        $_SESSION['success'] = 'Update archived successfully!';
    } else {
        $_SESSION['error'] = 'Failed to archive update.';
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Handle Add/Edit Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $project_id = intval($_POST['project_id']);
    $update_id = isset($_POST['update_id']) && $_POST['update_id'] !== '' ? intval($_POST['update_id']) : null;
    $update_title = mysqli_real_escape_string($conn, trim($_POST['update_title']));
    $update_description = mysqli_real_escape_string($conn, trim($_POST['update_description']));
    $progress_percentage = intval($_POST['progress_percentage']);
    
    // Validate inputs
    if (empty($update_title) || empty($update_description)) {
        $_SESSION['error'] = 'Title and description are required';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    if ($progress_percentage < 0 || $progress_percentage > 100) {
        $_SESSION['error'] = 'Progress must be between 0 and 100';
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    // Get current project progress for validation
    $current_progress_sql = "SELECT progress_percentage, project_name, user_id FROM projects WHERE project_id = $project_id";
    $current_progress_result = mysqli_query($conn, $current_progress_sql);
    $project_data = mysqli_fetch_assoc($current_progress_result);
    $current_project_progress = intval($project_data['progress_percentage'] ?? 0);
    
    // Validate that new progress is greater than or equal to current progress (unless editing)
    if (!$update_id && $progress_percentage < $current_project_progress) {
        $_SESSION['error'] = "Progress percentage must be at least {$current_project_progress}% (current project progress)";
        header("Location: admin-projects-detail.php?id=$project_id");
        exit;
    }
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['update_image']) && $_FILES['update_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['update_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $allowed_types)) {
            $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, and WEBP are allowed';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
        
        if ($file['size'] > $max_size) {
            $_SESSION['error'] = 'File size must be less than 5MB';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
        
        // Create upload directory
        $upload_dir = '../uploads/project_updates/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'update_' . $project_id . '_' . time() . '.' . $extension;
        $image_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $image_path)) {
            $_SESSION['error'] = 'Failed to upload image';
            header("Location: admin-projects-detail.php?id=$project_id");
            exit;
        }
    }
    
    mysqli_begin_transaction($conn);
    
    try {
        if ($update_id) {
            // UPDATE existing update
            $update_sql = "UPDATE project_updates 
                          SET update_title = '$update_title',
                              update_description = '$update_description',
                              progress_percentage = $progress_percentage" .
                              ($image_path ? ", update_image = '" . mysqli_real_escape_string($conn, $image_path) . "'" : "") . "
                          WHERE update_id = $update_id AND project_id = $project_id";
            
            if (!mysqli_query($conn, $update_sql)) {
                throw new Exception('Failed to update project update');
            }
            
            // Log activity for EDIT
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'UPDATE',
                'PROJECT_UPDATE',
                $update_id,
                $update_title,
                "Edited project update '$update_title' for project '{$project_data['project_name']}' (Progress: $progress_percentage%)"
            );
            
            // Get all admins for notification (check is_archived field)
            $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
            $admins_result = mysqli_query($conn, $admins_sql);
            
            $success_msg = 'Project update updated successfully';
            
        } else {
            // CREATE new update
            $insert_sql = "INSERT INTO project_updates 
                          (project_id, update_title, update_description, progress_percentage, update_image, created_by, is_archived, created_at)
                          VALUES ($project_id, '$update_title', '$update_description', $progress_percentage, " .
                          ($image_path ? "'" . mysqli_real_escape_string($conn, $image_path) . "'" : "NULL") . ", 
                          $employee_id, 0, NOW())";
            
            if (!mysqli_query($conn, $insert_sql)) {
                throw new Exception('Failed to add project update');
            }
            
            $new_update_id = mysqli_insert_id($conn);
            
            // Log activity for NEW update
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'CREATE',
                'PROJECT_UPDATE',
                $new_update_id,
                $update_title,
                "Added new project update '$update_title' for project '{$project_data['project_name']}' (Progress: $progress_percentage%)"
            );
            
            // Get all admins for notification (check is_archived field)
            $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
            $admins_result = mysqli_query($conn, $admins_sql);
            
            // Send notification to other admins
            while ($admin = mysqli_fetch_assoc($admins_result)) {
                create_notification(
                    $conn,
                    $admin['employee_id'],
                    $employee_id,
                    $employee_name,
                    'project_update_added',
                    'New Project Update',
                    "$employee_name added update '$update_title' to project '{$project_data['project_name']}' - Progress: $progress_percentage%",
                    "admin-projects-detail.php?id=$project_id",
                    $project_id
                );
            }
            
            // Send notification to client
            create_notification(
                $conn,
                $project_data['user_id'],
                $employee_id,
                $employee_name,
                'project_update_added',
                'New Project Update Available',
                "A new update has been posted for your project '{$project_data['project_name']}': $update_title (Progress: $progress_percentage%)",
                "client-project-details.php?id=$project_id",
                $project_id
            );
            
            $success_msg = 'Project update added successfully';
        }
        
        // Update project progress (only if new progress is higher)
        if ($progress_percentage > $current_project_progress) {
            $update_progress_sql = "UPDATE projects 
                                   SET progress_percentage = $progress_percentage,
                                       updated_at = NOW()
                                   WHERE project_id = $project_id";
            
            if (!mysqli_query($conn, $update_progress_sql)) {
                throw new Exception('Failed to update project progress');
            }
        }
        
        // Check if project should be marked as completed
        if ($progress_percentage >= 100) {
            $check_status_sql = "SELECT status FROM projects WHERE project_id = $project_id";
            $check_result = mysqli_query($conn, $check_status_sql);
            $current_status = mysqli_fetch_assoc($check_result)['status'];
            
            if ($current_status !== 'Completed') {
                $complete_sql = "UPDATE projects 
                                SET status = 'Completed' 
                                WHERE project_id = $project_id";
                
                if (mysqli_query($conn, $complete_sql)) {
                    // Log project completion
                    log_activity(
                        $conn,
                        $employee_id,
                        $employee_name,
                        'UPDATE',
                        'PROJECT',
                        $project_id,
                        $project_data['project_name'],
                        "Project '{$project_data['project_name']}' marked as completed (100% progress)"
                    );
                    
                    // Get all admins for completion notification (check is_archived field)
                    $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
                    $admins_result = mysqli_query($conn, $admins_sql);
                    
                    // Notify other admins about completion
                    while ($admin = mysqli_fetch_assoc($admins_result)) {
                        create_notification(
                            $conn,
                            $admin['employee_id'],
                            $employee_id,
                            $employee_name,
                            'project_completed',
                            'Project Completed',
                            "$employee_name marked project '{$project_data['project_name']}' as completed",
                            "admin-projects-detail.php?id=$project_id",
                            $project_id
                        );
                    }
                    
                    // Notify client about completion
                    create_notification(
                        $conn,
                        $project_data['user_id'],
                        $employee_id,
                        $employee_name,
                        'project_completed',
                        'Project Completed!',
                        "Congratulations! Your project '{$project_data['project_name']}' has been completed",
                        "client-project-details.php?id=$project_id",
                        $project_id
                    );
                }
            }
        }
        
        mysqli_commit($conn);
        $_SESSION['success'] = $success_msg;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Handle Delete Update (Keep for backward compatibility, but we use archive now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_update'])) {
    $project_id = intval($_POST['project_id']);
    $update_id = intval($_POST['delete_update_id']);
    
    // Get update and project details
    $get_update_sql = "SELECT pu.update_title, p.project_name, p.user_id 
                      FROM project_updates pu 
                      JOIN projects p ON pu.project_id = p.project_id
                      WHERE pu.update_id = $update_id AND pu.project_id = $project_id";
    $get_update_result = mysqli_query($conn, $get_update_sql);
    
    if (mysqli_num_rows($get_update_result) > 0) {
        $update = mysqli_fetch_assoc($get_update_result);
        
        // Archive instead of delete
        $archive_sql = "UPDATE project_updates SET is_archived = 1 WHERE update_id = $update_id";
        
        if (mysqli_query($conn, $archive_sql)) {
            // Log activity
            log_activity(
                $conn,
                $employee_id,
                $employee_name,
                'ARCHIVE',
                'PROJECT_UPDATE',
                $update_id,
                $update['update_title'],
                "Archived project update '{$update['update_title']}' from project '{$update['project_name']}'"
            );
            
            // Get all admins for notification (check is_archived field)
            $admins_sql = "SELECT employee_id FROM employees WHERE is_archived = 0 AND employee_id != $employee_id";
            $admins_result = mysqli_query($conn, $admins_sql);
            
            // Send notification to other admins
            while ($admin = mysqli_fetch_assoc($admins_result)) {
                create_notification(
                    $conn,
                    $admin['employee_id'],
                    $employee_id,
                    $employee_name,
                    'project_update_archived',
                    'Project Update Archived',
                    "$employee_name archived update '{$update['update_title']}' from project '{$update['project_name']}'",
                    "admin-projects-detail.php?id=$project_id",
                    $project_id
                );
            }
            
            // Send notification to client
            create_notification(
                $conn,
                $update['user_id'],
                $employee_id,
                $employee_name,
                'project_update_archived',
                'Project Update Archived',
                "An update has been archived from your project '{$update['project_name']}'",
                "client-project-details.php?id=$project_id",
                $project_id
            );
            
            $_SESSION['success'] = 'Project update archived successfully';
        } else {
            $_SESSION['error'] = 'Failed to archive update';
        }
    } else {
        $_SESSION['error'] = 'Update not found';
    }
    
    header("Location: admin-projects-detail.php?id=$project_id");
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>