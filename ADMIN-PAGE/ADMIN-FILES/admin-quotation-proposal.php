<?php
ob_start();

date_default_timezone_set('Asia/Manila');
include 'admin-header.php';

// Check if user is logged in as employee
if (!isset($_SESSION['employee_id']) || $_SESSION['user_type'] !== 'employee') {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

$is_view_only = !$is_admin;

// Get assessment ID from URL
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assessment_id === 0) {
  header('Location: admin-assessments.php');
  exit;
}



// Fetch assessment details with user information
$sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, 
        u.house_no, u.brgy, u.city, u.province, u.zip_code
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE a.assessment_id = $assessment_id AND a.status IN ('Accepted', 'Completed')";

$result = mysqli_query($conn, $sql);
$assessment = mysqli_fetch_assoc($result);

if (!$assessment) {
  ob_end_clean(); // Clean the buffer before redirect
  header('Location: admin-assessments.php');
  exit;
}

// Fetch existing quotation if it exists
$quotation_sql = "SELECT * FROM quotations WHERE assessment_id = $assessment_id";
$quotation_result = mysqli_query($conn, $quotation_sql);
$quotation = mysqli_fetch_assoc($quotation_result);

// Fetch quotation items
$items_sql = "SELECT qi.*
              FROM quotation_items qi 
              WHERE qi.assessment_id = $assessment_id 
              ORDER BY qi.created_at ASC";
$items_result = mysqli_query($conn, $items_sql);
$quotation_items = [];
if ($items_result) {
  while ($row = mysqli_fetch_assoc($items_result)) {
    $quotation_items[] = $row;
  }
}

// Fetch labor charges
$labor_sql = "SELECT * FROM quotation_labor WHERE assessment_id = $assessment_id ORDER BY created_at ASC";
$labor_result = mysqli_query($conn, $labor_sql);
$labor_charges = [];
if ($labor_result) {
  while ($row = mysqli_fetch_assoc($labor_result)) {
    $labor_charges[] = $row;
  }
}

// Fetch inventory items for dropdown
$inventory_sql = "SELECT item_id, item_name, selling_price, quantity, quantity_unit, status
                  FROM inventory_items 
                  WHERE status IN ('In Stock', 'Low Stock')
                  ORDER BY item_name ASC";
$inventory_result = mysqli_query($conn, $inventory_sql);
$inventory_items = [];
if ($inventory_result) {
  while ($row = mysqli_fetch_assoc($inventory_result)) {
    $inventory_items[] = $row;
  }
}

// Initialize errors and form data
$errors = [];
$form_data = [
  'project_name' => $quotation['project_name'] ?? '',
  'category' => $quotation['category'] ?? $assessment['service_type'],
  'start_date' => $quotation['start_date'] ?? '',
  'end_date' => $quotation['end_date'] ?? '',
  'notes' => $quotation['notes'] ?? ''
];

// Handle delete item with archive - Using modal confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal-delete-button'])) {
  $item_id = intval($_POST['delete_item_id']);

  // Get item details before deletion
  $get_item_sql = "SELECT qi.*, ii.quantity as current_inventory_qty 
                  FROM quotation_items qi 
                  LEFT JOIN inventory_items ii ON qi.inventory_item_id = ii.item_id 
                  WHERE qi.item_id = $item_id AND qi.assessment_id = $assessment_id";
  $get_item_result = mysqli_query($conn, $get_item_sql);
  $item_to_delete = mysqli_fetch_assoc($get_item_result);

  if ($item_to_delete) {
    // If item is from inventory, restore quantity
    if ($item_to_delete['inventory_item_id']) {
      $restore_qty = $item_to_delete['current_inventory_qty'] + $item_to_delete['quantity'];
      $restore_sql = "UPDATE inventory_items 
                    SET quantity = $restore_qty 
                    WHERE item_id = {$item_to_delete['inventory_item_id']}";
      mysqli_query($conn, $restore_sql);

      // Update status after restoring quantity
      $new_status = '';
      if ($restore_qty <= 0) {
        $new_status = 'Out of Stock';
      } elseif ($restore_qty <= 10) {
        $new_status = 'Low Stock';
      } else {
        $new_status = 'In Stock';
      }

      $update_status_sql = "UPDATE inventory_items 
                          SET status = '$new_status' 
                          WHERE item_id = {$item_to_delete['inventory_item_id']}";
      mysqli_query($conn, $update_status_sql);

      // Archive the item if it was created for this quotation (negative or zero quantity after restoration)
      if ($restore_qty <= 0) {
        $archive_sql = "UPDATE inventory_items 
                      SET is_archived = 1 
                      WHERE item_id = {$item_to_delete['inventory_item_id']}";
        mysqli_query($conn, $archive_sql);
      }
    }

    // Delete from quotation_items
    $delete_sql = "DELETE FROM quotation_items WHERE item_id = $item_id";
    if (mysqli_query($conn, $delete_sql)) {
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'DELETE',
        'QUOTATION_ITEMS',
        $item_id,
        'Item Deleted',
        "Deleted quotation item from quotation for assessment #$assessment_id"
      );

      echo "<script>
        alert('Item deleted successfully');
        window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
      </script>";
      exit;
    }
  }
}

// Handle edit item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
  $edit_item_id = intval($_POST['edit_item_id']);
  $quantity = floatval($_POST['edit_quantity']);
  $unit_price = floatval($_POST['edit_unit_price']);
  $total = $quantity * $unit_price;

  $old_item_sql = "SELECT * FROM quotation_items WHERE item_id = $edit_item_id";
  $old_item_result = mysqli_query($conn, $old_item_sql);
  $old_item = mysqli_fetch_assoc($old_item_result);

  if ($old_item && $old_item['inventory_item_id']) {
    $qty_diff = $quantity - $old_item['quantity'];
    $update_inv_sql = "UPDATE inventory_items 
                      SET quantity = quantity - $qty_diff,
                          selling_price = $unit_price
                      WHERE item_id = {$old_item['inventory_item_id']}";
    mysqli_query($conn, $update_inv_sql);
  }

  $update_sql = "UPDATE quotation_items 
                SET quantity = $quantity, 
                    unit_price = $unit_price,
                    total = $total 
                WHERE item_id = $edit_item_id";

  if (mysqli_query($conn, $update_sql)) {
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'UPDATE',
      'QUOTATION_ITEMS',
      $edit_item_id,
      'Item Updated',
      "Updated quotation item in assessment #$assessment_id (Qty: $quantity, Price: ₱$unit_price)"
    );

    echo "<script>
      alert('Item updated successfully');
      window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
    </script>";
    exit;
  }
}

