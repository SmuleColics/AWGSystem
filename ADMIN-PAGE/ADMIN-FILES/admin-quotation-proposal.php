<?php

date_default_timezone_set('Asia/Manila');
include 'admin-header.php';

// Check if user is logged in as employee
if (!isset($_SESSION['employee_id']) || $_SESSION['user_type'] !== 'employee') {
  header('Location: /INSY55-PROJECT/LOGS-PAGE/LOGS-FILES/login.php');
  exit;
}

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
        WHERE a.assessment_id = $assessment_id AND a.status = 'Accepted'";

$result = mysqli_query($conn, $sql);
$assessment = mysqli_fetch_assoc($result);

if (!$assessment) {
  header('Location: admin-assessments.php');
  exit;
}

// Fetch existing quotation if it exists
$quotation_sql = "SELECT * FROM quotations WHERE assessment_id = $assessment_id";
$quotation_result = mysqli_query($conn, $quotation_sql);
$quotation = mysqli_fetch_assoc($quotation_result);

// Fetch quotation items
$items_sql = "SELECT * FROM quotation_items WHERE assessment_id = $assessment_id ORDER BY created_at ASC";
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

// Handle delete item
if (isset($_GET['delete_item'])) {
  $item_id = intval($_GET['delete_item']);
  $delete_sql = "DELETE FROM quotation_items WHERE item_id = $item_id AND assessment_id = $assessment_id";
  if (mysqli_query($conn, $delete_sql)) {
    // Activity Log
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

// Handle add item to quotation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
  $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== '' ? intval($_POST['item_id']) : null;
  $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
  $quantity = floatval($_POST['quantity']);
  $unit_type = mysqli_real_escape_string($conn, trim($_POST['unit_type']));

  // For existing inventory items: use unit_price
  // For new items: use selling_price as unit_price in quotation
  if ($item_id !== null) {
    $unit_price = floatval($_POST['unit_price']);
  } else {
    $unit_price = floatval($_POST['item_selling_price']);
  }

  $item_cost = isset($_POST['item_cost']) ? floatval($_POST['item_cost']) : $unit_price;
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
  if ($item_id === null && $item_cost <= 0) {
    $errors['item_cost'] = 'Cost price must be greater than 0';
  }
  if ($item_id === null && $item_selling_price <= 0) {
    $errors['item_selling_price'] = 'Selling price must be greater than 0';
  }
  if ($item_id !== null && $unit_price <= 0) {
    $errors['unit_price'] = 'Unit price must be greater than 0';
  }

  if (empty($errors)) {
    // If item exists in inventory, reduce its quantity
    if ($item_id !== null) {
      // Get current inventory quantity
      $inv_check_sql = "SELECT quantity FROM inventory_items WHERE item_id = $item_id";
      $inv_check_result = mysqli_query($conn, $inv_check_sql);
      $inv_check = mysqli_fetch_assoc($inv_check_result);

      if ($inv_check) {
        $new_quantity = $inv_check['quantity'] - $quantity;

        // Update inventory quantity
        $update_inv_sql = "UPDATE inventory_items SET quantity = $new_quantity WHERE item_id = $item_id";
        mysqli_query($conn, $update_inv_sql);
      }
    } else {
      // If no item_id selected, add new item to inventory as "Out of Stock"
      $service_type = mysqli_real_escape_string($conn, $assessment['service_type']);
      $insert_inventory_sql = "INSERT INTO inventory_items (item_name, category, quantity, quantity_unit, price, selling_price, status, location, supplier, created_at)
                              VALUES ('$item_name', '$service_type', 0, '$unit_type', '$item_cost', '$item_selling_price', 'Out of Stock', '', '', NOW())";

      if (mysqli_query($conn, $insert_inventory_sql)) {
        $item_id = mysqli_insert_id($conn);
      }
    }

    // Add item to quotation items table
    $insert_sql = "INSERT INTO quotation_items (assessment_id, item_id, item_name, quantity, unit_type, unit_price, total, created_at)
                  VALUES ($assessment_id, $item_id, '$item_name', $quantity, '$unit_type', $unit_price, $total, NOW())";

    if (mysqli_query($conn, $insert_sql)) {
      // Activity Log
      if ($_POST['item_id'] && $_POST['item_id'] !== '') {
        // Item from inventory
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'QUOTATION_ITEMS',
          $item_id,
          $item_name,
          "Added quotation item from inventory: $item_name (Qty: $quantity, Unit Price: ₱$unit_price) to assessment #$assessment_id"
        );
      } else {
        // New item created
        log_activity(
          $conn,
          $employee_id,
          $employee_name,
          'CREATE',
          'QUOTATION_ITEMS',
          $item_id,
          $item_name,
          "Added new quotation item: $item_name (Qty: $quantity, Cost: ₱$item_cost, Selling Price: ₱$item_selling_price) to assessment #$assessment_id. Item created in inventory as 'Out of Stock'"
        );
      }

      echo "<script>
        alert('Item added successfully and inventory updated');
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
          "Updated project details for assessment #$assessment_id - Project: $project_name, Cost: ₱$estimated_cost"
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
          "Created new quotation for assessment #$assessment_id - Project: $project_name, Cost: ₱$estimated_cost"
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

    // Update quotation status and total amount
    $complete_sql = "UPDATE quotations 
                    SET status = 'Sent',
                        total_amount = $grand_total,
                        updated_at = NOW()
                    WHERE assessment_id = $assessment_id";

    if (mysqli_query($conn, $complete_sql)) {
      // Activity Log
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'COMPLETE',
        'QUOTATIONS',
        $assessment_id,
        'Quotation Completed',
        "Quotation for assessment #$assessment_id completed and sent. Total Amount: ₱$grand_total"
      );

      // Create Notification for CLIENT
      $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
      $project_name = $quotation['project_name'];
      $user_full_name_escaped = mysqli_real_escape_string($conn, $user_full_name);
      $project_name_escaped = mysqli_real_escape_string($conn, $project_name);
      $client_notification_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read)
                                 VALUES ({$assessment['user_id']}, 'QUOTATION_CREATED', 'Your Quotation is Ready', 'Your quotation for $project_name_escaped is now ready for review. Total Amount: ₱$grand_total', 'user-assessments.php', 0)";
      mysqli_query($conn, $client_notification_sql);

      echo "<script>
        alert('Quotation completed and sent successfully!');
        window.location.href = 'admin-quotation-proposal.php?id=$assessment_id';
      </script>";
      exit;
    } else {
      $errors['general'] = 'Failed to complete quotation: ' . mysqli_error($conn);
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
                      <input type="text" class="form-control <?= isset($errors['project_name']) ? 'is-invalid' : '' ?>"
                        id="projectName" name="project_name"
                        value="<?= htmlspecialchars($form_data['project_name']) ?>"
                        placeholder="CCTV Installation for Office">
                      <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['project_name']) ? 'block' : 'none' ?>">
                        <?= $errors['project_name'] ?? 'This field is required' ?>
                      </p>
                    </div>

                    <div class="col-md-4">
                      <label for="category" class="form-label">Category</label>
                      <input type="text" class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>"
                        id="category" name="category"
                        value="<?= htmlspecialchars($form_data['category']) ?>"
                        readonly>
                      <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['category']) ? 'block' : 'none' ?>">
                        <?= $errors['category'] ?? 'This field is required' ?>
                      </p>
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
                      <input id="startDate" type="date" name="start_date" class="form-control <?= isset($errors['date_range']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($form_data['start_date'] ?? '') ?>">
                      <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['date_range']) ? 'block' : 'none' ?>">
                        <?= $errors['date_range'] ?? 'Start date must be before end date' ?>
                      </p>
                    </div>

                    <div class="col-md-6">
                      <label for="endDate" class="form-label">End Date</label>
                      <input id="endDate" type="date" name="end_date" class="form-control <?= isset($errors['date_range']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($form_data['end_date'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="my-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                      placeholder="Add any additional project details here..."><?= htmlspecialchars($form_data['notes']) ?></textarea>
                  </div>

                  <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-green">
                      <i class="fas fa-save me-1"></i> Save Project Details
                    </button>
                  </div>
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
                      <div class="col-md-1">
                        <a href="#" class="text-secondary"><i class="fas fa-edit"></i></a>
                      </div>
                      <div class="col-md-1">
                        <a href="?id=<?= $assessment_id ?>&delete_item=<?= $item['item_id'] ?>" class="text-danger"
                          onclick="return confirm('Are you sure you want to delete this item? This will restore the inventory quantity.')">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
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

  <script>
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
        quantityWarning.className = 'd-block text-info mt-1';
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