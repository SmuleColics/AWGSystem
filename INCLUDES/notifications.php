<?php

/**
 * Create a notification
 */
function create_notification($conn, $recipient_id, $sender_id, $sender_name, $type, $title, $message, $link = null, $related_id = null) {
    $sql = "INSERT INTO notifications 
            (recipient_id, sender_id, sender_name, type, title, message, link, related_id, is_read, created_at) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }
    
    // Bind parameters: i=integer, s=string
    mysqli_stmt_bind_param($stmt, "iisssssi", 
        $recipient_id, 
        $sender_id, 
        $sender_name, 
        $type, 
        $title, 
        $message, 
        $link, 
        $related_id
    );
    
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Get unread notification count for an employee
 */
function get_unread_count($conn, $employee_id) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE recipient_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row['count'] ?? 0;
}

/**
 * Get all notifications for an employee
 */
function get_notifications($conn, $employee_id, $limit = 5) {
    $sql = "SELECT * FROM notifications 
            WHERE recipient_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $employee_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $notifications;
}

/**
 * Mark notification as read
 */
function mark_notification_read($conn, $notification_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $notification_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Mark all notifications as read for an employee
 */
function mark_all_read($conn, $employee_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE recipient_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

?>