// Handle delete labor
if (isset($_GET['delete_labor'])) {
  $labor_id = intval($_GET['delete_labor']);
  $delete_sql = "DELETE FROM quotation_labor WHERE labor_id = $labor_id AND assessment_id = $assessment_id";
  if (mysqli_query($conn, $delete_sql)) {
    // Activity Log
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'DELETE',
      'QUOTATION_LABOR',
      $labor_id,
      'Labor Charge Deleted',
      "Deleted labor charge from quotation for assessment #$assessment_id"
    );

    echo "<script>
      alert('Labor charge deleted successfully');
      window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
    </script>";
    exit;
  }
}

// Handle add item to quotation - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
  $inventory_item_id = isset($_POST['item_id']) && $_POST['item_id'] !== '' ? intval($_POST['item_id']) : null;
  $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
  $quantity = floatval($_POST['quantity']);
  $unit_type = mysqli_real_escape_string($conn, trim($_POST['unit_type']));

  if ($inventory_item_id !== null) {
    $unit_price = floatval($_POST['unit_price']);
  } else {
    $unit_price = floatval($_POST['item_selling_price']);
  }

  $item_cost = isset($_POST['item_cost']) ? floatval($_POST['item_cost']) : 0;
  $item_selling_price = isset($_POST['item_selling_price']) ? floatval($_POST['item_selling_price']) : $unit_price;
  $total = $quantity * $unit_price;

  // Validation
  if (empty($item_name)) {
    $errors['item_name'] = 'Item name is required';
  }
  if ($quantity <= 0) {
    $errors['quantity'] = 'Quantity must be greater than 0';
  }
  if (empty($unit_type)) {
    $errors['unit_type'] = 'Unit type is required';
  }
  if ($inventory_item_id === null && $item_cost <= 0) {
    $errors['item_cost'] = 'Cost price must be greater than 0';
  }
  if ($inventory_item_id === null && $item_selling_price <= 0) {
    $errors['item_selling_price'] = 'Selling price must be greater than 0';
  }
  if ($inventory_item_id !== null && $unit_price <= 0) {
    $errors['unit_price'] = 'Unit price must be greater than 0';
  }

  if (empty($errors)) {
    $new_inventory_id = null;

    if ($inventory_item_id !== null) {
      $inv_check_sql = "SELECT quantity FROM inventory_items WHERE item_id = $inventory_item_id";
      $inv_check_result = mysqli_query($conn, $inv_check_sql);
      $inv_check = mysqli_fetch_assoc($inv_check_result);

      if ($inv_check) {
        $new_quantity = $inv_check['quantity'] - $quantity;
        $update_inv_sql = "UPDATE inventory_items SET quantity = $new_quantity WHERE item_id = $inventory_item_id";
        mysqli_query($conn, $update_inv_sql);

        $new_status = '';
        if ($new_quantity <= 0) {
          $new_status = 'Out of Stock';
        } elseif ($new_quantity <= 10) {
          $new_status = 'Low Stock';
        } else {
          $new_status = 'In Stock';
        }

        $update_status_sql = "UPDATE inventory_items SET status = '$new_status' WHERE item_id = $inventory_item_id";
        mysqli_query($conn, $update_status_sql);
        $new_inventory_id = $inventory_item_id;
      }
    } else {
      $negative_quantity = -$quantity;
      $service_type = mysqli_real_escape_string($conn, $assessment['service_type']);
      $insert_inventory_sql = "INSERT INTO inventory_items (item_name, category, quantity, quantity_unit, price, selling_price, status, location, supplier, is_archived, created_at)
                              VALUES ('$item_name', '$service_type', $negative_quantity, '$unit_type', '$item_cost', '$item_selling_price', 'Out of Stock', '', '', 0, NOW())";

      if (mysqli_query($conn, $insert_inventory_sql)) {
        $new_inventory_id = mysqli_insert_id($conn);
      }
    }

    // Add item to quotation_items
    $insert_sql = "INSERT INTO quotation_items (assessment_id, inventory_item_id, item_name, quantity, unit_type, unit_price, total, created_at)
                  VALUES ($assessment_id, " . ($new_inventory_id ? $new_inventory_id : "NULL") . ", '$item_name', $quantity, '$unit_type', $unit_price, $total, NOW())";

    if (mysqli_query($conn, $insert_sql)) {
      if ($inventory_item_id !== null) {
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'QUOTATION_ITEMS',
          $new_inventory_id,
          $item_name,
          "Added quotation item from inventory: $item_name (Qty: $quantity, Unit Price: ₱$unit_price) to assessment #$assessment_id"
        );
      } else {
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'QUOTATION_ITEMS',
          $new_inventory_id,
          $item_name,
          "Added new quotation item: $item_name (Qty: $quantity, Cost: ₱$item_cost, Selling Price: ₱$item_selling_price) to assessment #$assessment_id. Item created in inventory with deficit of $quantity units (qty: $negative_quantity)."
        );
      }

      echo "<script>
        alert('Item added successfully');
        window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
      </script>";
      exit;
    } else {
      $errors['general'] = 'Failed to add item: ' . mysqli_error($conn);
    }
  }
}

