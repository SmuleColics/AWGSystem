<?php
ob_start();
include 'admin-header.php'; // Includes authentication and sets $is_admin variable

$errors = [];
$success = false;

// ========== GENERATE WEEKLY REPORT ========== //
if (isset($_GET['report']) && $_GET['report'] === 'weekly' && $is_admin) {
  $report_type = 'weekly';
  $date_from = date('Y-m-d', strtotime('-7 days'));
  $date_to = date('Y-m-d');

  // Get inventory items
  $sql = "SELECT * FROM inventory_items WHERE is_archived = 0 ORDER BY category, item_name";
  $result = mysqli_query($conn, $sql);
  $report_items = [];
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $report_items[] = $row;
    }
  }

  // Get activity logs for the period
  $log_sql = "SELECT * FROM activity_logs 
              WHERE module = 'INVENTORY' 
              AND created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
              ORDER BY created_at DESC";
  $log_result = mysqli_query($conn, $log_sql);
  $activity_logs = [];
  if ($log_result) {
    while ($row = mysqli_fetch_assoc($log_result)) {
      $activity_logs[] = $row;
    }
  }

  log_activity(
    $conn,
    $employee_id,
    $employee_full_name,
    'GENERATE REPORT',
    'INVENTORY',
    NULL,
    'Weekly Report',
    "Generated weekly inventory report from $date_from to $date_to" // details
  );

  include 'inventory-report-template.php';
  exit;
}

// ========== GENERATE MONTHLY REPORT ========== //
if (isset($_GET['report']) && $_GET['report'] === 'monthly' && $is_admin) {
  $report_type = 'monthly';
  $date_from = date('Y-m-01'); // First day of current month
  $date_to = date('Y-m-d'); // Today

  // Get inventory items
  $sql = "SELECT * FROM inventory_items WHERE is_archived = 0 ORDER BY category, item_name";
  $result = mysqli_query($conn, $sql);
  $report_items = [];
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $report_items[] = $row;
    }
  }

  log_activity(
    $conn,
    $employee_id,
    $employee_full_name,
    'GENERATE REPORT',
    'INVENTORY',
    NULL,
    'Monthly Report',
    "Generated monthly inventory report from $date_from to $date_to"
  );


  // Get activity logs for the period
  $log_sql = "SELECT * FROM activity_logs 
              WHERE module = 'INVENTORY' 
              AND created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
              ORDER BY created_at DESC";
  $log_result = mysqli_query($conn, $log_sql);
  $activity_logs = [];
  if ($log_result) {
    while ($row = mysqli_fetch_assoc($log_result)) {
      $activity_logs[] = $row;
    }
  }

  include 'inventory-report-template.php';
  exit;
}

