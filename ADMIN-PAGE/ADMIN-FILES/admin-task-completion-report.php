<?php
include 'admin-header.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['position'] !== 'Admin') {
  header('Location: ../../LANDING-PAGE/LOGIN/login.php');
  exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get filter parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$status_filter = $_GET['status_filter'] ?? 'all';
$employee_filter = $_GET['employee_filter'] ?? 'all';
$auto_generate = isset($_GET['auto']) && $_GET['auto'] === '1';

// Build query based on filters
$where_conditions = ["is_archived = 0"];

// FIXED: Changed from due_date to created_at for more accurate date filtering
if (!empty($date_from) && !empty($date_to)) {
  $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
  $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
  $where_conditions[] = "(DATE(created_at) BETWEEN '$date_from_escaped' AND '$date_to_escaped' OR DATE(due_date) BETWEEN '$date_from_escaped' AND '$date_to_escaped')";
}

if ($status_filter !== 'all') {
  $status_filter_escaped = mysqli_real_escape_string($conn, $status_filter);
  $where_conditions[] = "status = '$status_filter_escaped'";
}

if ($employee_filter !== 'all' && $employee_filter !== '') {
  $employee_filter_int = intval($employee_filter);
  $where_conditions[] = "assigned_to_id = $employee_filter_int";
}

$where_clause = implode(' AND ', $where_conditions);

// Fetch tasks
$report_tasks = [];
$query = "SELECT * FROM tasks WHERE $where_clause ORDER BY due_date DESC, created_at DESC";
$result = mysqli_query($conn, $query);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $report_tasks[] = $row;
  }
}

// Fetch activity logs related to these tasks
$activity_logs = [];
if (!empty($report_tasks)) {
  $task_ids = array_column($report_tasks, 'task_id');
  $task_ids_str = implode(',', array_map('intval', $task_ids));
  
  $log_query = "SELECT * FROM activity_logs 
                WHERE module = 'TASKS' 
                AND item_id IN ($task_ids_str)
                AND DATE(created_at) BETWEEN '$date_from_escaped' AND '$date_to_escaped'
                ORDER BY created_at DESC";
  $log_result = mysqli_query($conn, $log_query);
  
  if ($log_result) {
    while ($log = mysqli_fetch_assoc($log_result)) {
      $activity_logs[] = $log;
    }
  }
}

// Get filter display names
$status_display = $status_filter === 'all' ? 'All Status' : $status_filter;
$employee_display = 'All Employees';
if ($employee_filter !== 'all' && $employee_filter !== '' && is_numeric($employee_filter)) {
  $employee_filter_int = intval($employee_filter);
  $emp_query = "SELECT first_name, last_name FROM employees WHERE employee_id = $employee_filter_int";
  $emp_result = mysqli_query($conn, $emp_query);
  if ($emp_result && $emp_row = mysqli_fetch_assoc($emp_result)) {
    $employee_display = $emp_row['first_name'] . ' ' . $emp_row['last_name'];
  }
}