// Handle add labor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_labor'])) {
  $labor_description = mysqli_real_escape_string($conn, trim($_POST['labor_description']));
  $labor_amount = floatval($_POST['labor_amount']);

  // Validation
  if (empty($labor_description)) {
    $errors['labor_description'] = 'Labor description is required';
  }
  if ($labor_amount <= 0) {
    $errors['labor_amount'] = 'Amount must be greater than 0';
  }

  if (empty($errors)) {
    $insert_labor_sql = "INSERT INTO quotation_labor (assessment_id, description, amount, created_at)
                        VALUES ($assessment_id, '$labor_description', $labor_amount, NOW())";

    if (mysqli_query($conn, $insert_labor_sql)) {
      // Activity Log
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'CREATE',
        'QUOTATION_LABOR',
        $assessment_id,
        $labor_description,
        "Added labor charge: $labor_description (Amount: ₱$labor_amount) to assessment #$assessment_id"
      );

      echo "<script>
        alert('Labor charge added successfully');
        window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
      </script>";
      exit;
    } else {
      $errors['general'] = 'Failed to add labor: ' . mysqli_error($conn);
    }
  }
}

// Handle save project details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project_details'])) {
  $project_name = mysqli_real_escape_string($conn, trim($_POST['project_name']));
  $category = mysqli_real_escape_string($conn, trim($_POST['category']));
  $start_date = $_POST['start_date'] !== '' ? $_POST['start_date'] : null;
  $end_date = $_POST['end_date'] !== '' ? $_POST['end_date'] : null;
  $notes = mysqli_real_escape_string($conn, trim($_POST['notes']));

  // Validation
  if (empty($project_name)) {
    $errors['project_name'] = 'Project name is required';
  }
  if (empty($category)) {
    $errors['category'] = 'Category is required';
  }
  if ($start_date && $end_date && $start_date > $end_date) {
    $errors['date_range'] = 'Start date must be before end date';
  }

  // Show validation errors if any
  if (!empty($errors)) {
    $error_messages = [];
    foreach ($errors as $field => $message) {
      $error_messages[] = $message;
    }
    echo "<script>alert('" . implode("\\n", array_map('addslashes', $error_messages)) . "');</script>";
  }

  if (empty($errors)) {
    if ($quotation) {
      // Update existing quotation
      $update_sql = "UPDATE quotations 
                    SET project_name = '$project_name',
                        category = '$category',
                        start_date = " . ($start_date ? "'$start_date'" : "NULL") . ",
                        end_date = " . ($end_date ? "'$end_date'" : "NULL") . ",
                        notes = '$notes',
                        updated_at = NOW()
                    WHERE assessment_id = $assessment_id";

      if (mysqli_query($conn, $update_sql)) {
        // Activity Log
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'UPDATE',
          'QUOTATIONS',
          $assessment_id,
          $project_name,
          "Updated project details for assessment #$assessment_id - Project: $project_name"
        );

        echo "<script>
          alert('Project details updated successfully');
          window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
        </script>";
        exit;
      } else {
        $errors['general'] = 'Failed to update project details: ' . mysqli_error($conn);
      }
    } else {
      // Create new quotation
      $insert_sql = "INSERT INTO quotations (assessment_id, project_name, category, start_date, end_date, notes, status, created_at)
                    VALUES ($assessment_id, '$project_name', '$category', " . ($start_date ? "'$start_date'" : "NULL") . ", " . ($end_date ? "'$end_date'" : "NULL") . ", '$notes', 'Draft', NOW())";

      if (mysqli_query($conn, $insert_sql)) {
        $quotation_sql = "SELECT * FROM quotations WHERE assessment_id = $assessment_id";
        $quotation_result = mysqli_query($conn, $quotation_sql);
        $quotation = mysqli_fetch_assoc($quotation_result);

        // Activity Log
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'QUOTATIONS',
          $assessment_id,
          $project_name,
          "Created new quotation for assessment #$assessment_id - Project: $project_name"
        );

        echo "<script>
          alert('Project details created successfully');
          window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
        </script>";
        exit;
      } else {
        $errors['general'] = 'Failed to create project details: ' . mysqli_error($conn);
      }
    }
  }
}
// Handle Create Quotation (Complete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_quotation'])) {
  // Validate that we have at least project details and items
  if (!$quotation) {
    $errors['general'] = 'Please save project details first';
  } elseif (count($quotation_items) === 0 && count($labor_charges) === 0) {
    $errors['general'] = 'Please add at least one item or labor charge';
  } elseif (!$quotation['start_date'] || !$quotation['end_date']) {
    $errors['general'] = 'Please set both start and end dates before completing the quotation';
  }

  if (empty($errors)) {
    // Calculate total amount
    $items_total = 0;
    $labor_total = 0;

    foreach ($quotation_items as $item) {
      $items_total += $item['total'];
    }

    foreach ($labor_charges as $labor) {
      $labor_total += $labor['amount'];
    }

    $grand_total = $items_total + $labor_total;

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
      // Update quotation status and total amount
      $complete_sql = "UPDATE quotations 
                      SET status = 'Sent',
                          total_amount = $grand_total,
                          updated_at = NOW()
                      WHERE assessment_id = $assessment_id";

      if (!mysqli_query($conn, $complete_sql)) {
        throw new Exception("Failed to update quotation: " . mysqli_error($conn));
      }

      // Update assessment status to Completed
      $update_assessment_sql = "UPDATE assessments SET status = 'Completed' WHERE assessment_id = $assessment_id";
      if (!mysqli_query($conn, $update_assessment_sql)) {
        throw new Exception("Failed to update assessment: " . mysqli_error($conn));
      }

      // Get quotation ID
      $quotation_id_sql = "SELECT quotation_id FROM quotations WHERE assessment_id = $assessment_id";
      $quotation_id_result = mysqli_query($conn, $quotation_id_sql);
      $quotation_data = mysqli_fetch_assoc($quotation_id_result);
      $quotation_id = $quotation_data['quotation_id'];

      // Check if project already exists
      $check_project_sql = "SELECT project_id FROM projects WHERE quotation_id = $quotation_id";
      $check_project_result = mysqli_query($conn, $check_project_sql);

      if (mysqli_num_rows($check_project_result) == 0) {
        // Get user location
        $user_location = '';

        $location_sql = "SELECT CONCAT_WS(', ', house_no, brgy, city, province, zip_code) as location 
                        FROM users WHERE user_id = {$assessment['user_id']}";
        $location_result = mysqli_query($conn, $location_sql);
        if ($location_result && mysqli_num_rows($location_result) > 0) {
          $location_data = mysqli_fetch_assoc($location_result);
          $user_location = $location_data['location'] ?? '';
        }

        // Prepare project data
        $project_name_esc = mysqli_real_escape_string($conn, $quotation['project_name']);
        $project_type_esc = mysqli_real_escape_string($conn, $assessment['service_type']);
        $category_esc = mysqli_real_escape_string($conn, $quotation['category'] ?? '');
        $location_esc = mysqli_real_escape_string($conn, $user_location);
        $start_date = "'" . mysqli_real_escape_string($conn, $quotation['start_date']) . "'";
        $end_date = "'" . mysqli_real_escape_string($conn, $quotation['end_date']) . "'";
        $notes_esc = mysqli_real_escape_string($conn, $quotation['notes'] ?? '');
        $remaining_balance = $grand_total;

        // Calculate duration
        $duration = 'NULL';
        if (!empty($quotation['start_date']) && !empty($quotation['end_date'])) {
          $start = new DateTime($quotation['start_date']);
          $end = new DateTime($quotation['end_date']);
          $diff = $start->diff($end);
          $duration_str = $diff->days . ' days';
          $duration = "'" . mysqli_real_escape_string($conn, $duration_str) . "'";
        }

        // Insert project
        $insert_project_sql = "INSERT INTO projects 
                              (assessment_id, quotation_id, user_id, project_name, project_type, category,
                              location, start_date, end_date, duration, total_budget, amount_paid, 
                              remaining_balance, status, visibility, notes, is_archived, created_at) 
                              VALUES ($assessment_id, $quotation_id, {$assessment['user_id']}, 
                                      '$project_name_esc', '$project_type_esc', 
                                      " . (empty($category_esc) ? "NULL" : "'$category_esc'") . ", 
                                      " . (empty($location_esc) ? "NULL" : "'$location_esc'") . ", 
                                      $start_date, $end_date, $duration, $grand_total, 0, 
                                      $remaining_balance, 'In Progress', 'Private', 
                                      " . (empty($notes_esc) ? "NULL" : "'$notes_esc'") . ", 0, NOW())";

        if (!mysqli_query($conn, $insert_project_sql)) {
          throw new Exception("Failed to create project: " . mysqli_error($conn));
        }

        $project_id = mysqli_insert_id($conn);

        // Create initial project update
        $initial_update_sql = "INSERT INTO project_updates 
                              (project_id, update_title, update_description, progress_percentage, created_by, created_at) 
                              VALUES ($project_id, 'Project Created', 
                                      'Project has been created from approved quotation. Work will begin as scheduled.', 
                                      0, $employee_id, NOW())";
        mysqli_query($conn, $initial_update_sql);

        // LOG ACTIVITY FOR PROJECT CREATION
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'PROJECT',
          $project_id,
          $project_name_esc,
          "Auto-created project from quotation #$quotation_id. Total Budget: ₱" . number_format($grand_total, 2)
        );

        // CREATE NOTIFICATION FOR USER - PROJECT CREATED
        $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
        $user_project_notif_title = 'Project Started: ' . $quotation['project_name'];
        $user_project_notif_message = 'Hello ' . $user_full_name . ', your project "' . $quotation['project_name'] . '" has been created and will start on ' . date('F d, Y', strtotime($quotation['start_date'])) . '. Total Budget: ₱' . number_format($grand_total, 2);
        $user_project_notif_link = 'user-projects-detail.php?id=' . $project_id;

        $user_project_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, created_at) 
                                  VALUES ({$assessment['user_id']}, 'PROJECT_CREATED', 
                                        '" . mysqli_real_escape_string($conn, $user_project_notif_title) . "',
                                        '" . mysqli_real_escape_string($conn, $user_project_notif_message) . "',
                                        '" . mysqli_real_escape_string($conn, $user_project_notif_link) . "',
                                        0, NOW())";
        mysqli_query($conn, $user_project_notif_sql);

        // CREATE NOTIFICATION FOR ADMIN - PROJECT CREATED
        $admin_project_notif_title = 'New Project Created';
        $admin_project_notif_message = $employee_name . ' completed quotation and created project: ' . $quotation['project_name'] . ' for ' . $user_full_name . '. Budget: ₱' . number_format($grand_total, 2);
        $admin_project_notif_link = 'admin-projects-detail.php?id=' . $project_id;

        $admin_project_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, sender_name, created_at) 
                                  VALUES ($employee_id, 'PROJECT_CREATED_ADMIN', 
                                        '" . mysqli_real_escape_string($conn, $admin_project_notif_title) . "',
                                        '" . mysqli_real_escape_string($conn, $admin_project_notif_message) . "',
                                        '" . mysqli_real_escape_string($conn, $admin_project_notif_link) . "',
                                        0,
                                        '" . mysqli_real_escape_string($conn, $employee_name) . "',
                                        NOW())";
        mysqli_query($conn, $admin_project_notif_sql);
      }

      // LOG ACTIVITY FOR QUOTATION
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'COMPLETE',
        'QUOTATIONS',
        $assessment_id,
        'Quotation Completed',
        'Quotation for assessment #' . $assessment_id . ' completed and sent. Total Amount: ₱' . number_format($grand_total, 2)
      );

      // CREATE NOTIFICATION FOR USER - QUOTATION READY
      $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
      $user_notif_title = 'Your Quotation is Ready';
      $user_notif_message = 'Hello ' . $user_full_name . ', your quotation for ' . $quotation['project_name'] . ' is now ready for review. Total Amount: ₱' . number_format($grand_total, 2);
      $user_notif_link = 'user-assessments.php';

      $user_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, created_at) 
                        VALUES ({$assessment['user_id']}, 'QUOTATION_CREATED', 
                              '" . mysqli_real_escape_string($conn, $user_notif_title) . "',
                              '" . mysqli_real_escape_string($conn, $user_notif_message) . "',
                              '" . mysqli_real_escape_string($conn, $user_notif_link) . "',
                              0, NOW())";
      mysqli_query($conn, $user_notif_sql);

      // CREATE NOTIFICATION FOR ADMIN - QUOTATION SENT
      $admin_notif_title = 'Quotation Sent to Client';
      $admin_notif_message = $user_full_name . '\'s quotation for ' . $quotation['project_name'] . ' has been sent by ' . $employee_name . '. Total Amount: ₱' . number_format($grand_total, 2);
      $admin_notif_link = 'admin-quotation-proposal.php?id=' . $assessment_id;

      $admin_notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read, sender_name, created_at) 
                        VALUES ($employee_id, 'QUOTATION_SENT_ADMIN', 
                              '" . mysqli_real_escape_string($conn, $admin_notif_title) . "',
                              '" . mysqli_real_escape_string($conn, $admin_notif_message) . "',
                              '" . mysqli_real_escape_string($conn, $admin_notif_link) . "',
                              0,
                              '" . mysqli_real_escape_string($conn, $employee_name) . "',
                              NOW())";
      mysqli_query($conn, $admin_notif_sql);

      // Commit transaction
      mysqli_commit($conn);

      echo "<script>
        alert('Quotation completed successfully! Project has been created.');
        window.location.href = 'admin-projects.php';
      </script>";
      exit;
    } catch (Exception $e) {
      // Rollback on error
      mysqli_rollback($conn);
      $errors['general'] = 'Failed to complete quotation: ' . $e->getMessage();
      error_log("Quotation completion error: " . $e->getMessage());
    }
  }
}