// ========== ONLY ADMINS CAN ADD ITEMS ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {

  // Check if user is admin
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to add items.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $item_name = trim($_POST['item_name'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $quantity = trim($_POST['quantity'] ?? '');
  $quantity_unit = trim($_POST['quantity_unit'] ?? 'piece');
  $price = trim($_POST['price'] ?? '');
  $selling_price = trim($_POST['selling_price'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $supplier = trim($_POST['supplier'] ?? '');

  if (empty($item_name)) {
    $errors['item_name'] = 'Item name is required';
  }

  if (empty($category)) {
    $errors['category'] = 'Category is required';
  }

  if (!is_numeric($quantity)) {
    $errors['quantity'] = 'Valid quantity is required';
  }

  if (empty($price) || !is_numeric($price) || $price < 0) {
    $errors['price'] = 'Valid price is required';
  }

  if (empty($selling_price) || !is_numeric($selling_price) || $selling_price < 0) {
    $errors['selling_price'] = 'Valid unit price is required';
  }

  if (empty($location)) {
    $errors['location'] = 'Location is required';
  }

  if (empty($supplier)) {
    $errors['supplier'] = 'Supplier is required';
  }

  if (empty($errors)) {
    $item_name_esc = mysqli_real_escape_string($conn, $item_name);
    $category_esc = mysqli_real_escape_string($conn, $category);
    $quantity_unit_esc = mysqli_real_escape_string($conn, $quantity_unit);
    $location_esc = mysqli_real_escape_string($conn, $location);
    $supplier_esc = mysqli_real_escape_string($conn, $supplier);

    // Auto-determine status based on quantity
    if ($quantity <= 0) {
      $status = 'Out of Stock';
    } elseif ($quantity <= 10) {
      $status = 'Low Stock';
    } else {
      $status = 'In Stock';
    }

    $status_esc = mysqli_real_escape_string($conn, $status);

    $sql = "INSERT INTO inventory_items (item_name, category, quantity, quantity_unit, price, selling_price, status, location, supplier, is_archived) 
            VALUES ('$item_name_esc', '$category_esc', $quantity, '$quantity_unit_esc', $price, $selling_price, '$status_esc', '$location_esc', '$supplier_esc', 0)";

    if (mysqli_query($conn, $sql)) {
      $new_item_id = mysqli_insert_id($conn);

      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'CREATE',
        'INVENTORY',
        $new_item_id,
        $item_name,
        "Created new inventory item: $item_name ($category) - Qty: $quantity $quantity_unit, Status: $status"
      );

      echo "<script>
        alert('Item added successfully!');
        window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
      </script>";
      exit;
    } else {
      $errors['database'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

// ========== ONLY ADMINS CAN EDIT ITEMS ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {

  // Check if user is admin
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to edit items.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $item_id = intval($_POST['item_id']);
  $item_name = trim($_POST['item_name'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $quantity = trim($_POST['quantity'] ?? '');
  $quantity_unit = trim($_POST['quantity_unit'] ?? 'piece');
  $price = trim($_POST['price'] ?? '');
  $selling_price = trim($_POST['selling_price'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $supplier = trim($_POST['supplier'] ?? '');

  // Validation
  if (empty($item_name)) {
    $errors['item_name'] = 'Item name is required';
  }

  if (empty($category)) {
    $errors['category'] = 'Category is required';
  }

  if (!is_numeric($quantity)) {
    $errors['quantity'] = 'Valid quantity is required';
  }

  if (empty($price) || !is_numeric($price) || $price < 0) {
    $errors['price'] = 'Valid price is required';
  }

  if (empty($selling_price) || !is_numeric($selling_price) || $selling_price < 0) {
    $errors['selling_price'] = 'Valid unit price is required';
  }

  if (empty($location)) {
    $errors['location'] = 'Location is required';
  }

  if (empty($supplier)) {
    $errors['supplier'] = 'Supplier is required';
  }

  if (empty($errors)) {
    $item_name_esc = mysqli_real_escape_string($conn, $item_name);
    $category_esc = mysqli_real_escape_string($conn, $category);
    $quantity_unit_esc = mysqli_real_escape_string($conn, $quantity_unit);
    $location_esc = mysqli_real_escape_string($conn, $location);
    $supplier_esc = mysqli_real_escape_string($conn, $supplier);

    // Auto-determine status based on quantity
    if ($quantity <= 0) {
      $status = 'Out of Stock';
    } elseif ($quantity <= 10) {
      $status = 'Low Stock';
    } else {
      $status = 'In Stock';
    }

    $status_esc = mysqli_real_escape_string($conn, $status);

    $sql = "UPDATE inventory_items SET 
            item_name = '$item_name_esc',
            category = '$category_esc',
            quantity = $quantity,
            quantity_unit = '$quantity_unit_esc',
            price = $price,
            selling_price = $selling_price,
            status = '$status_esc',
            location = '$location_esc',
            supplier = '$supplier_esc'
        WHERE item_id = $item_id";

    if (mysqli_query($conn, $sql)) {

      // UPDATE LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_full_name,
        'UPDATE',
        'INVENTORY',
        $item_id,
        $item_name,
        "Updated inventory item: $item_name - Status: $status, Qty: $quantity $quantity_unit"
      );

      echo "<script>
        alert('Item updated successfully!');
        window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
      </script>";
      exit;
    }
  }
}

// ========== ONLY ADMINS CAN ARCHIVE ITEMS ========== //
if (isset($_POST['modal-archive-button'])) {

  // Check if user is admin
  if (!$is_admin) {
    echo "<script>alert('You do not have permission to archive items.'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  }

  $archive_id = (int)$_POST['archive_id'];

  // Get item name before archiving
  $get_name_sql = "SELECT item_name FROM inventory_items WHERE item_id = $archive_id";
  $result = mysqli_query($conn, $get_name_sql);
  $item_data = mysqli_fetch_assoc($result);
  $item_name = $item_data['item_name'] ?? 'Unknown Item';

  // Archive the item by setting is_archived = 1
  $sql = "UPDATE inventory_items SET is_archived = 1 WHERE item_id = $archive_id";
  if (mysqli_query($conn, $sql)) {

    // LOG ACTIVITY
    log_activity(
      $conn,
      $employee_id,
      $employee_full_name,
      'ARCHIVE',
      'INVENTORY',
      $archive_id,
      $item_name,
      "Archived inventory item: $item_name"
    );

    echo "<script>alert('Item archived successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error archiving item: " . mysqli_error($conn) . "');</script>";
  }
}

// ========== GET STATISTICS ========== //
$stats = [
  'total_items' => 0,
  'low_stock' => 0,
  'out_of_stock' => 0,
  'in_stock' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_items'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'Low Stock' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['low_stock'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'Out of Stock' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['out_of_stock'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'In Stock' AND is_archived = 0");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['in_stock'] = $row['count'];
}

// ========== GET ALL ITEMS ========== //
$items = [];
$sql = "SELECT * FROM inventory_items WHERE is_archived = 0 ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
  }
}

// ========== AUTO-UPDATE STATUS BASED ON QUANTITY ========== //
function updateItemStatus($conn, $item_id)
{
  // Get current quantity
  $sql = "SELECT quantity FROM inventory_items WHERE item_id = $item_id";
  $result = mysqli_query($conn, $sql);

  if ($result && $row = mysqli_fetch_assoc($result)) {
    $quantity = $row['quantity'];
    $new_status = '';

    if ($quantity <= 0) {
      $new_status = 'Out of Stock';
    } elseif ($quantity <= 10) {
      $new_status = 'Low Stock';
    } else {
      $new_status = 'In Stock';
    }

    // Update status
    $update_sql = "UPDATE inventory_items SET status = '$new_status' WHERE item_id = $item_id";
    mysqli_query($conn, $update_sql);

    return $new_status;
  }

  return null;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />

  <style>
    .sidebar-content-item:nth-child(2) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(2) .sidebar-anchor,
    .sidebar-content-item:nth-child(2) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>

  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Inventory</h1>
        <p class="admin-top-desc">Track and manage your inventory items</p>
      </div>

      <?php if ($is_admin): ?>
        <div class="d-flex flex-column flex-md-row gap-2">
          <button class="btn green-bg text-white add-item-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fa-solid fa-plus me-1"></i> Add <span class=" d-none d-md-block ms-1">Item</span>
          </button>
        <?php endif; ?>
        <a href="admin-archive-items.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Items</span>
        </a>
        <?php if ($is_admin): ?>
          <div class="btn-group">
            <button type="button" class="btn btn-primary text-white dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-file-lines me-1"></i> Generate <span class="d-none d-md-block ms-1">Report</span>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="generateWeeklyPDF()">
                  <i class="fa-solid fa-calendar-week me-2"></i>Weekly Report
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="generateMonthlyPDF()">
                  <i class="fa-solid fa-calendar-days me-2"></i>Monthly Report
                </a>
              </li>
            </ul>
          </div>
        <?php endif; ?>
        </div>

    </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Items</p>
            <p class="mb-0 fs-24 text-primary"><?= $stats['total_items'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box fs-32 text-primary"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Low Stock</p>
            <p class="mb-0 fs-24 text-warning"><?= $stats['low_stock'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box fs-32 text-warning"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Out of Stock</p>
            <p class="mb-0 fs-24 text-danger"><?= $stats['out_of_stock'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box fs-32 text-danger"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">In Stock</p>
            <p class="mb-0 fs-24 green-text"><?= $stats['in_stock'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box fs-32 green-text"></i>
          </div>
        </div>
      </div>

    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0">
          <div class="table-responsive table-14px bg-white rounded p-4">
            <?php if (empty($items)): ?>
              <div class="text-center py-5">
                <i class="fa-solid fa-box fs-48 text-muted mb-3"></i>
                <h4 class="text-muted">No Inventory Items</h4>
                <p class="text-muted">Start by adding your first inventory item.</p>
              </div>
            <?php else: ?>
              <table id="inventoryTable" class="table table-hover mb-0">
                <thead>
                  <tr class="bg-white">
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($items as $index => $item): ?>
                    <tr class="bg-white">
                      <th scope="row"><?= $index + 1 ?></th>
                      <td><?= htmlspecialchars($item['item_name']) ?></td>
                      <td><?= htmlspecialchars($item['category']) ?></td>
                      <td>
                        <?php
                        $status = $item['status'];
                        $class = match ($status) {
                          "In Stock"       => "status-badge status-instock",
                          "Low Stock"      => "status-badge status-lowstock",
                          "Out of Stock"   => "status-badge status-outstock",
                          "To Be Delivered" => "status-badge status-delivered",
                          default          => "status-badge"
                        };
                        ?>
                        <span class="<?= $class ?>"><?= htmlspecialchars($status) ?></span>
                      </td>
                      <td><?= htmlspecialchars($item['location']) ?></td>
                      <td class="text-nowrap">
                        <i class="fa-solid fa-eye view-item fs-18"
                          data-id="<?= $item['item_id'] ?>"
                          data-name="<?= htmlspecialchars($item['item_name']) ?>"
                          data-category="<?= htmlspecialchars($item['category']) ?>"
                          data-status="<?= htmlspecialchars($item['status']) ?>"
                          data-location="<?= htmlspecialchars($item['location']) ?>"
                          data-quantity="<?= $item['quantity'] ?>"
                          data-quantity-unit="<?= htmlspecialchars($item['quantity_unit']) ?>"
                          data-price="<?= $item['price'] ?>"
                          data-selling="<?= $item['selling_price'] ?>"
                          data-supplier="<?= htmlspecialchars($item['supplier']) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewItemModal"
                          style="cursor:pointer"></i>
                        <?php if ($is_admin): ?>
                          <i class="fa-solid fa-pencil text-primary mx-2 fs-18 edit-item"
                            data-id="<?= $item['item_id'] ?>"
                            data-name="<?= htmlspecialchars($item['item_name']) ?>"
                            data-category="<?= htmlspecialchars($item['category']) ?>"
                            data-status="<?= htmlspecialchars($item['status']) ?>"
                            data-location="<?= htmlspecialchars($item['location']) ?>"
                            data-quantity="<?= $item['quantity'] ?>"
                            data-quantity-unit="<?= htmlspecialchars($item['quantity_unit']) ?>"
                            data-price="<?= $item['price'] ?>"
                            data-selling="<?= $item['selling_price'] ?>"
                            data-supplier="<?= htmlspecialchars($item['supplier']) ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editItemModal"
                            style="cursor:pointer"></i>
                          <i class="fa-solid fa-box-archive text-danger archive-item fs-18"
                            data-id="<?= $item['item_id'] ?>"
                            style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#archiveItemModal"></i>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>

              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- ADD ITEM MODAL -->
  <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form id="addItemForm" method="POST" action="">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" class="form-control" placeholder="4MP CCTV Camera" value="<?= $_POST['item_name'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['item_name']) ? 'block' : 'none' ?>">
                  <?= $errors['item_name'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                  <option value="CCTV Project" <?= (isset($_POST['category']) && $_POST['category'] == 'CCTV Project') ? 'selected' : '' ?>>CCTV Project</option>
                  <option value="Solar Project" <?= (isset($_POST['category']) && $_POST['category'] == 'Solar Project') ? 'selected' : '' ?>>Solar Project</option>
                  <option value="Room Renovation" <?= (isset($_POST['category']) && $_POST['category'] == 'Room Renovation') ? 'selected' : '' ?>>Room Renovation</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['category']) ? 'block' : 'none' ?>">
                  <?= $errors['category'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <div class="d-flex">

                  <div class="flex-fill" style="width: 130px;">
                    <label class="form-label">Quantity</label>
                    <input
                      type="number"
                      name="quantity"
                      class="form-control"
                      placeholder="10"
                      value="<?= $_POST['quantity'] ?? '' ?>">
                    <?php if (isset($errors['quantity'])): ?>
                      <p class="fs-14 text-danger mb-0 mt-1">
                        <?= $errors['quantity'] ?>
                      </p>
                    <?php endif; ?>
                  </div>

                  <div class="flex-fill">
                    <label class="form-label">Unit</label>
                    <select name="quantity_unit" class="form-select">
                      <option value="piece" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'piece') ? 'selected' : '' ?>>Piece</option>
                      <option value="unit" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'unit') ? 'selected' : '' ?>>Unit</option>
                      <option value="set" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'set') ? 'selected' : '' ?>>Set</option>
                      <option value="roll" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'roll') ? 'selected' : '' ?>>Roll</option>
                      <option value="box" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'box') ? 'selected' : '' ?>>Box</option>
                      <option value="meter" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'meter') ? 'selected' : '' ?>>Meter</option>
                      <option value="liter" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'liter') ? 'selected' : '' ?>>Liter</option>
                      <option value="pack" <?= (isset($_POST['quantity_unit']) && $_POST['quantity_unit'] == 'pack') ? 'selected' : '' ?>>Pack</option>
                    </select>

                    <?php if (isset($errors['quantity_unit'])): ?>
                      <p class="fs-14 text-danger mb-0 mt-1">
                        <?= $errors['quantity_unit'] ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>


              <div class="col-md-4">
                <label class="form-label">Price (₱)</label>
                <input type="number" name="price" class="form-control" placeholder="1500" value="<?= $_POST['price'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['price']) ? 'block' : 'none' ?>">
                  <?= $errors['price'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Unit Price (₱)</label>
                <input type="number" name="selling_price" class="form-control" placeholder="2000" value="<?= $_POST['selling_price'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['selling_price']) ? 'block' : 'none' ?>">
                  <?= $errors['selling_price'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="In Stock" <?= (isset($_POST['status']) && $_POST['status'] == 'In Stock') ? 'selected' : '' ?>>In Stock</option>
                  <option value="Low Stock" <?= (isset($_POST['status']) && $_POST['status'] == 'Low Stock') ? 'selected' : '' ?>>Low Stock</option>
                  <option value="Out of Stock" <?= (isset($_POST['status']) && $_POST['status'] == 'Out of Stock') ? 'selected' : '' ?>>Out of Stock</option>
                  <option value="To Be Delivered" <?= (isset($_POST['status']) && $_POST['status'] == 'To Be Delivered') ? 'selected' : '' ?>>To Be Delivered</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['status']) ? 'block' : 'none' ?>">
                  <?= $errors['status'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="Storage Room A" value="<?= $_POST['location'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['location']) ? 'block' : 'none' ?>">
                  <?= $errors['location'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-4">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" class="form-control" placeholder="Hikvision GMA" value="<?= $_POST['supplier'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['supplier']) ? 'block' : 'none' ?>">
                  <?= $errors['supplier'] ?? 'This field is required' ?>
                </p>
              </div>


            </div>

            <div class="modal-footer mt-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add_item" class="btn btn-green addItem-btn-modal text-white">Add Item</button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- VIEW ITEM MODAL -->
  <div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewItemModalLabel">Item Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <strong>Item Name:</strong>
              <p id="view-item-name"></p>
            </div>
            <div class="col-md-6">
              <strong>Category:</strong>
              <p id="view-category"></p>
            </div>
            <div class="col-md-4">
              <strong>Quantity:</strong>
              <p id="view-quantity"></p>
            </div>
            <div class="col-md-4">
              <strong>Price:</strong>
              <p id="view-price"></p>
            </div>
            <div class="col-md-4">
              <strong>Unit Price:</strong>
              <p id="view-selling"></p>
            </div>
            <div class="col-md-4">
              <strong>Status:</strong>
              <p id="view-status"></p>
            </div>
            <div class="col-md-4">
              <strong>Location:</strong>
              <p id="view-location"></p>
            </div>
            <div class="col-md-4">
              <strong>Supplier:</strong>
              <p id="view-supplier"></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- EDIT ITEM MODAL -->
  <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editItemForm" method="POST" action="">
            <input type="hidden" name="item_id" id="edit-item-id">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" id="edit-item-name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category" id="edit-category" class="form-select" required>
                  <option value="CCTV Project">CCTV Project</option>
                  <option value="Solar Project">Solar Project</option>
                  <option value="Room Renovation">Room Renovation</option>
                </select>
              </div>

              <div class="col-md-4">

                <div class="d-flex">
                  <div>
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="edit-quantity" class="form-control" required style="width: 130px;">
                  </div>

                  <div>
                    <label class="form-label">Unit</label>
                    <select name="quantity_unit" id="edit-quantity-unit" class="form-select">
                      <option value="piece">Piece</option>
                      <option value="unit">Unit</option>
                      <option value="set">Set</option>
                      <option value="roll">Roll</option>
                      <option value="box">Box</option>
                      <option value="meter">Meter</option>
                      <option value="liter">Liter</option>
                      <option value="pack">Pack</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Price (₱)</label>
                <input type="number" name="price" id="edit-price" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Unit Price (₱)</label>
                <input type="number" name="selling_price" id="edit-selling" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" id="edit-status" class="form-select">
                  <option value="In Stock">In Stock</option>
                  <option value="Low Stock">Low Stock</option>
                  <option value="Out of Stock">Out of Stock</option>
                  <option value="To Be Delivered">To Be Delivered</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Location</label>
                <input type="text" name="location" id="edit-location" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" id="edit-supplier" class="form-control" required>
              </div>
            </div>

            <div class="modal-footer mt-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="edit_item" class="btn btn-primary">Update Item</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ARCHIVE ITEM MODAL -->
  <div class="modal fade" id="archiveItemModal" tabindex="-1" aria-labelledby="archiveItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="archiveItemModalLabel">Archive Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to archive this item?</p>
        </div>
        <div class="modal-footer">
          <form method="POST" action="">
            <input type="hidden" name="archive_id" id="archive-item-id">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-archive-button" class="btn btn-danger">Archive</button>
          </form>
        </div>
      </div>
    </div>
  </div>




  <!-- DATATABLES -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</body>
<script>
  $(document).ready(function() {
    // Initialize DataTable only if table exists
    if ($('#inventoryTable').length) {
      $('#inventoryTable').DataTable({
        pageLength: 10,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        responsive: true
      });
    }
  });
</script>


<script>
  // Function to generate Weekly Report PDF
  async function generateWeeklyPDF() {
    const reportWindow = window.open('?report=weekly&auto=1', '_blank', 'width=1200,height=800');

    reportWindow.addEventListener('load', function() {
      setTimeout(async function() {
        try {
          const {
            jsPDF
          } = reportWindow.jspdf;
          const content = reportWindow.document.getElementById('report-content');

          const canvas = await reportWindow.html2canvas(content, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
          });

          const imgData = canvas.toDataURL('image/png');
          const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
          });

          const imgWidth = 210;
          const pageHeight = 297;
          const imgHeight = (canvas.height * imgWidth) / canvas.width;
          let heightLeft = imgHeight;
          let position = 0;

          pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
          heightLeft -= pageHeight;

          while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
          }

          const filename = 'Weekly_Inventory_Report_' + new Date().toISOString().split('T')[0] + '.pdf';
          pdf.save(filename);

          reportWindow.close();
        } catch (error) {
          console.error('Error generating PDF:', error);
          alert('Error generating PDF. Please try again.');
          reportWindow.close();
        }
      });
    });
  }

  // Function to generate Monthly Report PDF
  async function generateMonthlyPDF() {
    const reportWindow = window.open('?report=monthly', '_blank', 'width=1200,height=800');

    reportWindow.addEventListener('load', function() {
      setTimeout(async function() {
        try {
          const {
            jsPDF
          } = reportWindow.jspdf;
          const content = reportWindow.document.getElementById('report-content');

          const canvas = await reportWindow.html2canvas(content, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
          });

          const imgData = canvas.toDataURL('image/png');
          const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
          });

          const imgWidth = 210;
          const pageHeight = 297;
          const imgHeight = (canvas.height * imgWidth) / canvas.width;
          let heightLeft = imgHeight;
          let position = 0;

          pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
          heightLeft -= pageHeight;

          while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
          }

          const filename = 'Monthly_Inventory_Report_' + new Date().toISOString().split('T')[0] + '.pdf';
          pdf.save(filename);

          reportWindow.close();
        } catch (error) {
          console.error('Error generating PDF:', error);
          alert('Error generating PDF. Please try again.');
          reportWindow.close();
        }
      });
    });
  }
</script>
<script>
  // VIEW ITEM
  $(document).on('click', '.view-item', function() {
    $('#view-item-name').text($(this).data('name'));
    $('#view-category').text($(this).data('category'));
    $('#view-quantity').text($(this).data('quantity') + ' ' + $(this).data('quantity-unit'));
    $('#view-price').text('₱' + parseFloat($(this).data('price')).toFixed(2));
    $('#view-selling').text('₱' + parseFloat($(this).data('selling')).toFixed(2));
    $('#view-status').text($(this).data('status'));
    $('#view-location').text($(this).data('location'));
    $('#view-supplier').text($(this).data('supplier'));
  });

  // EDIT ITEM
  $(document).on('click', '.edit-item', function() {
    $('#edit-item-id').val($(this).data('id'));
    $('#edit-item-name').val($(this).data('name'));
    $('#edit-category').val($(this).data('category'));
    $('#edit-quantity').val($(this).data('quantity'));
    $('#edit-quantity-unit').val($(this).data('quantity-unit'));
    $('#edit-price').val($(this).data('price'));
    $('#edit-selling').val($(this).data('selling'));
    $('#edit-status').val($(this).data('status'));
    $('#edit-location').val($(this).data('location'));
    $('#edit-supplier').val($(this).data('supplier'));
  });

  // ARCHIVE ITEM
  $(document).on('click', '.archive-item', function() {
    $('#archive-item-id').val($(this).data('id'));
  });
</script>

</html>