<?php

function log_activity($conn, $param2, $param3 = null, $param4 = null, $param5 = null, $param6 = null, $param7 = null, $param8 = null) {
    // Detect if this is OLD or NEW calling method
    // OLD method: param2 is numeric (employee_id/user_id), param3 is string (name)
    // NEW method: param2 is string (action), param3 is string (module)
    
    if (is_numeric($param2) && is_string($param3) && !in_array(strtoupper($param3), ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'ACCEPT', 'REJECT', 'ARCHIVE', 'RESTORE', 'VIEW'])) {
        // OLD METHOD: Explicit employee_id/user_id and name passed
        $actor_id = $param2;
        $actor_name = $param3;
        $action = $param4;
        $module = $param5;
        $item_id = $param6;
        $item_name = $param7;
        $description = $param8;
        
        // Determine if this is employee or user based on session or ID
        if (isset($_SESSION['employee_id']) && $_SESSION['employee_id'] == $actor_id) {
            $actor_type = 'employee';
        } else {
            $actor_type = 'user';
        }
    } else {
        // NEW METHOD: Get actor from session
        $action = $param2;
        $module = $param3;
        $item_id = $param4;
        $item_name = $param5;
        $description = $param6;
        
        if (isset($_SESSION['employee_id'])) {
            $actor_type = 'employee';
            $actor_id = $_SESSION['employee_id'];
            $actor_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        } elseif (isset($_SESSION['user_id'])) {
            $actor_type = 'user';
            $actor_id = $_SESSION['user_id'];
            $actor_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        } else {
            return false; // No valid session
        }
    }
    
    // Escape all string values
    $actor_name = mysqli_real_escape_string($conn, $actor_name);
    $action = mysqli_real_escape_string($conn, $action);
    $module = mysqli_real_escape_string($conn, $module);
    $item_name = $item_name ? mysqli_real_escape_string($conn, $item_name) : null;
    $description = $description ? mysqli_real_escape_string($conn, $description) : null;
    
    // Build SQL based on actor type
    if ($actor_type == 'employee') {
        $sql = "INSERT INTO activity_logs 
                (employee_id, employee_name, action, module, item_id, item_name, description) 
                VALUES 
                ($actor_id, '$actor_name', '$action', '$module', " . 
                ($item_id ? $item_id : 'NULL') . ", " .
                ($item_name ? "'$item_name'" : 'NULL') . ", " .
                ($description ? "'$description'" : 'NULL') . ")";
    } else {
        $sql = "INSERT INTO activity_logs 
                (user_id, user_name, action, module, item_id, item_name, description) 
                VALUES 
                ($actor_id, '$actor_name', '$action', '$module', " . 
                ($item_id ? $item_id : 'NULL') . ", " .
                ($item_name ? "'$item_name'" : 'NULL') . ", " .
                ($description ? "'$description'" : 'NULL') . ")";
    }
    
    return mysqli_query($conn, $sql);
}


?>