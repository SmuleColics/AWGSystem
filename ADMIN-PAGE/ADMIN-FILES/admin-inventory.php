<?php
ob_start();
include 'admin-header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {

  $item_name = trim($_POST['item_name'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $quantity = trim($_POST['quantity'] ?? '');
  $price = trim($_POST['price'] ?? '');
  $selling_price = trim($_POST['selling_price'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $supplier = trim($_POST['supplier'] ?? '');
  $warranty_years = intval($_POST['warranty_years'] ?? 0);
  $warranty_months = intval($_POST['warranty_months'] ?? 0);
  $warranty_days = intval($_POST['warranty_days'] ?? 0);

  if (empty($item_name)) {
    $errors['item_name'] = 'Item name is required';
  }

  if (empty($category)) {
    $errors['category'] = 'Category is required';
  }

  if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) {
    $errors['quantity'] = 'Valid quantity is required';
  }

  if (empty($price) || !is_numeric($price) || $price < 0) {
    $errors['price'] = 'Valid price is required';
  }

  if (empty($selling_price) || !is_numeric($selling_price) || $selling_price < 0) {
    $errors['selling_price'] = 'Valid selling price is required';
  }

  if (empty($status)) {
    $errors['status'] = 'Status is required';
  }

  if (empty($location)) {
    $errors['location'] = 'Location is required';
  }

  if (empty($supplier)) {
    $errors['supplier'] = 'Supplier is required';
  }


  if (empty($errors)) {

    $item_name = mysqli_real_escape_string($conn, $item_name);
    $category = mysqli_real_escape_string($conn, $category);
    $status = mysqli_real_escape_string($conn, $status);
    $location = mysqli_real_escape_string($conn, $location);
    $supplier = mysqli_real_escape_string($conn, $supplier);

    $sql = "INSERT INTO inventory_items (item_name, category, quantity, price, selling_price, status, location, supplier, warranty_years, warranty_months, warranty_days, is_archived) 
                VALUES ('$item_name', '$category', $quantity, $price, $selling_price, '$status', '$location', '$supplier', $warranty_years, $warranty_months, $warranty_days, 0)";

    if (mysqli_query($conn, $sql)) {
      $success = true;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
  
  $item_id = intval($_POST['item_id']);
  $item_name = trim($_POST['item_name'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $quantity = trim($_POST['quantity'] ?? '');
  $price = trim($_POST['price'] ?? '');
  $selling_price = trim($_POST['selling_price'] ?? '');
  $status = trim($_POST['status'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $supplier = trim($_POST['supplier'] ?? '');
  $warranty_years = intval($_POST['warranty_years'] ?? 0);
  $warranty_months = intval($_POST['warranty_months'] ?? 0);
  $warranty_days = intval($_POST['warranty_days'] ?? 0);

  // Validation
  if (empty($item_name)) {
    $errors['item_name'] = 'Item name is required';
  }

  if (empty($category)) {
    $errors['category'] = 'Category is required';
  }

  if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) {
    $errors['quantity'] = 'Valid quantity is required';
  }

  if (empty($price) || !is_numeric($price) || $price < 0) {
    $errors['price'] = 'Valid price is required';
  }

  if (empty($selling_price) || !is_numeric($selling_price) || $selling_price < 0) {
    $errors['selling_price'] = 'Valid selling price is required';
  }

  if (empty($status)) {
    $errors['status'] = 'Status is required';
  }

  if (empty($location)) {
    $errors['location'] = 'Location is required';
  }

  if (empty($supplier)) {
    $errors['supplier'] = 'Supplier is required';
  }

  // If no errors, update the database
  if (empty($errors)) {
    $item_name = mysqli_real_escape_string($conn, $item_name);
    $category = mysqli_real_escape_string($conn, $category);
    $status = mysqli_real_escape_string($conn, $status);
    $location = mysqli_real_escape_string($conn, $location);
    $supplier = mysqli_real_escape_string($conn, $supplier);

    $sql = "UPDATE inventory_items SET 
                item_name = '$item_name',
                category = '$category',
                quantity = $quantity,
                price = $price,
                selling_price = $selling_price,
                status = '$status',
                location = '$location',
                supplier = '$supplier',
                warranty_years = $warranty_years,
                warranty_months = $warranty_months,
                warranty_days = $warranty_days
            WHERE item_id = $item_id";

    if (mysqli_query($conn, $sql)) {
      echo "<script>
                alert('Item updated successfully!');
                window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
              </script>";
      exit;
    } else {
      $errors['database'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

if (isset($_POST['modal-archive-button'])) {
  $archive_id = (int)$_POST['archive_id'];

  // Archive the item by setting is_archived = 1
  $sql = "UPDATE inventory_items SET is_archived = 1 WHERE item_id = $archive_id";
  if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Item archived successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error archiving item: " . mysqli_error($conn) . "');</script>";
  }
}

$stats = [
  'total_items' => 0,
  'low_stock' => 0,
  'out_of_stock' => 0,
  'in_stock' => 0
];

// Get total count of archived items
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

// Get all items (only non-archived)
$items = [];
$sql = "SELECT * FROM inventory_items WHERE is_archived = 0 ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
  }
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
      <div class="d-flex flex-column flex-md-row gap-2">
        <button class="btn green-bg text-white add-item-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
          <i class="fa-solid fa-plus me-1 d-none d-md-block"></i> Add <span class=" d-none d-md-block ms-1">Item</span>
        </button>
        <a href="admin-archive-items.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1 d-none d-md-block"></i>  Archived <span class="d-none d-md-block ms-1">Items</span>
        </a>
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
          <div class="table-responsive table-14px bg-white rounded p-4 ">
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
                        data-price="<?= $item['price'] ?>"
                        data-selling="<?= $item['selling_price'] ?>"
                        data-supplier="<?= htmlspecialchars($item['supplier']) ?>"
                        data-warranty-years="<?= $item['warranty_years'] ?>"
                        data-warranty-months="<?= $item['warranty_months'] ?>"
                        data-warranty-days="<?= $item['warranty_days'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#viewItemModal"
                        style="cursor:pointer"></i>
                      <i class="fa-solid fa-pencil text-primary mx-2 fs-18 edit-item"
                        data-id="<?= $item['item_id'] ?>"
                        data-name="<?= htmlspecialchars($item['item_name']) ?>"
                        data-category="<?= htmlspecialchars($item['category']) ?>"
                        data-status="<?= htmlspecialchars($item['status']) ?>"
                        data-location="<?= htmlspecialchars($item['location']) ?>"
                        data-quantity="<?= $item['quantity'] ?>"
                        data-price="<?= $item['price'] ?>"
                        data-selling="<?= $item['selling_price'] ?>"
                        data-supplier="<?= htmlspecialchars($item['supplier']) ?>"
                        data-warranty-years="<?= $item['warranty_years'] ?>"
                        data-warranty-months="<?= $item['warranty_months'] ?>"
                        data-warranty-days="<?= $item['warranty_days'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#editItemModal"
                        style="cursor:pointer"></i>
                      <i class="fa-solid fa-box-archive text-danger archive-item fs-18"
                        data-id="<?= $item['item_id'] ?>"
                        style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#archiveItemModal"></i>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>

            </table>
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
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" placeholder="10" value="<?= $_POST['quantity'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['quantity']) ? 'block' : 'none' ?>">
                  <?= $errors['quantity'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Price (₱)</label>
                <input type="number" name="price" class="form-control" placeholder="1500" value="<?= $_POST['price'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['price']) ? 'block' : 'none' ?>">
                  <?= $errors['price'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-4">
                <label class="form-label">Selling Price (₱)</label>
                <input type="number" name="selling_price" class="form-control" placeholder="2000" value="<?= $_POST['selling_price'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['selling_price']) ? 'block' : 'none' ?>">
                  <?= $errors['selling_price'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-md-6">
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

              <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="Storage Room A" value="<?= $_POST['location'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['location']) ? 'block' : 'none' ?>">
                  <?= $errors['location'] ?? 'This field is required' ?>
                </p>
              </div>

              <div class="col-6">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" class="form-control" placeholder="Hikvision GMA" value="<?= $_POST['supplier'] ?? '' ?>">
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['supplier']) ? 'block' : 'none' ?>">
                  <?= $errors['supplier'] ?? 'This field is required' ?>
                </p>
              </div>

              <!-- WARRANTY SECTION -->
              <div class="col-6">
                <label class="form-label fw-semibold">Warranty Duration</label>
                <div class="row g-2">

                  <div class="col-md-4">
                    <input type="number" name="warranty_years" class="form-control" placeholder="0" value="<?= $_POST['warranty_years'] ?? 0 ?>">
                    <small class="text-muted">Years</small>
                  </div>

                  <div class="col-md-4">
                    <input type="number" name="warranty_months" class="form-control" placeholder="0" value="<?= $_POST['warranty_months'] ?? 0 ?>">
                    <small class="text-muted">Months</small>
                  </div>

                  <div class="col-md-4">
                    <input type="number" name="warranty_days" class="form-control" placeholder="0" value="<?= $_POST['warranty_days'] ?? 0 ?>">
                    <small class="text-muted">Days</small>
                  </div>

                </div>
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
            <input type="hidden" name="item_id" id="editItemId">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" id="editItemName" class="form-control" placeholder="4MP CCTV Camera">
              </div>

              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category" id="editCategory" class="form-select">
                  <option value="CCTV Project">CCTV Project</option>
                  <option value="Solar Project">Solar Project</option>
                  <option value="Room Renovation">Room Renovation</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="editQuantity" class="form-control" placeholder="10">
              </div>

              <div class="col-md-4">
                <label class="form-label">Price (₱)</label>
                <input type="number" name="price" id="editPrice" class="form-control" placeholder="1500">
              </div>

              <div class="col-md-4">
                <label class="form-label">Selling Price (₱)</label>
                <input type="number" name="selling_price" id="editSellingPrice" class="form-control" placeholder="2000">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" id="editStatus" class="form-select">
                  <option value="In Stock">In Stock</option>
                  <option value="Low Stock">Low Stock</option>
                  <option value="Out of Stock">Out of Stock</option>
                  <option value="To Be Delivered">To Be Delivered</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" name="location" id="editLocation" class="form-control" placeholder="Storage Room A">
              </div>

              <div class="col-6">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" id="editSupplier" class="form-control" placeholder="Hikvision GMA">
              </div>

              <!-- WARRANTY SECTION -->
              <div class="col-6">
                <label class="form-label fw-semibold">Warranty Duration</label>
                <div class="row g-2">

                  <div class="col-md-4">
                    <input type="number" name="warranty_years" id="editWarrantyYears" class="form-control" placeholder="0">
                    <small class="text-muted">Years</small>
                  </div>

                  <div class="col-md-4">
                    <input type="number" name="warranty_months" id="editWarrantyMonths" class="form-control" placeholder="0">
                    <small class="text-muted">Months</small>
                  </div>

                  <div class="col-md-4">
                    <input type="number" name="warranty_days" id="editWarrantyDays" class="form-control" placeholder="0">
                    <small class="text-muted">Days</small>
                  </div>

                </div>
              </div>

            </div>

            <div class="modal-footer mt-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="edit_item" class="btn btn-green text-white">Update Item</button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- VIEW ITEM MODAL -->
  <div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">View Inventory Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form>

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" id="viewItemName" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label">Category</label>
                <input type="text" class="form-control" id="viewCategory" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" id="viewStatus" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" id="viewLocation" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="text" class="form-control" id="viewQuantity" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label">Price (₱)</label>
                <input type="text" class="form-control" id="viewPrice" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label">Selling Price (₱)</label>
                <input type="text" class="form-control" id="viewSellingPrice" readonly>
              </div>

              <div class="col-6">
                <label class="form-label">Supplier</label>
                <input type="text" class="form-control" id="viewSupplier" readonly>
              </div>

              <div class="col-6">
                <label class="form-label">Warranty Duration</label>
                <input type="text" class="form-control" id="viewWarranty" readonly>
              </div>

            </div>

          </form>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>

  <!-- ARCHIVE MODAL (replaces DELETE) -->
  <div class="modal fade" id="archiveItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="archiveItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="archiveItemLabel">Archive Inventory Item</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="archive_id" id="archiveItemId">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this item?</h3>
            <p class="text-center text-muted">Archived items can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="modal-archive-button" class="btn btn-danger">Archive</button>
          </div>
        </form>
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
    // Initialize DataTable
    $('#inventoryTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [{
        orderable: false,
        targets: [5]
      }]
    });

    // View Item Modal - Populate data
    $('.view-item').on('click', function() {
      $('#viewItemName').val($(this).data('name'));
      $('#viewCategory').val($(this).data('category'));
      $('#viewStatus').val($(this).data('status'));
      $('#viewLocation').val($(this).data('location'));
      $('#viewQuantity').val($(this).data('quantity'));
      $('#viewPrice').val($(this).data('price'));
      $('#viewSellingPrice').val($(this).data('selling'));
      $('#viewSupplier').val($(this).data('supplier'));

      // Format warranty
      let warrantyYears = $(this).data('warranty-years');
      let warrantyMonths = $(this).data('warranty-months');
      let warrantyDays = $(this).data('warranty-days');
      let warranty = '';

      if (warrantyYears > 0) warranty += warrantyYears + ' years ';
      if (warrantyMonths > 0) warranty += warrantyMonths + ' months ';
      if (warrantyDays > 0) warranty += warrantyDays + ' days';

      $('#viewWarranty').val(warranty.trim() || 'No warranty');
    });

    // Edit Item Modal - Populate data
    $('.edit-item').on('click', function() {
      $('#editItemId').val($(this).data('id'));
      $('#editItemName').val($(this).data('name'));
      $('#editCategory').val($(this).data('category'));
      $('#editStatus').val($(this).data('status'));
      $('#editLocation').val($(this).data('location'));
      $('#editQuantity').val($(this).data('quantity'));
      $('#editPrice').val($(this).data('price'));
      $('#editSellingPrice').val($(this).data('selling'));
      $('#editSupplier').val($(this).data('supplier'));
      $('#editWarrantyYears').val($(this).data('warranty-years'));
      $('#editWarrantyMonths').val($(this).data('warranty-months'));
      $('#editWarrantyDays').val($(this).data('warranty-days'));
    });

    // Archive Item with confirmation
    $('.archive-item').on('click', function() {
      let itemId = $(this).data('id');
      $('#archiveItemId').val(itemId);
    });

    <?php if (!empty($errors)): ?>
      // If there are errors, reopen the modal
      $('#addItemModal').modal('show');
    <?php endif; ?>
  });
</script>

</html>