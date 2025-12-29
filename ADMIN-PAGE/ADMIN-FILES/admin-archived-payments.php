
<?php
ob_start();
include 'admin-header.php';

// Get project ID from URL
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

if ($project_id === 0) {
  header('Location: admin-projects.php');
  exit;
}

// Handle restore actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['restore-single-payment'])) {
    $payment_id = intval($_POST['restore_payment_id']);
    
    // Redirect to restore script
    $_POST['payment_id'] = $payment_id;
    $_POST['project_id'] = $project_id;
    include 'restore-payment.php';
    exit;
  }
  
  if (isset($_POST['restore-all-payments'])) {
    // Redirect to restore script
    $_POST['project_id'] = $project_id;
    $_POST['restore_all'] = true;
    include 'restore-payment.php';
    exit;
  }
}

// Fetch project details
$project_sql = "SELECT p.*, u.first_name, u.last_name 
                FROM projects p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.project_id = $project_id AND p.is_archived = 0";
$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  header('Location: admin-projects.php');
  exit;
}

$project = mysqli_fetch_assoc($project_result);

// Fetch archived payments
$archived_payments_sql = "SELECT * FROM project_payments 
                          WHERE project_id = $project_id 
                          AND is_archived = 1
                          ORDER BY payment_date DESC";
$archived_payments_result = mysqli_query($conn, $archived_payments_sql);
$archived_payments = [];
while ($row = mysqli_fetch_assoc($archived_payments_result)) {
  $archived_payments[] = $row;
}

// Calculate totals
$total_archived = array_sum(array_column($archived_payments, 'payment_amount'));

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archived Payments - <?= htmlspecialchars($project['project_name']) ?></title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css">
  <style>
    .payment-card {
      transition: all 0.3s ease;
    }
    .payment-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .payment-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }
    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A246 !important;
    }
    .timeline-dot {
      width: 48px;
      height: 48px;
      position: relative;
      z-index: 1;
    }
  </style>
</head>

