<?php
include 'admin-header.php';
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
  <main id="main" class="container-xxl text-dark px-4">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Inventory</h1>
        <p class="admin-top-desc">Track and manage your inventory items</p>
      </div>
      <div>
        <button class="btn green-bg text-white add-item-btn" data-bs-toggle="modal" data-bs-target="#addItemModal">
          <i class="fa-solid fa-plus me-1"></i> Add Item
        </button>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">To Be Delivered</p>
            <p class="mb-0 fs-24 text-primary">4</p>
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
            <p class="mb-0 fs-24 text-warning">2</p>
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
            <p class="mb-0 fs-24 text-danger">2</p>
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
            <p class="mb-0 fs-24 green-text">4</p>
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
                <tr class="bg-white">
                  <th scope="row">1</th>
                  <td>Security Camera HD</td>
                  <td>CCTV</td>
                  <td>
                    <?php
                    $status = "In Stock";

                    $class = match ($status) {
                      "In Stock"       => "status-badge status-instock",
                      "Low Stock"      => "status-badge status-lowstock",
                      "Out of Stock"   => "status-badge status-outstock",
                      "To Be Delivered" => "status-badge status-delivered",
                      default          => "status-badge"
                    };
                    ?>

                    <span class="<?= $class ?>"><?= $status ?></span>
                  </td>
                  <td>Warehouse A</td>
                  <td class="text-nowrap">
                    <i class="fa-solid fa-eye view-item fs-18" data-bs-toggle="modal" data-bs-target="#viewItemModal" style="cursor:pointer"></i>
                    <i class="fa-solid fa-pencil text-primary mx-2 fs-18"></i>
                    <i class="fa-solid fa-trash text-danger fs-18"></i>
                  </td>
                </tr>
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
          <form id="addItemForm">

            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" placeholder="4MP CCTV Camera" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select class="form-select" required>
                  <option selected>CCTV Project</option>
                  <option>Solar Project</option>
                  <option>Room Renovation</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" placeholder="10" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Price</label>
                <input type="number" class="form-control" placeholder="1500" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Selling Price</label>
                <input type="number" class="form-control" placeholder="2000" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" required>
                  <option selected>In Stock</option>
                  <option>Low Stock</option>
                  <option>Out of Stock</option>
                  <option>To Be Delivered</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" placeholder="Storage Room A" required>
              </div>

              <div class="col-6">
                <label class="form-label">Supplier</label>
                <input type="text" class="form-control" placeholder="Hikvision GMA" required>
              </div>

              <!-- ✅ WARRANTY FIELD ADDED -->
              <div class="col-3">
                <label class="form-label">Warranty Duration</label>
                <input type="number" class="form-control" placeholder="0" required>
              </div>

              <div class="col-3 d-flex align-items-end">
                <select class="form-select" required>
                  <option value="days">Days</option>
                  <option value="months">Months</option>
                  <option value="years">Years</option>
                </select>
              </div>

            </div>

            <div class="modal-footer mt-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-green addItem-btn-modal text-white">Add Item</button>
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

              <!-- ✅ WARRANTY FIELD Added in view modal -->
              <div class="col-6">
                <label class="form-label">Warranty Duration</label>
                <input type="number" class="form-control" placeholder="Enter number" id="viewWarranty" readonly>
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

  <!-- DATATABLES -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</body>

<script>
  $(document).ready(function() {
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
  });
</script>

</html>