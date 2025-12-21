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

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_project_image'])) {
    $project_id = intval($_POST['project_id']);
    
    // Validate project exists
    $check_sql = "SELECT project_id, project_name FROM projects WHERE project_id = $project_id AND is_archived = 0";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) === 0) {
        $_SESSION['error'] = 'Project not found';
        header('Location: admin-projects.php');
        exit;
    }
    
    $project = mysqli_fetch_assoc($check_result);
    
    // Validate file upload
    if (!isset($_FILES['project_image']) || $_FILES['project_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Please select a valid image file';
        header('Location: admin-projects.php');
        exit;
    }
    
    $file = $_FILES['project_image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, and WEBP are allowed';
        header('Location: admin-projects.php');
        exit;
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size must be less than 5MB';
        header('Location: admin-projects.php');
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/projects/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'project_' . $project_id . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Delete old image if exists
    $old_image_sql = "SELECT project_image FROM projects WHERE project_id = $project_id";
    $old_image_result = mysqli_query($conn, $old_image_sql);
    $old_image_data = mysqli_fetch_assoc($old_image_result);
    
    if (!empty($old_image_data['project_image']) && file_exists($old_image_data['project_image'])) {
        unlink($old_image_data['project_image']);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Update database
        $update_sql = "UPDATE projects 
                      SET project_image = '" . mysqli_real_escape_string($conn, $upload_path) . "',
                          updated_at = NOW()
                      WHERE project_id = $project_id";
        
        if (mysqli_query($conn, $update_sql)) {
            // Log activity
            if (function_exists('log_activity')) {
                log_activity(
                    $conn,
                    $employee_id,
                    $employee_name,
                    'UPDATE',
                    'PROJECT_IMAGE',
                    $project_id,
                    $project['project_name'],
                    "Uploaded project image for: {$project['project_name']}"
                );
            }
            
            $_SESSION['success'] = 'Project image uploaded successfully';
        } else {
            // Delete uploaded file if database update fails
            unlink($upload_path);
            $_SESSION['error'] = 'Failed to update database: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Failed to upload file';
    }
    
    header('Location: admin-projects.php');
    exit;
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_image'])) {
    $project_id = intval($_POST['project_id']);
    
    // Get current image path
    $get_image_sql = "SELECT project_image, project_name FROM projects WHERE project_id = $project_id";
    $get_image_result = mysqli_query($conn, $get_image_sql);
    
    if (mysqli_num_rows($get_image_result) > 0) {
        $project = mysqli_fetch_assoc($get_image_result);
        
        // Delete file if exists
        if (!empty($project['project_image']) && file_exists($project['project_image'])) {
            unlink($project['project_image']);
        }
        
        // Update database
        $update_sql = "UPDATE projects 
                      SET project_image = NULL,
                          updated_at = NOW()
                      WHERE project_id = $project_id";
        
        if (mysqli_query($conn, $update_sql)) {
            // Log activity
            if (function_exists('log_activity')) {
                log_activity(
                    $conn,
                    $employee_id,
                    $employee_name,
                    'DELETE',
                    'PROJECT_IMAGE',
                    $project_id,
                    $project['project_name'],
                    "Deleted project image for: {$project['project_name']}"
                );
            }
            
            $_SESSION['success'] = 'Project image deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete image: ' . mysqli_error($conn);
        }
    }
    
    header('Location: admin-projects.php');
    exit;
}

// Invalid request
$_SESSION['error'] = 'Invalid request';
header('Location: admin-projects.php');
exit;
?>