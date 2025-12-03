<?php
include 'admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Admin Attendance</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
    <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />
  <style>
    .sidebar-content-item:nth-child(5) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(5) .sidebar-anchor,
    .sidebar-content-item:nth-child(5) .sidebar-anchor span {
      color: #16A249 !important;

    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Employee Attendance</h1>
        <p class="admin-top-desc">Monitor and track employee attendance records</p>
      </div>
    </div>

    <div class="row g-3 mb-2">

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Employees</p>
            <p class="mb-0 fs-24 fw-bold">4</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-users p-3 light-dark-bg rounded-pill fs-20"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Present</p>
            <p class="mb-0 fs-24 green-text fw-bold">2</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-user-check p-3 light-green-bg rounded-pill fs-18 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Late</p>
            <p class="mb-0 fs-24 text-warning fw-bold">2</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-user-clock p-3 warning-text-bg rounded-pill fs-18 text-warning"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-center">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Absent</p>
            <p class="mb-0 fs-24 text-danger fw-bold">3</p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-user-slash p-3 red-text-bg rounded-pill fs-18 text-danger"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">

          <!-- Date Filter -->
          <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="fs-24 mobile-fs-22 mb-0">Attendance Records</p>
            <div class="flex gap-2">
              <label for="attendanceDate" class="form-label mb-0">Select Date:</label>
              <input type="date" id="attendanceDate" class="form-control w-auto">
            </div>
          </div>
          <div class="divider my-3"></div>
          <!-- Attendance Table -->
          <div class="table-responsive bg-white rounded">
            <table id="attendanceTable" class="table table-hover mb-0">
              <thead>
                <tr class="bg-white">
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Position</th>
                  <th>Date</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Work Hours</th>
                  <th>Status</th>
                </tr>
              </thead>

              <tbody>
                <?php
                $attendanceData = [
                  ['id' => 1, 'name' => 'Lenard Colico', 'position' => 'Security', 'date' => '2025-11-22', 'in' => '08:00', 'out' => '17:00', 'hours' => '9', 'status' => 'Present'],
                  ['id' => 2, 'name' => 'Mikaela Maalat', 'position' => 'Admin', 'date' => '2025-11-22', 'in' => '08:15', 'out' => '17:00', 'hours' => '8.75', 'status' => 'Late'],
                  ['id' => 3, 'name' => 'Roberto Blanco', 'position' => 'Technician', 'date' => '2025-11-22', 'in' => '', 'out' => '', 'hours' => '0', 'status' => 'Absent'],
                  ['id' => 4, 'name' => 'Jahziel Hawan', 'position' => 'Manager', 'date' => '2025-11-23', 'in' => '08:00', 'out' => '17:00', 'hours' => '9', 'status' => 'Present'],
                ];

                foreach ($attendanceData as $row) {
                  $badgeClass = match ($row['status']) {
                    'Present' => 'badge-pill priority-low',
                    'Late'    => 'badge-pill priority-medium',
                    'Absent'  => 'badge-pill priority-high',
                    default   => 'badge-pill'
                  };
                  echo "<tr>
                      <th scope='row'>{$row['id']}</th>
                      <td>{$row['name']}</td>
                      <td>{$row['position']}</td>
                      <td>{$row['date']}</td>
                      <td>{$row['in']}</td>
                      <td>{$row['out']}</td>
                      <td>{$row['hours']}</td>
                      <td><span class='{$badgeClass}'>{$row['status']}</span></td>
                    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>


  </main>
  <!-- END OF MAIN -->

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <!-- jQuery and DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>

<script>
  $(document).ready(function() {
    var table = $('#attendanceTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [{
        orderable: false,
        targets: [7] // Status column not sortable
      }]
    });

    // Date filter
    $('#attendanceDate').on('change', function() {
      var selectedDate = $(this).val();
      table.column(3).search(selectedDate).draw(); // column 3 = Date
    });
  });
</script>



</html>