<body class="bg-light">
  <div class="container-xxl px-4 py-5 min-vh-100">
    
    <!-- BACK BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <a href="admin-projects-detail.php?id=<?= $project_id ?>" class="btn btn-outline-secondary">
          <i class="fa fa-arrow-left me-2"></i> Back to Project Details
        </a>
      </div>
      <?php if ($is_admin && count($archived_payments) > 0): ?>
        <div>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreAllModal">
            <i class="fa fa-rotate-left me-1"></i> Restore All Payments
          </button>
        </div>
      <?php endif; ?>
    </div>

    <!-- PROJECT HEADER CARD -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="fw-bold mb-2">
              <i class="fa fa-project-diagram text-success me-2"></i>
              <?= htmlspecialchars($project['project_name']) ?>
            </h4>
            <div class="d-flex gap-3 text-muted">
              <span>
                <i class="fa fa-user me-1"></i>
                Client: <?= htmlspecialchars($project['first_name'] . ' ' . $project['last_name']) ?>
              </span>
              <span>
                <i class="fa fa-tag me-1"></i>
                <?= htmlspecialchars($project['project_type']) ?>
              </span>
            </div>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="p-3 rounded">
              <small class="text-muted d-block mb-1">Total Project Cost</small>
              <h5 class="fw-bold mb-0">₱<?= number_format($project['total_budget'], 2) ?></h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fa fa-box-archive text-muted mb-2" style="font-size: 32px;"></i>
            <h6 class="text-muted mb-1">Total Archived</h6>
            <h4 class="fw-bold mb-0">₱<?= number_format($total_archived, 2) ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fa fa-receipt text-success mb-2" style="font-size: 32px;"></i>
            <h6 class="text-muted mb-1">Payment Records</h6>
            <h4 class="fw-bold mb-0"><?= count($archived_payments) ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fa fa-check-circle text-primary mb-2" style="font-size: 32px;"></i>
            <h6 class="text-muted mb-1">Payment Status</h6>
            <h4 class="fw-bold mb-0 text-success">Fully Paid</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- ARCHIVED PAYMENTS LIST -->
    <div class="card shadow-sm">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold">
            <i class="fa fa-history me-2 text-muted"></i>
            Payment History
          </h5>
          <span class="badge bg-secondary"><?= count($archived_payments) ?> Payment<?= count($archived_payments) > 1 ? 's' : '' ?></span>
        </div>
      </div>
      
      <div class="card-body p-4">
        <?php if (count($archived_payments) > 0): ?>
          
          <!-- Payment Timeline -->
          <div class="position-relative">
            <?php foreach ($archived_payments as $index => $payment): ?>
              <div class="d-flex mb-4 position-relative">
                
                <!-- Timeline Line -->
                <?php if ($index < count($archived_payments) - 1): ?>
                  <div class="position-absolute" style="left: 24px; top: 50px; bottom: -30px; width: 2px; background: #dee2e6;"></div>
                <?php endif; ?>
                
                <!-- Timeline Dot -->
                <div class="flex-shrink-0 me-3">
                  <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center timeline-dot">
                    <i class="fa fa-check"></i>
                  </div>
                </div>

                <!-- Payment Card -->
                <div class="flex-grow-1">
                  <div class="card border shadow-sm payment-card">
                    <div class="card-body">
                      
                      <!-- Header -->
                      <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                          <div class="d-flex align-items-center gap-2 mb-2">
                            <h5 class="fw-bold text-success mb-0">₱<?= number_format($payment['payment_amount'], 2) ?></h5>
                            <span class="payment-badge <?= $payment['payment_method'] === 'Cash' ? 'bg-success text-white' : 'green-bg text-white' ?>">
                              <i class="fa <?= $payment['payment_method'] === 'Cash' ? 'fa-money-bill' : 'fa-mobile-alt' ?> me-1"></i>
                              <?= htmlspecialchars($payment['payment_method']) ?>
                            </span>
                          </div>
                          <p class="text-muted small mb-0">
                            <i class="fa fa-calendar me-1"></i>
                            <?= date('F d, Y - g:i A', strtotime($payment['payment_date'])) ?>
                          </p>
                        </div>
                        
                      </div>

                      <!-- Payment Details Grid -->
                      <div class="row g-3">
                        
                        <?php if ($payment['payment_method'] === 'GCash'): ?>
                          
                          <?php if (!empty($payment['gcash_number'])): ?>
                            <div class="col-md-6">
                              <div class="p-2 rounded">
                                <small class="text-muted d-block mb-1">
                                  <i class="fa fa-phone me-1"></i> GCash Number
                                </small>
                                <span class="fw-semibold"><?= htmlspecialchars($payment['gcash_number']) ?></span>
                              </div>
                            </div>
                          <?php endif; ?>

                          <?php if (!empty($payment['reference_number'])): ?>
                            <div class="col-md-6">
                              <div class="p-2 rounded">
                                <small class="text-muted d-block mb-1">
                                  <i class="fa fa-hashtag me-1"></i> Reference Number
                                </small>
                                <span class="fw-semibold"><?= htmlspecialchars($payment['reference_number']) ?></span>
                              </div>
                            </div>
                          <?php endif; ?>

                        <?php else: ?>
                          
                          <?php if (!empty($payment['reference_number'])): ?>
                            <div class="col-md-6">
                              <div class="p-2 rounded">
                                <small class="text-muted d-block mb-1">
                                  <i class="fa fa-hashtag me-1"></i> Reference Number
                                </small>
                                <span class="fw-semibold"><?= htmlspecialchars($payment['reference_number']) ?></span>
                              </div>
                            </div>
                          <?php endif; ?>

                        <?php endif; ?>

                        <?php if (!empty($payment['payment_notes'])): ?>
                          <div class="col-12">
                            <div class="p-3 rounded">
                              <small class="text-muted d-block mb-1">
                                <i class="fa fa-comment-dots me-1"></i> Notes
                              </small>
                              <p class="mb-0"><?= nl2br(htmlspecialchars($payment['payment_notes'])) ?></p>
                            </div>
                          </div>
                        <?php endif; ?>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- TOTAL SUMMARY -->
          <div class="card bg-success text-white border-0 mt-4">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h5 class="mb-0 fw-semibold">
                    <i class="fa fa-check-circle me-2"></i>
                    Total Amount Paid
                  </h5>
                  <small class="opacity-75"><?= count($archived_payments) ?> payment transaction<?= count($archived_payments) > 1 ? 's' : '' ?> completed</small>
                </div>
                <div class="col-md-4 text-end">
                  <h3 class="fw-bold mb-0">₱<?= number_format($total_archived, 2) ?></h3>
                  <small class="opacity-75">Project Fully Paid</small>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- EMPTY STATE -->
          <div class="text-center py-5">
            <i class="fa fa-inbox text-muted mb-3" style="font-size: 64px;"></i>
            <h5 class="text-muted mb-2">No Archived Payment Records</h5>
            <p class="text-muted">There are no archived payment records for this project.</p>
            <a href="admin-projects-detail.php?id=<?= $project_id ?>" class="btn btn-outline-secondary mt-3">
              <i class="fa fa-arrow-left me-1"></i> Return to Project
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- RESTORE SINGLE PAYMENT MODAL -->
  <div class="modal fade" id="restorePaymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore Payment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="restore_payment_id" id="restorePaymentId">
          <div class="modal-body">
            <h6 class="text-center py-3 fs-20">Are you sure you want to restore this payment?</h6>
            <p class="text-center text-muted mb-0">
              Payment of <strong id="paymentAmountDisplay"></strong> will be moved back to active payments.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="restore-single-payment" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- RESTORE ALL PAYMENTS MODAL -->
  <div class="modal fade" id="restoreAllModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-rotate-left text-success me-2"></i>
            Restore All Payments
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST">
          <div class="modal-body">
            <h6 class="text-center py-3 fs-20">Are you sure you want to restore ALL payment records?</h6>
            <p class="text-center text-muted mb-3">
              All <strong><?= count($archived_payments) ?> payment record<?= count($archived_payments) > 1 ? 's' : '' ?></strong> will be moved back to active payments.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="restore-all-payments" class="btn btn-success">
              <i class="fa-solid fa-rotate-left me-1"></i> Restore All
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function showRestoreModal(paymentId, amount) {
      document.getElementById('restorePaymentId').value = paymentId;
      document.getElementById('paymentAmountDisplay').textContent = '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      
      const modal = new bootstrap.Modal(document.getElementById('restorePaymentModal'));
      modal.show();
    }

    // Show messages
    <?php if (isset($_SESSION['success'])): ?>
      alert('<?= addslashes($_SESSION['success']) ?>');
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      alert('<?= addslashes($_SESSION['error']) ?>');
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  </script>
</body>
</html>