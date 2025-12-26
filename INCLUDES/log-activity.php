<?php

function log_activity($conn, $employee_id, $employee_name, $action, $module, $item_id = null, $item_name = null, $description = null) {

    $employee_name = mysqli_real_escape_string($conn, $employee_name);
    $action = mysqli_real_escape_string($conn, $action);
    $module = mysqli_real_escape_string($conn, $module);
    $item_name = $item_name ? mysqli_real_escape_string($conn, $item_name) : null;
    $description = $description ? mysqli_real_escape_string($conn, $description) : null;

    $sql = "INSERT INTO activity_logs 
            (employee_id, employee_name, action, module, item_id, item_name, description) 
            VALUES 
            ($employee_id, '$employee_name', '$action', '$module', " . 
            ($item_id ? $item_id : 'NULL') . ", " .
            ($item_name ? "'$item_name'" : 'NULL') . ", " .
            ($description ? "'$description'" : 'NULL') . ")";
    
    mysqli_query($conn, $sql);
}

// Example usage:
// log_activity($conn, $employee_id, $employee_full_name, 'CREATE', 'INVENTORY', $item_id, $item_name, 'Added new CCTV camera to inventory');
?>