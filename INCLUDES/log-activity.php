<?php
// Save as: INCLUDES/log-activity.php

/**
 * Log user activity to database
 * 
 * @param mysqli $conn Database connection
 * @param int $employee_id Employee ID
 * @param string $employee_name Employee full name
 * @param string $action Action performed (LOGIN, CREATE, UPDATE, ARCHIVE, RESTORE, DELETE, VIEW)
 * @param string $module Module name (INVENTORY, EMPLOYEES, TASKS, etc.)
 * @param int|null $item_id ID of affected item (optional)
 * @param string|null $item_name Name of affected item (optional)
 * @param string|null $description Additional details (optional)
 */
function log_activity($conn, $employee_id, $employee_name, $action, $module, $item_id = null, $item_name = null, $description = null) {
    // Get user's IP address
    // $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Escape strings for SQL
    $employee_name = mysqli_real_escape_string($conn, $employee_name);
    $action = mysqli_real_escape_string($conn, $action);
    $module = mysqli_real_escape_string($conn, $module);
    $item_name = $item_name ? mysqli_real_escape_string($conn, $item_name) : null;
    $description = $description ? mysqli_real_escape_string($conn, $description) : null;
    // $ip_address = mysqli_real_escape_string($conn, $ip_address);
    
    // Build SQL query
    $sql = "INSERT INTO activity_logs 
            (employee_id, employee_name, action, module, item_id, item_name, description) 
            VALUES 
            ($employee_id, '$employee_name', '$action', '$module', " . 
            ($item_id ? $item_id : 'NULL') . ", " .
            ($item_name ? "'$item_name'" : 'NULL') . ", " .
            ($description ? "'$description'" : 'NULL') . ")";
    
    // Execute query
    mysqli_query($conn, $sql);
}

// Example usage:
// log_activity($conn, $employee_id, $employee_full_name, 'CREATE', 'INVENTORY', $item_id, $item_name, 'Added new CCTV camera to inventory');
?>