// Calculate totals
$items_total = 0;
$labor_total = 0;

foreach ($quotation_items as $item) {
  $items_total += $item['total'];
}

foreach ($labor_charges as $labor) {
  $labor_total += $labor['amount'];
}

$grand_total = $items_total + $labor_total;

// Format data
$user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
$location = trim($assessment['city'] . ', ' . $assessment['province']);
$formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Quotation Proposal - Admin Dashboard</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />
  <style>
    .sidebar-content-item:nth-child(4) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(4) .sidebar-anchor,
    .sidebar-content-item:nth-child(4) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>

  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4">
    <!-- BACK BUTTON -->
    <a href="admin-assessments.php" class="btn btn-outline-secondary mb-2" style="margin-top: 42px;">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="pb-3 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Quotation Proposal</h1>
        <p class="admin-top-desc">Handle customer assessment requests and generate quotation proposals.</p>
      </div>
      <?php if ($quotation && $quotation['status'] === 'Sent'): ?>
        <div>
          <span class="badge-pill taskstatus-completed d-flex align-items-center justify-content-center"
            style="height: 40px; font-size: 18px;">
            <i class="fas fa-check-circle me-1"></i> Quotation Sent
          </span>
        </div>
      <?php endif; ?>
    </div>

    <!-- General Error Message -->
    <?php if (isset($errors['general'])): ?>
      <script>
        alert('<?= addslashes($errors['general']) ?>');
      </script>
    <?php endif; ?>

    <div class="row g-3 mb-4">

      <div class="col-12">

        <!-- PROJECT DETAILS FORM -->
        <div class="project-details rounded-3 bg-white mb-3">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4">
              <div class="w-100">
                <div class="assessment-top">
                  <h2 class="fs-24 mb-0">Project Details</h2>
                  <p class="light-text fs-14">Set the project parameters</p>
                </div>

                <form method="POST" id="projectDetailsForm">
                  <input type="hidden" name="save_project_details" value="1">

                  <div class="row mt-3">
                    <div class="col-md-4">
                      <label for="projectName" class="form-label">Project Name</label>
                      <input type="text" class="form-control"
                        id="projectName" name="project_name"
                        value="<?= htmlspecialchars($form_data['project_name']) ?>"
                        placeholder="CCTV Installation for Office"
                        <?= $is_view_only ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-4">
                      <label for="category" class="form-label">Category</label>
                      <input type="text" class="form-control"
                        id="category" name="category"
                        value="<?= htmlspecialchars($form_data['category']) ?>"
                        readonly>
                    </div>

                    <div class="col-md-4">
                      <label for="clientBudget" class="form-label">Client's Estimated Budget:</label>
                      <input id="clientBudget" type="text" class="form-control"
                        value="₱<?= number_format($assessment['estimated_budget'] ?? 0, 2) ?>"
                        readonly>
                      <small class="text-muted">Budget provided by client</small>
                    </div>
                  </div>

                  <div class="row mt-3">
                    <div class="col-md-6">
                      <label for="startDate" class="form-label">Start Date</label>
                      <input id="startDate" type="date" name="start_date" class="form-control"
                        value="<?= htmlspecialchars($form_data['start_date'] ?? '') ?>"
                        <?= $is_view_only ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-6">
                      <label for="endDate" class="form-label">End Date</label>
                      <input id="endDate" type="date" name="end_date" class="form-control"
                        value="<?= htmlspecialchars($form_data['end_date'] ?? '') ?>"
                        <?= $is_view_only ? 'readonly' : '' ?>>
                    </div>
                  </div>

                  <div class="my-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                      placeholder="Add any additional project details here..."
                      <?= $is_view_only ? 'readonly' : '' ?>><?= htmlspecialchars($form_data['notes']) ?></textarea>
                  </div>

                  <?php if (!$is_view_only): ?>
                    <div class="d-flex justify-content-end gap-2">
                      <button type="submit" class="btn btn-green">
                        <i class="fas fa-save me-1"></i> Save Project Details
                      </button>
                    </div>
                  <?php endif; ?>
                </form>

              </div>
            </div>

          </div>
        </div>

        <!-- QUOTATION ITEMS -->
        <div class="added-items rounded-3 bg-white mb-3">

          <div class="p-4 d-flex flex-column gap-4">

            <div class="assessment-con border p-3 rounded-3 gap-4">
              <!-- QUOTATION TOP -->
              <div class="w-100">
                <div class="assessment-top d-flex justify-content-between align-items-center mb-4">
                  <div>
                    <h2 class="fs-24 mobile-fs-22 mb-0">Quotation Items</h2>
                    <p class="light-text fs-14">Review the quotation items</p>
                  </div>

                  <?php if ($is_admin): ?>
                    <div class="d-flex flex-column flex-md-row gap-2">
                      <button class="btn btn-green d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus d-none d-md-block me-1"></i>
                        Add Item
                      </button>

                      <button class="btn btn-green d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addLaborModal">
                        <i class="fas fa-plus d-none d-md-block me-1"></i>
                        Add Labor
                      </button>
                    </div>
                  <?php endif; ?>

                </div>
              </div>

              <!-- QUOTATION ITEMS LIST -->
              <div class="quotation-items-list">
                <?php if (count($quotation_items) > 0): ?>
                  <?php foreach ($quotation_items as $item): ?>
                    <div class="row quotation-row mt-3 border rounded-3 p-3 mx-0 align-items-center">
                      <div class="col-md-4">
                        <span class="fs-14 light-text">Item Name: </span>
                        <p class="mb-2"><?= htmlspecialchars($item['item_name']) ?></p>
                      </div>
                      <div class="col-md-2">
                        <span class="fs-14 light-text">Quantity: </span>
                        <p class="mb-2"><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_type']) ?></p>
                      </div>
                      <div class="col-md-2">
                        <span class="fs-14 light-text">Unit Price: </span>
                        <p class="mb-2">₱<?= number_format($item['unit_price'], 2) ?></p>
                      </div>
                      <div class="col-md-2">
                        <span class="fs-14 light-text">Total: </span>
                        <p class="mb-2">₱<?= number_format($item['total'], 2) ?></p>
                      </div>
                      <?php if ($is_admin): ?>
                        <div class="col-md-1">
                          <a href="#" class="text-secondary" data-bs-toggle="modal" data-bs-target="#editItemModal"
                            onclick="populateEditModal(<?= $item['item_id'] ?>, '<?= addslashes($item['item_name']) ?>', <?= $item['quantity'] ?>, <?= $item['unit_price'] ?>)">
                            <i class="fas fa-edit"></i>
                          </a>
                        </div>
                        <div class="col-md-1">
                          <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal"
                            onclick="setDeleteItemId(<?= $item['item_id'] ?>, '<?= addslashes($item['item_name']) ?>')">
                            <i class="fas fa-trash"></i>
                          </a>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No items added yet. Click "Add Item" to get started.</p>
                  </div>
                <?php endif; ?>

                <!-- LABOR LIST -->
                <?php if (count($labor_charges) > 0): ?>
                  <div class="mt-4">
                    <h5 class="mb-3">Labor & Additional Charges</h5>
                    <?php foreach ($labor_charges as $labor): ?>
                      <div class="row quotation-row mt-3 border rounded-3 p-3 mx-0 align-items-center">
                        <div class="col-md-8">
                          <span class="fs-14 light-text">Labor & Services: </span>
                          <p class="mb-2"><?= htmlspecialchars($labor['description']) ?></p>
                        </div>
                        <div class="col-md-2">
                          <span class="fs-14 light-text">Amount: </span>
                          <p class="mb-2">₱<?= number_format($labor['amount'], 2) ?></p>
                        </div>
                        <?php if ($is_admin): ?>
                          <div class="col-md-1">
                            <a href="#" class="text-secondary"><i class="fas fa-edit"></i></a>
                          </div>
                          <div class="col-md-1">
                            <a href="?id=<?= $assessment_id ?>&delete_labor=<?= $labor['labor_id'] ?>" class="text-danger"
                              onclick="return confirm('Are you sure you want to delete this labor charge?')">
                              <i class="fas fa-trash"></i>
                            </a>
                          </div>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="divider my-4"></div>

              <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                  <p class="fs-14 light-text mb-2">Items Total: <strong>₱<?= number_format($items_total, 2) ?></strong></p>
                  <p class="fs-14 light-text mb-0">Labor Total: <strong>₱<?= number_format($labor_total, 2) ?></strong></p>
                </div>
                <div class="text-end">
                  <p class="fs-18 fw-semibold mb-0">Total Quotation Amount:</p>
                  <p class="fs-28 green-text fw-bold mb-0">₱<?= number_format($grand_total, 2) ?></p>
                </div>
              </div>

              <?php if ($is_admin): ?>
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <a href="admin-assessments.php" class="btn btn-outline-secondary">Cancel</a>
                  <form method="POST" style="margin: 0;">
                    <input type="hidden" name="complete_quotation" value="1">
                    <button type="submit" class="btn btn-success" <?= ($quotation && $quotation['status'] === 'Sent') ? 'disabled' : '' ?>>
                      <i class="fas fa-check-circle me-1"></i>
                      <?= ($quotation && $quotation['status'] === 'Sent') ? 'Quotation Sent' : 'Complete & Send Quotation' ?>
                    </button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- ADD ITEM MODAL -->
  <div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="add_item" value="1">
          <div class="modal-body">
            <div class="row g-3">
              <!-- Select from Inventory -->
              <div class="col-12">
                <label class="form-label">Select from Inventory (Optional)</label>
                <select id="inventorySelect" class="form-select">
                  <option value="">-- Or add new item --</option>
                  <?php foreach ($inventory_items as $inv_item): ?>
                    <option value="<?= $inv_item['item_id'] ?>"
                      data-price="<?= $inv_item['selling_price'] ?>"
                      data-name="<?= htmlspecialchars($inv_item['item_name']) ?>"
                      data-quantity="<?= $inv_item['quantity'] ?>"
                      data-unit="<?= htmlspecialchars($inv_item['quantity_unit']) ?>">
                      <?= htmlspecialchars($inv_item['item_name']) ?> - ₱<?= number_format($inv_item['selling_price'], 2) ?> (<?= $inv_item['status'] ?> - Stock: <?= $inv_item['quantity'] ?> <?= htmlspecialchars($inv_item['quantity_unit']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Item Name -->
              <div class="col-md-6">
                <label class="form-label">Item Name *</label>
                <input type="text" class="form-control <?= isset($errors['item_name']) ? 'is-invalid' : '' ?>"
                  id="itemName" name="item_name" placeholder="4mp CCTV Camera" required>
                <input type="hidden" name="item_id" id="itemId" value="">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['item_name']) ? 'block' : 'none' ?>">
                  <?= $errors['item_name'] ?? 'This field is required' ?>
                </p>
              </div>

              <!-- Quantity -->
              <div class="col-md-3">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                  id="itemQuantity" name="quantity" placeholder="1" min="0.01" step="0.01" required>
                <small id="quantityWarning" class="d-block text-warning mt-1" style="display: none;"></small>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['quantity']) ? 'block' : 'none' ?>">
                  <?= $errors['quantity'] ?? 'Must be greater than 0' ?>
                </p>
              </div>

              <!-- Unit Type -->
              <div class="col-md-3">
                <label class="form-label">Unit Type *</label>
                <select class="form-select <?= isset($errors['unit_type']) ? 'is-invalid' : '' ?>"
                  id="unitType" name="unit_type" required>
                  <option value="">Select unit</option>
                  <option value="piece">Piece</option>
                  <option value="roll">Roll</option>
                  <option value="unit">Unit</option>
                  <option value="box">Box</option>
                  <option value="pack">Pack</option>
                  <option value="set">Set</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['unit_type']) ? 'block' : 'none' ?>">
                  <?= $errors['unit_type'] ?? 'This field is required' ?>
                </p>
              </div>

              <!-- Cost Price (Only for new items) -->
              <div class="col-md-6" id="costPriceField">
                <label class="form-label">Cost Price (₱) *</label>
                <input type="number" class="form-control <?= isset($errors['item_cost']) ? 'is-invalid' : '' ?>"
                  id="itemCost" name="item_cost" placeholder="0.00" min="0" step="0.01">
                <small class="text-muted">Your cost for this item</small>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['item_cost']) ? 'block' : 'none' ?>">
                  <?= $errors['item_cost'] ?? 'Must be greater than 0' ?>
                </p>
              </div>

              <!-- Selling Price (Only for new items) -->
              <div class="col-md-6" id="sellingPriceField">
                <label class="form-label">Selling Price (₱) *</label>
                <input type="number" class="form-control <?= isset($errors['item_selling_price']) ? 'is-invalid' : '' ?>"
                  id="itemSellingPrice" name="item_selling_price" placeholder="0.00" min="0" step="0.01">
                <small class="text-muted">Selling price in inventory</small>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['item_selling_price']) ? 'block' : 'none' ?>">
                  <?= $errors['item_selling_price'] ?? 'Must be greater than 0' ?>
                </p>
              </div>

              <!-- Unit Price (Only for inventory items) -->
              <div class="col-md-12" id="unitPriceField" style="display: none;">
                <label class="form-label">Unit Price (₱) *</label>
                <input type="number" class="form-control <?= isset($errors['unit_price']) ? 'is-invalid' : '' ?>"
                  id="unitPrice" name="unit_price" placeholder="0.00" min="0" step="0.01">
                <small class="text-muted">Price for this quotation</small>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['unit_price']) ? 'block' : 'none' ?>">
                  <?= $errors['unit_price'] ?? 'Must be greater than 0' ?>
                </p>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green">Add Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- ADD LABOR MODAL -->
  <div class="modal fade" id="addLaborModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Labor / Additional Charges</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="add_labor" value="1">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Labor / Service Description *</label>
                <input type="text" class="form-control <?= isset($errors['labor_description']) ? 'is-invalid' : '' ?>"
                  name="labor_description" placeholder="Labor, Maintenance, Installation Fee" required>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['labor_description']) ? 'block' : 'none' ?>">
                  <?= $errors['labor_description'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Amount (₱) *</label>
                <input type="number" class="form-control <?= isset($errors['labor_amount']) ? 'is-invalid' : '' ?>"
                  name="labor_amount" placeholder="0.00" min="0" step="0.01" required>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['labor_amount']) ? 'block' : 'none' ?>">
                  <?= $errors['labor_amount'] ?? 'Must be greater than 0' ?>
                </p>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green">Add Labor</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- DELETE ITEM MODAL -->
  <div class="modal fade" id="deleteItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="deleteItemLabel">Delete Quotation Item</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="delete_item_id" id="deleteItemId">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this item?</h3>
            <p class="text-center text-muted mb-2"><strong id="deleteItemName"></strong></p>
            <p class="text-center text-muted">This will restore inventory quantity or archive new items.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="modal-delete-button" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- EDIT ITEM MODAL -->
  <div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="edit_item" value="1">
          <input type="hidden" name="edit_item_id" id="editItemId">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" id="editItemName" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label">Quantity *</label>
                <input type="number" class="form-control" id="editQuantity" name="edit_quantity"
                  min="0.01" step="0.01" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Selling Price (₱) *</label>
                <input type="number" class="form-control" id="editUnitPrice" name="edit_unit_price"
                  min="0" step="0.01" required>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green">Update Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Function to set delete item ID and name in modal
    function setDeleteItemId(itemId, itemName) {
      document.getElementById('deleteItemId').value = itemId;
      document.getElementById('deleteItemName').textContent = itemName;
    }

    // Function to populate edit modal with item data
    function populateEditModal(itemId, itemName, quantity, unitPrice) {
      document.getElementById('editItemId').value = itemId;
      document.getElementById('editItemName').value = itemName;
      document.getElementById('editQuantity').value = quantity;
      document.getElementById('editUnitPrice').value = unitPrice;
    }

    // Populate item details when selecting from inventory
    document.getElementById('inventorySelect').addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];

      if (selectedOption.value) {
        // Existing inventory item selected
        const itemName = selectedOption.getAttribute('data-name');
        const itemPrice = selectedOption.getAttribute('data-price');
        const stockQuantity = parseFloat(selectedOption.getAttribute('data-quantity'));
        const unitType = selectedOption.getAttribute('data-unit');

        document.getElementById('itemName').value = itemName;
        document.getElementById('itemId').value = selectedOption.value;
        document.getElementById('unitPrice').value = itemPrice;
        document.getElementById('unitType').value = unitType;
        document.getElementById('itemQuantity').value = '';

        // Show ONLY unit price field for inventory items
        document.getElementById('unitPriceField').style.display = 'block';
        document.getElementById('costPriceField').style.display = 'none';
        document.getElementById('sellingPriceField').style.display = 'none';

        // Clear cost and selling price (not needed for inventory items)
        document.getElementById('itemCost').value = '';
        document.getElementById('itemSellingPrice').value = '';
        document.getElementById('itemCost').removeAttribute('required');
        document.getElementById('itemSellingPrice').removeAttribute('required');
        document.getElementById('unitPrice').setAttribute('required', 'required');

        // Show available stock info
        const quantityWarning = document.getElementById('quantityWarning');
        quantityWarning.textContent = `Available in stock: ${stockQuantity} ${unitType}`;
        quantityWarning.style.display = 'block';
        quantityWarning.className = 'd-block text-success mt-1';

        // Store max quantity for validation
        document.getElementById('itemQuantity').setAttribute('data-max-quantity', stockQuantity);

      } else {
        // New item - show all fields
        document.getElementById('itemName').value = '';
        document.getElementById('itemId').value = '';
        document.getElementById('unitType').value = '';
        document.getElementById('itemQuantity').value = '';
        document.getElementById('unitPrice').value = '';
        document.getElementById('itemCost').value = '';
        document.getElementById('itemSellingPrice').value = '';
        document.getElementById('itemQuantity').removeAttribute('data-max-quantity');
        document.getElementById('quantityWarning').style.display = 'none';

        // Show cost and selling price fields, hide unit price field
        document.getElementById('unitPriceField').style.display = 'none';
        document.getElementById('costPriceField').style.display = 'block';
        document.getElementById('sellingPriceField').style.display = 'block';

        // Set required attributes appropriately
        document.getElementById('itemCost').setAttribute('required', 'required');
        document.getElementById('itemSellingPrice').setAttribute('required', 'required');
        document.getElementById('unitPrice').removeAttribute('required');
      }
    });

    // Validate quantity against available stock
    document.getElementById('itemQuantity').addEventListener('input', function() {
      const maxQuantity = parseFloat(this.getAttribute('data-max-quantity'));
      const enteredQuantity = parseFloat(this.value);
      const quantityWarning = document.getElementById('quantityWarning');

      if (maxQuantity && !isNaN(enteredQuantity) && enteredQuantity > 0) {
        if (enteredQuantity > maxQuantity) {
          quantityWarning.textContent = `⚠️ Warning: You're requesting ${enteredQuantity} but only ${maxQuantity} is available in stock.`;
          quantityWarning.className = 'd-block text-danger mt-1';
        } else {
          const remaining = maxQuantity - enteredQuantity;
          quantityWarning.textContent = `${remaining} units will remain in stock after this order.`;
          quantityWarning.className = 'd-block green-text mt-1';
        }
        quantityWarning.style.display = 'block';
      }
    });

    // Allow manual entry to override inventory selection
    document.getElementById('itemName').addEventListener('input', function() {
      const selectedOption = document.getElementById('inventorySelect').options[document.getElementById('inventorySelect').selectedIndex];
      const selectedName = selectedOption.getAttribute('data-name');

      if (this.value !== selectedName && this.value.trim() !== '') {
        document.getElementById('itemId').value = '';
        document.getElementById('itemQuantity').removeAttribute('data-max-quantity');
        document.getElementById('quantityWarning').style.display = 'none';

        // Show cost and selling price fields, hide unit price field for new items
        document.getElementById('unitPriceField').style.display = 'none';
        document.getElementById('costPriceField').style.display = 'block';
        document.getElementById('sellingPriceField').style.display = 'block';

        // Set required attributes appropriately
        document.getElementById('itemCost').setAttribute('required', 'required');
        document.getElementById('itemSellingPrice').setAttribute('required', 'required');
        document.getElementById('unitPrice').removeAttribute('required');
        document.getElementById('unitPrice').value = '';
      }
    });

    // Reset modal form when opened
    const addItemModal = document.getElementById('addItemModal');
    if (addItemModal) {
      addItemModal.addEventListener('show.bs.modal', function() {
        // Reset form fields
        document.getElementById('inventorySelect').value = '';
        document.getElementById('itemName').value = '';
        document.getElementById('itemId').value = '';
        document.getElementById('itemQuantity').value = '';
        document.getElementById('unitType').value = '';
        document.getElementById('unitPrice').value = '';
        document.getElementById('itemCost').value = '';
        document.getElementById('itemSellingPrice').value = '';

        // Show cost and selling price fields by default (for new items)
        document.getElementById('unitPriceField').style.display = 'none';
        document.getElementById('costPriceField').style.display = 'block';
        document.getElementById('sellingPriceField').style.display = 'block';

        // Set required attributes for new item fields
        document.getElementById('itemCost').setAttribute('required', 'required');
        document.getElementById('itemSellingPrice').setAttribute('required', 'required');
        document.getElementById('unitPrice').removeAttribute('required');

        document.getElementById('quantityWarning').style.display = 'none';
      });
    }
  </script>

</body>

</html>