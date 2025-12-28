<?php
include 'admin-header.php';

if (!isset($_SESSION['employee_id']) || $_SESSION['position'] !== 'Admin') {
  header('Location: ../index.php');
  exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get project ID from URL
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id === 0) {
  header('Location: admin-projects.php');
  exit;
}

$auto_generate = isset($_GET['auto']) && $_GET['auto'] === '1';

// Fetch project details with user info
$project_sql = "SELECT 
    p.*,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    TRIM(CONCAT_WS(', ',
        NULLIF(u.house_no, ''),
        NULLIF(u.brgy, ''),
        NULLIF(u.city, ''),
        NULLIF(u.province, '')
    )) AS user_address
FROM projects p
JOIN users u ON p.user_id = u.user_id
WHERE p.project_id = $project_id";

$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  header('Location: admin-projects.php');
  exit;
}

$project = mysqli_fetch_assoc($project_result);

// Fetch project updates
$updates_sql = "SELECT pu.*, e.first_name, e.last_name 
                FROM project_updates pu
                LEFT JOIN employees e ON pu.created_by = e.employee_id
                WHERE pu.project_id = $project_id
                ORDER BY pu.created_at DESC";
$updates_result = mysqli_query($conn, $updates_sql);
$updates = [];
while ($row = mysqli_fetch_assoc($updates_result)) {
  $updates[] = $row;
}

// Fetch payment history
$payments_sql = "SELECT * FROM project_payments 
                WHERE project_id = $project_id 
                ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_sql);
$payments = [];
while ($row = mysqli_fetch_assoc($payments_result)) {
  $payments[] = $row;
}

// Fetch assigned tasks for this project
$tasks_sql = "SELECT t.*, e.first_name, e.last_name
              FROM tasks t
              LEFT JOIN employees e ON t.assigned_to_id = e.employee_id
              WHERE t.project_id = $project_id
              ORDER BY t.due_date ASC";
$tasks_result = mysqli_query($conn, $tasks_sql);
$tasks = [];
while ($row = mysqli_fetch_assoc($tasks_result)) {
  $tasks[] = $row;
}

// Calculate financial summary
$total_budget = floatval($project['total_budget']);
$amount_paid = floatval($project['amount_paid']);
$remaining_balance = floatval($project['remaining_balance']);

// Format dates
$start_date = !empty($project['start_date']) ? date('F d, Y', strtotime($project['start_date'])) : 'N/A';
$end_date = !empty($project['end_date']) ? date('F d, Y', strtotime($project['end_date'])) : 'N/A';

// Client info
$client_name = $project['first_name'] . ' ' . $project['last_name'];
$location = !empty($project['location']) ? $project['location'] : $project['user_address'];