// Log activity
if (!$auto_generate) {
  log_activity(
    $conn,
    $employee_id,
    $employee_full_name,
    'GENERATE REPORT',
    'TASKS',
    NULL,
    'Task Completion Report',
    "Generated task completion report from $date_from to $date_to - Status: $status_display, Employee: $employee_display - Total Tasks: " . count($report_tasks)
  );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Completion Report - A We Green Enterprise</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @media print {
      .no-print {
        display: none !important;
      }

      body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
      }

      @page {
        size: A4;
        margin: 15mm;
      }
    }

    body {
      font-size: 11px;
    }

    .report-header {
      border-bottom: 3px solid #16A249;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .company-logo {
      max-height: 70px;
      width: auto;
    }

    .report-title {
      color: #16A249;
      font-weight: bold;
      font-size: 24px;
    }

    .info-label {
      font-weight: 600;
      color: #666;
    }

    .table-header {
      background-color: #16A249 !important;
      color: white !important;
    }

    .status-badge {
      padding: 3px 10px;
      border-radius: 10px;
      font-size: 10px;
      font-weight: 500;
      display: inline-block;
    }

    .taskstatus-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .taskstatus-inprogress {
      background-color: #cfe2ff;
      color: #084298;
    }

    .taskstatus-completed {
      background-color: #d1e7dd;
      color: #0f5132;
    }

    .priority-high {
      background-color: #f8d7da;
      color: #721c24;
    }

    .priority-medium {
      background-color: #fff3cd;
      color: #856404;
    }

    .priority-low {
      background-color: #d1ecf1;
      color: #0c5460;
    }

    .summary-card {
      border-left: 4px solid #16A249;
      background-color: #f8f9fa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    .activity-log {
      background-color: #f8f9fa;
      border-radius: 4px;
      padding: 10px;
      margin-bottom: 10px;
      font-size: 10px;
      border-left: 3px solid #6c757d;
    }

    .activity-create {
      border-left-color: #28a745;
    }

    .activity-update {
      border-left-color: #007bff;
    }

    .activity-archive {
      border-left-color: #dc3545;
    }

    footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #e0e0e0;
      font-size: 10px;
      color: #666;
    }

    table {
      font-size: 10px;
    }

    .btn-generate-pdf {
      background-color: #dc3545;
      border: none;
      padding: 12px 30px;
      font-size: 16px;
    }

    .btn-generate-pdf:hover {
      background-color: #bb2d3b;
    }

    .task-section {
      margin-bottom: 25px;
      padding: 15px;
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 5px;
    }

    .task-header {
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .debug-info {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-size: 10px;
    }

    <?php if ($auto_generate): ?>
    .no-print {
      display: none !important;
    }
    <?php endif; ?>
  </style>
</head>

<body>
  <div class="container my-4">

    <!-- ACTION BUTTONS -->
    <div class="text-end mb-3 no-print">
      <button onclick="generatePDF()" class="btn btn-danger btn-generate-pdf me-2">
        <i class="fa-solid fa-file-pdf"></i> Generate PDF
      </button>
      <button onclick="window.print()" class="btn btn-success btn-lg">
        <i class="fa-solid fa-print"></i> Print Report
      </button>
      <button onclick="window.close()" class="btn btn-secondary btn-lg">
        Close
      </button>
    </div>

    <!-- DEBUG INFO (Remove after testing) -->
    <div class="debug-info no-print">
      <strong>Debug Information:</strong><br>
      Query: <?= htmlspecialchars($query) ?><br>
      Total Tasks Found: <?= count($report_tasks) ?><br>
      Date From: <?= htmlspecialchars($date_from) ?><br>
      Date To: <?= htmlspecialchars($date_to) ?><br>
      Status Filter: <?= htmlspecialchars($status_filter) ?><br>
      Employee Filter: <?= htmlspecialchars($employee_filter) ?><br>
    </div>

    <div id="report-content">
      <!-- REPORT HEADER -->
      <div class="report-header">
        <div class="row align-items-center">
          <div class="col-md-2 text-center">
            <img src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Enterprise Logo" class="company-logo">
          </div>
          <div class="col-md-10">
            <h1 class="report-title mb-2">A WE GREEN ENTERPRISE</h1>
            <p class="mb-1"><strong>Main Office:</strong> ATH Phase 4 Blk 51 Lot 30 Brgy. A. Olaes, GMA, Cavite 4117</p>
            <p class="mb-1">
              <strong>Contact:</strong>
              Globe: 0917 752 3343 | Smart: 0998 884 5671
            </p>
            <p class="mb-0"><strong>Email:</strong> awegreenenterprise@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- REPORT INFO -->
      <div class="row mb-3">
        <div class="col-md-12">
          <h2 class="h4 text-uppercase">Task Completion Report</h2>
          <p class="mb-1"><span class="info-label">Report Period:</span> <?= date('F d, Y', strtotime($date_from)) ?> - <?= date('F d, Y', strtotime($date_to)) ?></p>
          <p class="mb-1"><span class="info-label">Status Filter:</span> <?= htmlspecialchars($status_display) ?></p>
          <p class="mb-1"><span class="info-label">Employee Filter:</span> <?= htmlspecialchars($employee_display) ?></p>
          <p class="mb-1"><span class="info-label">Generated On:</span> <?= date('F d, Y h:i A') ?></p>
          <p class="mb-1"><span class="info-label">Generated By:</span> <?= htmlspecialchars($employee_full_name) ?></p>
        </div>
      </div>

      <!-- SUMMARY STATISTICS -->
      <div class="summary-card">
        <h3 class="h6 mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Summary Statistics</h3>
        <div class="row">
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Total Tasks</p>
            <h4 class="h5 text-primary mb-0"><?= count($report_tasks) ?></h4>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Pending</p>
            <h4 class="h5 text-warning mb-0">
              <?= count(array_filter($report_tasks, fn($task) => $task['status'] === 'Pending')) ?>
            </h4>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">In Progress</p>
            <h4 class="h5 text-info mb-0">
              <?= count(array_filter($report_tasks, fn($task) => $task['status'] === 'In Progress')) ?>
            </h4>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Completed</p>
            <h4 class="h5 text-success mb-0">
              <?= count(array_filter($report_tasks, fn($task) => $task['status'] === 'Completed')) ?>
            </h4>
          </div>
        </div>
      </div>

      <!-- TASKS TABLE -->
      <div class="mb-4">
        <h3 class="h6 mb-3"><i class="fa-solid fa-tasks me-2"></i>Task Details</h3>
        
        <?php if (empty($report_tasks)): ?>
          <div class="alert alert-info text-center">
            <i class="fa-solid fa-info-circle me-2"></i>
            No tasks found for the selected filters. Try adjusting your date range or removing filters.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-header">
                <tr>
                  <th style="width: 3%;">#</th>
                  <th style="width: 20%;">Task Title</th>
                  <th style="width: 25%;">Description</th>
                  <th style="width: 12%;">Assigned To</th>
                  <th style="width: 12%;">Project</th>
                  <th style="width: 8%;">Priority</th>
                  <th style="width: 10%;">Status</th>
                  <th style="width: 10%;">Due Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($report_tasks as $index => $task): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($task['task_title']) ?></td>
                    <td><?= htmlspecialchars(substr($task['task_desc'], 0, 100)) ?><?= strlen($task['task_desc']) > 100 ? '...' : '' ?></td>
                    <td><?= htmlspecialchars($task['assigned_to']) ?></td>
                    <td><?= htmlspecialchars($task['project_name'] ?? 'N/A') ?></td>
                    <td>
                      <?php
                      $priority_class = match($task['priority']) {
                        'High' => 'priority-high',
                        'Medium' => 'priority-medium',
                        'Low' => 'priority-low',
                        default => ''
                      };
                      ?>
                      <span class="status-badge <?= $priority_class ?>"><?= htmlspecialchars($task['priority']) ?></span>
                    </td>
                    <td>
                      <?php
                      $status_class = match($task['status']) {
                        'Pending' => 'taskstatus-pending',
                        'In Progress' => 'taskstatus-inprogress',
                        'Completed' => 'taskstatus-completed',
                        default => ''
                      };
                      ?>
                      <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($task['status']) ?></span>
                    </td>
                    <td><?= date('m/d/Y', strtotime($task['due_date'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- ACTIVITY LOGS -->
      <?php if (!empty($activity_logs)): ?>
        <div class="mb-4">
          <h3 class="h6 mb-3"><i class="fa-solid fa-history me-2"></i>Activity Logs (Last 20)</h3>
          
          <?php 
          $displayed_logs = array_slice($activity_logs, 0, 20);
          foreach ($displayed_logs as $log): 
            $action_class = match($log['action']) {
              'CREATE' => 'activity-create',
              'UPDATE' => 'activity-update',
              'ARCHIVE' => 'activity-archive',
              default => ''
            };
          ?>
            <div class="activity-log <?= $action_class ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= htmlspecialchars($log['employee_name']) ?></strong>
                  <span class="text-muted">• <?= htmlspecialchars($log['action']) ?></span>
                  <span class="text-muted">• <?= htmlspecialchars($log['item_name']) ?></span>
                </div>
                <small class="text-muted"><?= date('m/d/Y h:i A', strtotime($log['created_at'])) ?></small>
              </div>
              <?php if ($log['description']): ?>
                <div class="mt-1 text-muted"><?= htmlspecialchars($log['description']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- FOOTER -->
      <footer class="text-center">
        <p class="mb-1">This is a system-generated report from A We Green Enterprise Task Management System</p>
        <p class="mb-0">For inquiries, please contact us at awegreenenterprise@gmail.com</p>
        <p class="mt-2 mb-0">
          <strong>A We Green Enterprise</strong> |
          Main Office: ATH Phase 4 Blk 51 Lot 30 Brgy. A. Olaes, GMA, Cavite 4117
        </p>
      </footer>
    </div>

  </div>

  <!-- jsPDF and html2canvas for PDF generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <script>
    async function generatePDF() {
      const {
        jsPDF
      } = window.jspdf;
      const content = document.getElementById('report-content');

      // Show loading message
      const buttons = document.querySelectorAll('.btn-generate-pdf');
      const originalText = buttons[0].innerHTML;
      buttons[0].innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';
      buttons[0].disabled = true;

      try {
        // Convert HTML to canvas
        const canvas = await html2canvas(content, {
          scale: 2,
          useCORS: true,
          logging: false,
          backgroundColor: '#ffffff'
        });

        const imgData = canvas.toDataURL('image/png');

        // Create PDF
        const pdf = new jsPDF({
          orientation: 'portrait',
          unit: 'mm',
          format: 'a4'
        });

        const imgWidth = 210; // A4 width in mm
        const pageHeight = 297; // A4 height in mm
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;

        // Add first page
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        // Add additional pages if needed
        while (heightLeft > 0) {
          position = heightLeft - imgHeight;
          pdf.addPage();
          pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
          heightLeft -= pageHeight;
        }

        // Save PDF
        const filename = 'Task_Report_<?= date("Y-m-d_His") ?>.pdf';
        pdf.save(filename);

        // Reset button
        buttons[0].innerHTML = originalText;
        buttons[0].disabled = false;

      } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF. Please try using the Print button instead.');
        buttons[0].innerHTML = originalText;
        buttons[0].disabled = false;
      }
    }
  </script>
</body>

</html>