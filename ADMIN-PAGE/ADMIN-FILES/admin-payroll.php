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
    .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A249 !important;

    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Payroll</h1>
        <p class="admin-top-desc">Manage employee payroll and compensation</p>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Payroll</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">₱285,000</p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-money-bill-wave fs-24 mobile-fs-22 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Employees</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">4</p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-users fs-24 mobile-fs-22 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Average Salary</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">₱23,750</p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-chart-line fs-24 mobile-fs-22 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Payment Date</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold">Nov 30</p>
          </div>
          <div class="inventory-icon">
            <i class="fa-solid fa-calendar-days fs-24 mobile-fs-22 green-text"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">

      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">
          <!-- Date Filter -->
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">Payroll Records</p>
            <div class="flex gap-2">
              <label for="attendanceDate" class="form-label mb-0">Select Date:</label>
              <input type="date" id="payrollDate" class="form-control w-auto">
            </div>
          </div>
          <div class="divider my-3"></div>

          <div class="table-responsive bg-white rounded ">
            <table id="payrollTable" class="table table-hover mb-0">
              <thead>
                <tr class="bg-white">
                  <th>Employee ID</th>
                  <th>Position</th>
                  <th>Base Salary</th>
                  <th>Net Salary</th>
                  <th>Payment Date</th>
                  <th>Status</th>
                  <th>Actions</th>

                </tr>
              </thead>

              <tbody>
                <!-- Employee 1 -->
                <tr class="bg-white">
                  <th scope="row">EMP-001</th>
                  <td>Technician</td>
                  <td>₱25,000</td>
                  <td>₱21,750</td>
                  <td>Nov 15, 2025</td>

                  <?php
                  $status = "Completed";
                  $class = match ($status) {
                    "Completed" => "status-badge taskstatus-completed",
                    "Pending"   => "status-badge taskstatus-pending",
                    default     => "status-badge"
                  };
                  ?>

                  <td><span class="<?= $class ?>"><?= $status ?></span></td>

                  <td>
                    <button class="btn btn-sm btn-green" data-bs-toggle="modal" data-bs-target="#payrollReceiptModal">
                      <i class="fa-solid fa-eye me-1"></i> View Details
                    </button>
                  </td>
                </tr>

                <!-- Employee 2 -->
                <tr class="bg-white">
                  <th scope="row">EMP-002</th>
                  <td>Electrician</td>
                  <td>₱30,000</td>
                  <td>₱26,150</td>
                  <td>Nov 30, 2025</td>

                  <?php
                  $status = "Pending";
                  $class = match ($status) {
                    "Completed" => "status-badge taskstatus-completed",
                    "Pending"   => "status-badge taskstatus-pending",
                    "Processing"   => "status-badge taskstatus-processing",
                    default     => "status-badge"
                  };
                  ?>

                  <td><span class="<?= $class ?>"><?= $status ?></span></td>

                  <td>
                    <button class="btn btn-sm btn-green">
                      <i class="fa-solid fa-eye me-1"></i> View Details
                    </button>
                  </td>
                </tr>

                <!-- Employee 3 (with Process Payment button) -->
                <tr class="bg-white">
                  <th scope="row">EMP-003</th>
                  <td>Installer</td>
                  <td>₱22,000</td>
                  <td>₱19,350</td>
                  <td>Nov 30, 2025</td>

                  <?php
                  $status = "Pending";
                  $class = match ($status) {
                    "Completed" => "status-badge taskstatus-completed",
                    "Pending"   => "status-badge taskstatus-pending",
                    default     => "status-badge"
                  };
                  ?>

                  <td><span class="<?= $class ?>"><?= $status ?></span></td>

                  <td>

                    <button class="btn btn-sm btn-warning text-white ms-1" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                      <i class="fa-solid fa-money-check-dollar me-1"></i> Process Payment
                    </button>
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

  <!-- Payroll Receipt Modal -->
  <div class="modal fade" id="payrollReceiptModal" tabindex="-1" aria-labelledby="payrollReceiptLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="payrollReceiptLabel">Payroll Receipt</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">

          <!-- Payroll Header -->
          <div class="text-center mb-4">
            <h3 class="fw-bold mb-1">PAYROLL RECEIPT</h3>
            <p class="mb-0">A We Green Enterprise</p>
            <p class="mb-0">Cavite, Philippines</p>
            <p class="mb-0">Contact: 09XX-XXX-XXXX</p>
            <hr class="mt-3">
          </div>

          <!-- Employee Info -->
          <div class="d-flex flex-wrap mb-3">

            <div class="flex-column modal-info">
              <small class="light-text">Employee Name</small>
              <p class="fw-semibold mb-0">Juan Dela Cruz</p>
            </div>

            <div class="flex-column modal-info">
              <small class="light-text">Position</small>
              <p class="fw-semibold mb-0">Electrician</p>
            </div>

            <div class="flex-column modal-info">
              <small class="light-text">Payment Date</small>
              <p class="fw-semibold mb-0">November 30, 2024</p>
            </div>

            <div class="flex-column modal-info">
              <small class="light-text">Status</small><br>
              <span class="badge bg-success-light text-success border">Paid</span>
            </div>

          </div>


          <hr>

          <!-- Earnings -->
          <h6 class="fw-bold mb-3">Earnings</h6>
          <div class="d-flex justify-content-between mb-2">
            <span>Base Salary</span>
            <span class="fw-semibold">₱25,000</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Allowances</span>
            <span class="fw-semibold">₱2,000</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-3 fw-bold">
            <span>Gross Salary</span>
            <span>₱27,000</span>
          </div>

          <hr>

          <!-- Deductions -->
          <h6 class="fw-bold mb-3">Deductions</h6>
          <div class="d-flex justify-content-between mb-2">
            <div>
              <span>SSS Contribution</span><br>
              <small class="light-text">4.5% of gross</small>
            </div>
            <span class="text-danger fw-semibold">-₱500</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <div>
              <span>PhilHealth</span><br>
              <small class="light-text">2.5% share</small>
            </div>
            <span class="text-danger fw-semibold">-₱300</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <div>
              <span>Pag-IBIG</span><br>
              <small class="light-text">2% (max ₱100)</small>
            </div>
            <span class="text-danger fw-semibold">-₱100</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <div>
              <span>Withholding Tax</span><br>
              <small class="light-text">Based on TRAIN Law</small>
            </div>
            <span class="text-danger fw-semibold">-₱750</span>
          </div>

          <hr>

          <div class="d-flex justify-content-between text-danger fw-bold mb-3">
            <span>Total Deductions</span>
            <span>-₱1,650</span>
          </div>

          <hr>

          <!-- Net Pay -->
          <div class="bg-light p-3 rounded">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fs-5 fw-bold">Net Pay</span>
              <span class="fs-3 fw-bold text-success">₱25,350</span>
            </div>
          </div>

          <small class="light-text d-block mt-3">
            • SSS: Social Security System
            <br>• PhilHealth: National health insurance
            <br>• Pag-IBIG: Housing contribution
            <br>• Withholding Tax: Per TRAIN Law
          </small>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button class="btn btn-green w-100" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Receipt
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Process Payment Modal -->
  <div class="modal fade" id="processPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">

        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">Process Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">

          <!-- Payment Summary -->
          <div class="p-3 rounded bg-light mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span class="light-text small">Employee</span>
              <span class="fw-semibold" id="employeeName">Juan Dela Cruz</span>
            </div>

            <div class="d-flex justify-content-between">
              <span class="light-text small">Net Salary</span>
              <span class="fw-bold green-text fs-5" id="netSalary">₱25,000</span>
            </div>
          </div>

          <hr>

          <!-- Payment Method -->
          <label class="form-label fw-semibold">Payment Method</label>
          <div class="mb-3">

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="paymentMethod" value="cash" id="cashRadio">
              <label class="form-check-label" for="cashRadio">Cash</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="paymentMethod" value="gcash" id="gcashRadio">
              <label class="form-check-label" for="gcashRadio">GCash</label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="paymentMethod" value="bank" id="bankRadio">
              <label class="form-check-label" for="bankRadio">Bank Transfer</label>
            </div>

          </div>

          <hr>

          <!-- GCash Fields -->
          <div id="gcashSection" class="d-none">
            <div class="mb-3">
              <label class="form-label">GCash Number</label>
              <input type="text" class="form-control" id="gcashNumber" placeholder="09XX XXX XXXX">
            </div>
            <div class="mb-3">
              <label class="form-label">Reference Number</label>
              <input type="text" class="form-control" id="gcashReference" placeholder="Enter GCash reference number">
            </div>
          </div>

          <!-- Bank Fields -->
          <div id="bankSection" class="d-none">
            <div class="mb-3">
              <label class="form-label">Bank Name</label>
              <input type="text" class="form-control" id="bankName" placeholder="e.g., BDO, BPI, Metrobank">
            </div>
            <div class="mb-3">
              <label class="form-label">Account Number</label>
              <input type="text" class="form-control" id="accountNumber" placeholder="Enter account number">
            </div>
            <div class="mb-3">
              <label class="form-label">Transfer Reference Number</label>
              <input type="text" class="form-control" id="bankReference" placeholder="Enter reference number">
            </div>
          </div>

          <!-- Cash Notice -->
          <div id="cashSection" class="alert alert-secondary small d-none">
            Payment will be recorded as cash. Please ensure physical payment is made.
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">

          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-green" id="confirmPaymentBtn">Confirm Payment</button>
        </div>

      </div>
    </div>
  </div>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <!-- jQuery and DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>

<script>
  $(document).ready(function() {
    var table = $('#payrollTable').DataTable({
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

    $('#payrollDate').on('change', function() {
      var selectedDate = $(this).val();
      table.column(4).search(selectedDate).draw();
    });
  });

  function updatePaymentSections() {

    document.getElementById('gcashSection').classList.add('d-none');
    document.getElementById('bankSection').classList.add('d-none');
    document.getElementById('cashSection').classList.add('d-none');

    const selectedValue = document.querySelector('input[name="paymentMethod"]:checked').value;
    // Show the relevant section
    if (selectedValue === "gcash") {
      document.getElementById('gcashSection').classList.remove('d-none');
    } else if (selectedValue === "bank") {
      document.getElementById('bankSection').classList.remove('d-none');
    } else if (selectedValue === "cash") {
      document.getElementById('cashSection').classList.remove('d-none');
    }
  }

  document.getElementById('cashRadio').checked = true;

  updatePaymentSections();

  document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
    radio.addEventListener('change', updatePaymentSections);
  });
</script>




</html>