// Log activity
if (!$auto_generate) {
  log_activity(
    $conn,
    $employee_id,
    $employee_full_name,
    'GENERATE REPORT',
    'PROJECTS',
    $project_id,
    $project['project_name'],
    "Generated progress report for project: {$project['project_name']}"
  );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Progress Report - <?= htmlspecialchars($project['project_name']) ?></title>
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

    .status-completed,
    .status-active {
      background-color: #d1e7dd;
      color: #0f5132;
    }

    .status-in-progress {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-on-hold {
      background-color: #f8d7da;
      color: #721c24;
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

    .summary-card {
      border-left: 4px solid #16A249;
      background-color: #f8f9fa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    .section-box {
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 5px;
      padding: 15px;
      margin-bottom: 20px;
      page-break-inside: avoid;
    }

    .section-header {
      border-bottom: 2px solid #16A249;
      padding-bottom: 8px;
      margin-bottom: 15px;
      font-weight: bold;
      color: #16A249;
    }

    .progress-bar-container {
      height: 25px;
      background-color: #e9ecef;
      border-radius: 12px;
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      background-color: #16A249;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 11px;
      font-weight: bold;
    }

    .update-item {
      background-color: #f8f9fa;
      border-radius: 4px;
      padding: 10px;
      margin-bottom: 10px;
      font-size: 10px;
      border-left: 3px solid #16A249;
    }

    .payment-item {
      background-color: #f8f9fa;
      border-radius: 4px;
      padding: 8px;
      margin-bottom: 8px;
      font-size: 10px;
      border-left: 3px solid #0d6efd;
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

    .project-image {
      max-width: 100%;
      max-height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
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
          <h2 class="h4 text-uppercase">Project Progress Report</h2>
          <h3 class="h5 text-primary"><?= htmlspecialchars($project['project_name']) ?></h3>
          <p class="mb-1"><span class="info-label">Report Generated On:</span> <?= date('F d, Y h:i A') ?></p>
          <p class="mb-1"><span class="info-label">Generated By:</span> <?= htmlspecialchars($employee_full_name) ?></p>
        </div>
      </div>

      <!-- PROJECT SUMMARY -->
      <div class="summary-card">
        <h3 class="h6 mb-3"><i class="fa-solid fa-info-circle me-2"></i>Project Summary</h3>
        <div class="row">
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Project Type</p>
            <h4 class="h6 mb-0"><?= htmlspecialchars($project['project_type']) ?></h4>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Status</p>
            <?php
            $status_class = match($project['status']) {
              'Completed' => 'status-completed',
              'Active' => 'status-active',
              'In Progress' => 'status-in-progress',
              'On Hold' => 'status-on-hold',
              default => ''
            };
            ?>
            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($project['status']) ?></span>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Progress</p>
            <h4 class="h6 mb-0 text-success"><?= $project['progress_percentage'] ?>%</h4>
          </div>
          <div class="col-md-3">
            <p class="mb-1 text-muted small">Visibility</p>
            <h4 class="h6 mb-0"><?= htmlspecialchars($project['visibility']) ?></h4>
          </div>
        </div>
      </div>

      <!-- PROJECT DETAILS -->
      <div class="section-box">
        <h4 class="section-header"><i class="fa-solid fa-clipboard-list me-2"></i>Project Details</h4>
        
        <?php if (!empty($project['project_image']) && file_exists($project['project_image'])): ?>
          <div class="text-center mb-3">
            <img src="<?= htmlspecialchars($project['project_image']) ?>" 
                 alt="<?= htmlspecialchars($project['project_name']) ?>" 
                 class="project-image">
          </div>
        <?php endif; ?>

        <div class="row mb-2">
          <div class="col-md-6">
            <p class="mb-2"><strong><i class="fa fa-user me-1"></i> Client:</strong> <?= htmlspecialchars($client_name) ?></p>
            <p class="mb-2"><strong><i class="fa fa-envelope me-1"></i> Email:</strong> <?= htmlspecialchars($project['email']) ?></p>
            <p class="mb-2"><strong><i class="fa fa-phone me-1"></i> Phone:</strong> <?= htmlspecialchars($project['phone']) ?></p>
          </div>
          <div class="col-md-6">
            <p class="mb-2"><strong><i class="fa fa-location-dot me-1"></i> Location:</strong> <?= htmlspecialchars($location) ?></p>
            <p class="mb-2"><strong><i class="fa fa-calendar me-1"></i> Start Date:</strong> <?= $start_date ?></p>
            <p class="mb-2"><strong><i class="fa fa-calendar-check me-1"></i> End Date:</strong> <?= $end_date ?></p>
            <?php if (!empty($project['duration'])): ?>
              <p class="mb-2"><strong><i class="fa fa-clock me-1"></i> Duration:</strong> <?= htmlspecialchars($project['duration']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($project['description'])): ?>
          <div class="mt-2">
            <strong>Description:</strong>
            <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
          </div>
        <?php endif; ?>

        <?php if (!empty($project['notes'])): ?>
          <div class="mt-2">
            <strong>Notes:</strong>
            <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($project['notes'])) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- PROJECT PROGRESS -->
      <div class="section-box">
        <h4 class="section-header"><i class="fa-solid fa-chart-line me-2"></i>Project Progress</h4>
        <div class="mb-2">
          <div class="d-flex justify-content-between mb-1">
            <span><strong>Overall Progress</strong></span>
            <span><strong><?= $project['progress_percentage'] ?>%</strong></span>
          </div>
          <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: <?= $project['progress_percentage'] ?>%;">
              <?= $project['progress_percentage'] ?>%
            </div>
          </div>
        </div>
      </div>

      <!-- FINANCIAL SUMMARY -->
      <div class="section-box">
        <h4 class="section-header"><i class="fa-solid fa-peso-sign me-2"></i>Financial Summary</h4>
        <div class="row">
          <div class="col-md-4">
            <p class="mb-1 text-muted small">Total Budget</p>
            <h4 class="h5 text-primary mb-0">₱<?= number_format($total_budget, 2) ?></h4>
          </div>
          <div class="col-md-4">
            <p class="mb-1 text-muted small">Amount Paid</p>
            <h4 class="h5 text-success mb-0">₱<?= number_format($amount_paid, 2) ?></h4>
          </div>
          <div class="col-md-4">
            <p class="mb-1 text-muted small">Remaining Balance</p>
            <h4 class="h5 text-danger mb-0">₱<?= number_format($remaining_balance, 2) ?></h4>
          </div>
        </div>

        <?php if (!empty($payments)): ?>
          <div class="mt-3">
            <strong class="d-block mb-2">Payment History:</strong>
            <?php foreach ($payments as $payment): ?>
              <div class="payment-item">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong>₱<?= number_format($payment['payment_amount'], 2) ?></strong>
                    <span class="text-muted">- <?= htmlspecialchars($payment['payment_method']) ?></span>
                    <?php if (!empty($payment['reference_number'])): ?>
                      <span class="text-muted">(Ref: <?= htmlspecialchars($payment['reference_number']) ?>)</span>
                    <?php endif; ?>
                  </div>
                  <span class="text-muted"><?= date('m/d/Y', strtotime($payment['payment_date'])) ?></span>
                </div>
                <?php if (!empty($payment['notes'])): ?>
                  <p class="mb-0 mt-1 text-muted"><?= htmlspecialchars($payment['notes']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- PROJECT UPDATES -->
      <?php if (!empty($updates)): ?>
        <div class="section-box">
          <h4 class="section-header"><i class="fa-solid fa-clipboard-list me-2"></i>Project Updates (<?= count($updates) ?>)</h4>
          <?php foreach ($updates as $update): ?>
            <div class="update-item">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= htmlspecialchars($update['update_title']) ?></strong>
                  <?php if ($update['progress_percentage'] !== null): ?>
                    <span class="badge bg-success ms-2" style="font-size: 9px;">Progress: <?= $update['progress_percentage'] ?>%</span>
                  <?php endif; ?>
                </div>
                <span class="text-muted"><?= date('m/d/Y h:i A', strtotime($update['created_at'])) ?></span>
              </div>
              <p class="mb-1 mt-1"><?= nl2br(htmlspecialchars($update['update_description'])) ?></p>
              <?php if (!empty($update['first_name'])): ?>
                <p class="mb-0 text-muted"><em>By: <?= htmlspecialchars($update['first_name'] . ' ' . $update['last_name']) ?></em></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ASSIGNED TASKS -->
      <?php if (!empty($tasks)): ?>
        <div class="section-box">
          <h4 class="section-header"><i class="fa-solid fa-tasks me-2"></i>Assigned Tasks (<?= count($tasks) ?>)</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-header">
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 25%;">Task Title</th>
                  <th style="width: 20%;">Assigned To</th>
                  <th style="width: 15%;">Status</th>
                  <th style="width: 10%;">Priority</th>
                  <th style="width: 15%;">Due Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tasks as $index => $task): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($task['task_title']) ?></td>
                    <td><?= htmlspecialchars($task['assigned_to']) ?></td>
                    <td>
                      <?php
                      $task_status_class = match($task['status']) {
                        'Pending' => 'taskstatus-pending',
                        'In Progress' => 'taskstatus-inprogress',
                        'Completed' => 'taskstatus-completed',
                        default => ''
                      };
                      ?>
                      <span class="status-badge <?= $task_status_class ?>"><?= htmlspecialchars($task['status']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($task['priority']) ?></td>
                    <td><?= date('m/d/Y', strtotime($task['due_date'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <!-- FOOTER -->
      <footer class="text-center">
        <p class="mb-1">This is a system-generated report from A We Green Enterprise Project Management System</p>
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
        const filename = 'Project_Report_<?= htmlspecialchars($project['project_name']) ?>_<?= date("Y-m-d") ?>.pdf';
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