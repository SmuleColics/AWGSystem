<?php
ob_start();
include 'admin-header.php';

// Handle RESTORE (unarchive)
if (isset($_POST['modal-restore-button'])) {
  $restore_id = (int)$_POST['restore_id'];

  $get_name_sql = "SELECT item_name FROM inventory_items WHERE item_id = $restore_id";
  $result = mysqli_query($conn, $get_name_sql);
  $item_data = mysqli_fetch_assoc($result);
  $item_name = $item_data['item_name'] ?? 'Unknown Item';

  // Restore the item by setting is_archived = 0
  $sql = "UPDATE inventory_items SET is_archived = 0 WHERE item_id = $restore_id";
  if (mysqli_query($conn, $sql)) {
    
    // LOG ACTIVITY
    log_activity(
      $conn,
      $employee_id,
      $employee_full_name,
      'RESTORE',
      'INVENTORY',
      $restore_id,
      $item_name,
      "Restored inventory item: $item_name back to active inventory"
    );

    echo "<script>alert('Item restored successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error restoring item: " . mysqli_error($conn) . "');</script>";
  }
}

// Handle PERMANENT DELETE
if (isset($_POST['modal-permanent-delete-button'])) {
  $delete_id = (int)$_POST['delete_id'];

  // Permanently delete the item
  $sql = "DELETE FROM inventory_items WHERE item_id = $delete_id";
  if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Item permanently deleted!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
  } else {
    echo "<script>alert('Error deleting item: " . mysqli_error($conn) . "');</script>";
  }
}

// Get statistics for ARCHIVED items only
$stats = [
  'low_stock' => 0,
  'out_of_stock' => 0,
  'in_stock' => 0,
  'total_archived' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'To Be Delivered' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['to_be_delivered'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'Low Stock' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['low_stock'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'Out of Stock' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['out_of_stock'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE status = 'In Stock' AND is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['in_stock'] = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items WHERE is_archived = 1");
if ($result) {
  $row = mysqli_fetch_assoc($result);
  $stats['total_archived'] = $row['count'];
}

// Get all ARCHIVED items only
$items = [];
$sql = "SELECT * FROM inventory_items WHERE is_archived = 1 ORDER BY created_at DESC";
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
  <title>Archived Items - Admin Dashboard</title>
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
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">
    <!-- BACK BUTTON -->
    <a href="admin-inventory.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back to Inventory
    </a>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Items</h1>
        <p class="admin-top-desc">View and restore archived inventory items</p>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Archived</p>
            <p class="mb-0 fs-24 text-secondary"><?= $stats['total_archived'] ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-box fs-32 text-secondary"></i>
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
                <i class="fa-solid fa-box-archive fs-48 text-muted mb-3"></i>
                <h4 class="text-muted">No Archived Items</h4>
                <p class="text-muted">All your inventory items are active.</p>
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
                    <th>Quantity</th>
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
                      <td><?= $item['quantity'] ?></td>
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
                          style="cursor:pointer"
                          title="View Details"></i>
                        <i class="fa-solid fa-rotate-left text-success mx-2 fs-18 restore-item"
                          data-id="<?= $item['item_id'] ?>"
                          style="cursor:pointer" 
                          data-bs-toggle="modal" 
                          data-bs-target="#restoreItemModal"
                          title="Restore Item"></i>
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

  <!-- VIEW ITEM MODAL -->
  <div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">View Archived Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fa-solid fa-info-circle me-2"></i>
            This item is archived. Restore it to make changes.
          </div>

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
                <label class="form-label">Price</label>
                <input type="text" class="form-control" id="viewPrice" readonly>
              </div>

              <div class="col-md-4">
                <label class="form-label">Selling Price</label>
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

  <!-- RESTORE MODAL -->
  <div class="modal fade" id="restoreItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="restoreItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="restoreItemLabel">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Inventory Item
          </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="" method="post">
          <input type="hidden" name="restore_id" id="restoreItemId">
          <div class="modal-body">
            <h3 class="fs-20 text-center m-0 py-3">Are you sure you want to restore this item?</h3>
            <p class="text-center text-muted mb-0">This item will be moved back to active inventory.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="modal-restore-button" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore
            </button>
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
    // Initialize DataTable only if table exists
    if ($('#inventoryTable').length) {
      $('#inventoryTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        columnDefs: [{
          orderable: false,
          targets: [6]
        }]
      });
    }

    // View Item Modal - Populate data
    $('.view-item').on('click', function() {
      $('#viewItemName').val($(this).data('name'));
      $('#viewCategory').val($(this).data('category'));
      $('#viewStatus').val($(this).data('status'));
      $('#viewLocation').val($(this).data('location'));
      $('#viewQuantity').val($(this).data('quantity'));
      $('#viewPrice').val('₱' + parseFloat($(this).data('price')).toLocaleString());
      $('#viewSellingPrice').val('₱' + parseFloat($(this).data('selling')).toLocaleString());
      $('#viewSupplier').val($(this).data('supplier'));

      // Format warranty
      let warrantyYears = $(this).data('warranty-years');
      let warrantyMonths = $(this).data('warranty-months');
      let warrantyDays = $(this).data('warranty-days');
      let warranty = '';

      if (warrantyYears > 0) warranty += warrantyYears + ' year' + (warrantyYears > 1 ? 's' : '') + ' ';
      if (warrantyMonths > 0) warranty += warrantyMonths + ' month' + (warrantyMonths > 1 ? 's' : '') + ' ';
      if (warrantyDays > 0) warranty += warrantyDays + ' day' + (warrantyDays > 1 ? 's' : '');

      $('#viewWarranty').val(warranty.trim() || 'No warranty');
    });

    // Restore Item Modal - Set ID
    $('.restore-item').on('click', function() {
      let itemId = $(this).data('id');
      $('#restoreItemId').val(itemId);
    });

    // Delete Item Modal - Set ID and Name
    $('.delete-item').on('click', function() {
      let itemId = $(this).data('id');
      let itemName = $(this).data('name');
      $('#deleteItemId').val(itemId);
      $('#deleteItemName').text(itemName);
    });
  });
</script>

</html>