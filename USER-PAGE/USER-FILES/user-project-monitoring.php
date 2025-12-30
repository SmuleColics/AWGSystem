<?php
ob_start();
include 'user-header.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id === 0) {
  header('Location: user-awg-projects.php');
  exit;
}

// Fetch project details - ONLY if it belongs to this user
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
WHERE p.project_id = $project_id 
AND p.user_id = $user_id 
AND p.is_archived = 0";

$project_result = mysqli_query($conn, $project_sql);

if (mysqli_num_rows($project_result) === 0) {
  echo "<script>
    alert('Project not found or access denied.');
    window.location='user-awg-projects.php';
  </script>";
  exit;
}

$project = mysqli_fetch_assoc($project_result);

// Only show if visibility is Public OR it's the user's own project
if ($project['visibility'] === 'Private' && $project['user_id'] != $user_id) {
  echo "<script>
    alert('This project is private.');
    window.location='user-awg-projects.php';
  </script>";
  exit;
}

// Fetch project updates
$updates_sql = "SELECT pu.*, e.first_name, e.last_name 
                FROM project_updates pu
                LEFT JOIN employees e ON pu.created_by = e.employee_id
                WHERE pu.project_id = $project_id AND pu.is_archived = 0
                ORDER BY pu.created_at DESC";
$updates_result = mysqli_query($conn, $updates_sql);
$updates = [];
while ($row = mysqli_fetch_assoc($updates_result)) {
  $updates[] = $row;
}

// Fetch payment history
$payments_sql = "SELECT * FROM project_payments 
                WHERE project_id = $project_id 
                AND is_archived = 0
                ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_sql);
$payments = [];
while ($row = mysqli_fetch_assoc($payments_result)) {
  $payments[] = $row;
}

// Calculate financial summary
$total_budget = floatval($project['total_budget']);
$amount_paid = floatval($project['amount_paid']);
$remaining_balance = floatval($project['remaining_balance']);

// Format dates
$start_date = !empty($project['start_date']) ? date('M d, Y', strtotime($project['start_date'])) : 'N/A';
$end_date = !empty($project['end_date']) ? date('M d, Y', strtotime($project['end_date'])) : 'N/A';


$is_archived_payment_view = ($remaining_balance == 0 && $amount_paid > 0 && count($payments) == 0);

// Client info
$client_name = $project['first_name'] . ' ' . $project['last_name'];
$location = !empty($project['location']) ? $project['location'] : $project['user_address'];

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Project Details - <?= htmlspecialchars($project['project_name']) ?></title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .project-image-hero {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 8px;
    }

    .no-image-hero {
      width: 100%;
      height: 300px;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      color: #dee2e6;
      font-size: 64px;
    }

    .update-image {
      max-width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: 8px;
    }

    .sticky-payment {
      position: sticky;
      top: 90px;
    }
  </style>
</head>

<body class="bg-light">

  <div class="container-xxl px-4 py-5 min-vh-100" style="margin-top: 60px;">

    <!-- BACK BUTTON -->
    <div class="mb-4">
      <a href="user-awg-projects.php" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left me-2"></i> Back to Projects
      </a>
    </div>

    <div class="row g-4">

      <!-- LEFT SIDE -->
      <div class="col-lg-8">

        <!-- PROJECT INFO CARD -->
        <div class="card mb-4 p-4">
          <div class="card-body">

            <!-- TOP ROW -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <span class="green-text fw-bold me-2"><?= htmlspecialchars($project['project_type']) ?></span>

                <?php
                $status = $project['status'];
                $class = match ($status) {
                  "Completed"   => "status-badge taskstatus-completed",
                  "Active"      => "status-badge taskstatus-completed",
                  "In Progress" => "status-badge taskstatus-pending",
                  "On Hold"     => "status-badge status-lowstock",
                  "Cancelled"   => "status-badge status-outstock",
                  default       => "status-badge"
                };
                ?>
                <span class="<?= $class ?>"><?= htmlspecialchars($status) ?></span>
              </div>
            </div>

            <!-- PROJECT IMAGE -->
            <?php
            $project_image_path = '';

            if (!empty($project['project_image'])) {
              // Remove all leading ../ or ./ or / from the path
              $clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $project['project_image']);

              // Prepend the correct base path
              $project_image_path = '../../ADMIN-PAGE/' . $clean_path;
            }
            ?>

            <?php if (!empty($project_image_path) && file_exists('../../ADMIN-PAGE/' . $clean_path)): ?>
              <img src="<?= htmlspecialchars($project_image_path) ?>"
                alt="<?= htmlspecialchars($project['project_name']) ?>"
                class="project-image-hero mb-3">
            <?php else: ?>
              <div class="no-image-hero mb-3">
                <i class="fa-solid fa-image"></i>
              </div>
            <?php endif; ?>


            <!-- PROJECT TITLE -->
            <h3 class="fw-bold mb-3"><?= htmlspecialchars($project['project_name']) ?></h3>

            <!-- DESCRIPTION -->
            <?php if (!empty($project['description'])): ?>
              <h6 class="fw-semibold">Description</h6>
              <p class="text-secondary"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
            <?php endif; ?>

            <!-- NOTES -->
            <?php if (!empty($project['notes'])): ?>
              <h6 class="fw-semibold mt-3">Notes</h6>
              <p class="text-secondary"><?= nl2br(htmlspecialchars($project['notes'])) ?></p>
            <?php endif; ?>

            <!-- DETAILS GRID -->
            <div class="row g-3 mt-4">
              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-user text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Client</p>
                  <p class="fw-semibold mb-0"><?= htmlspecialchars($client_name) ?></p>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-location-dot text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Location</p>
                  <p class="fw-semibold mb-0"><?= htmlspecialchars($location) ?></p>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-calendar text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Duration</p>
                  <p class="fw-semibold mb-0"><?= $start_date ?> – <?= $end_date ?></p>
                  <?php if (!empty($project['duration'])): ?>
                    <small class="text-muted">(<?= htmlspecialchars($project['duration']) ?>)</small>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <i class="fa fa-peso-sign text-secondary me-2"></i>
                <div>
                  <p class="light-text small mb-0">Budget</p>
                  <p class="fw-semibold mb-0">₱<?= number_format($total_budget, 2) ?></p>
                </div>
              </div>
            </div>

            <!-- PROGRESS BAR -->
            <div class="mt-4">
              <div class="d-flex justify-content-between align-items-center">
                <span class="light-text small">Project Progress</span>
              </div>

              <div class="d-flex justify-content-between mt-2">
                <span class="fw-semibold"><?= $project['progress_percentage'] ?? 0 ?>%</span>
              </div>

              <div class="progress" style="height: 8px;">
                <div class="progress-bar"
                  style="width: <?= $project['progress_percentage'] ?? 0 ?>%; background-color:#16a249;">
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- PROJECT UPDATES CARD -->
        <div class="card shadow-sm p-4">
          <div class="card-header mb-4 d-flex justify-content-between align-items-center bg-white px-0">
            <h5 class="mb-0 fw-semibold">Project Updates</h5>
          </div>

          <div class="card-body p-0">
            <?php if (count($updates) > 0): ?>
              <?php foreach ($updates as $update): ?>
                <div class="p-3 mb-3 rounded border bg-white">
                  <h6 class="fw-bold mb-1 fs-18"><?= htmlspecialchars($update['update_title']) ?></h6>
                  <p class="text-secondary small mb-2"><?= nl2br(htmlspecialchars($update['update_description'])) ?></p>

                  <?php
                  $project_update_image_path = '';

                  if (!empty($update['update_image'])) {

                    $pu_clean_path = preg_replace('#^(\.\./|\.?/)+#', '', $update['update_image']);


                    $server_path = __DIR__ . '/../../ADMIN-PAGE/' . $pu_clean_path;

                    if (file_exists($server_path)) {
                      // Construct URL path for <img src>
                      $project_update_image_path = '../../ADMIN-PAGE/' . $pu_clean_path;
                    }
                  }
                  ?>

                  <?php if ($project_update_image_path): ?>
                    <div class="w-100 flex">
                      <img src="<?= htmlspecialchars($project_update_image_path) ?>"
                        class="update-image mt-2 border"
                        alt="Update image">
                    </div>
                  <?php endif; ?>



                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <p class="light-text small mb-0">
                      <?= date('F d, Y – g:i A', strtotime($update['created_at'])) ?>
                      <?php if (!empty($update['first_name'])): ?>
                        by <?= htmlspecialchars($update['first_name'] . ' ' . $update['last_name']) ?>
                      <?php endif; ?>
                    </p>
                    <?php if ($update['progress_percentage'] !== null): ?>
                      <span class="badge bg-success">Progress: <?= $update['progress_percentage'] ?>%</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No updates yet</p>
                <small class="text-muted">Updates will appear here as work progresses</small>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- RIGHT SIDE — PAYMENT DETAILS -->
      <div class="col-lg-4">
        <div class="card shadow-sm sticky-payment">
          <div class="card-header bg-white">
            <h5 class="fw-semibold mb-0">Payment Details</h5>
          </div>
          <div class="card-body" style="max-height: 400px; overflow-y: auto;">

            <!-- TOTAL COST -->
            <?php if (!$is_archived_payment_view): ?>
              <div class="p-3 border rounded mb-3 bg-light">
                <div class="fw-semibold mb-1">Project Cost</div>
                <p class="light-text small mb-0">
                  Total Amount: <strong>₱<?= number_format($total_budget, 2) ?></strong>
                </p>
              </div>
            <?php endif; ?>


            <?php if ($remaining_balance == 0 && $amount_paid > 0 && count($payments) == 0): ?>
              <!-- ARCHIVED PAYMENTS STATE -->
              <div class="p-4 text-center border rounded bg-light">
                <div class="mb-3">
                  <i class="fa-solid fa-box-archive text-muted" style="font-size: 56px;"></i>
                </div>

                <h5 class="fw-bold mb-2">Payment Records Archived</h5>

                <p class="text-muted mb-3">
                  Your payment records for this project have been archived.
                </p>

                <p class="small text-muted mb-0">
                  Please contact us at
                  <strong>awegreenenterprise@gmail.com</strong><br>
                  or call <strong>0917 752 3343</strong> for any inquiries.
                </p>
              </div>
            <?php endif; ?>



            <!-- PAYMENT HISTORY -->
            <?php if (!$is_archived_payment_view): ?>
              <?php if (count($payments) > 0): ?>
                <div class="p-3 border rounded mb-3 bg-light">
                  <div class="fw-semibold mb-2">Payment History</div>
                  <?php foreach ($payments as $payment): ?>
                    <div class="mb-2 pb-2 border-bottom">
                      <p class="light-text small mb-1">
                        <strong>₱<?= number_format($payment['payment_amount'], 2) ?></strong>
                      </p>
                      <p class="text-muted small mb-0">
                        <?= date('M d, Y', strtotime($payment['payment_date'])) ?>
                        (<?= htmlspecialchars($payment['payment_method']) ?>)
                      </p>
                      <?php if (!empty($payment['reference_number'])): ?>
                        <p class="text-muted small mb-0">Ref: <?= htmlspecialchars($payment['reference_number']) ?></p>
                      <?php endif; ?>
                      <?php if (!empty($payment['gcash_number'])): ?>
                        <p class="text-muted small mb-0">GCash: <?= htmlspecialchars($payment['gcash_number']) ?></p>
                      <?php endif; ?>
                      <?php if (!empty($payment['notes'])): ?>
                        <p class="text-muted small mb-0">Note: <?= htmlspecialchars($payment['notes']) ?></p>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="p-3 border rounded mb-3 bg-light">
                  <p class="text-muted small text-center mb-0">No payments recorded yet</p>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <?php
            // Get quotation ID for this project
            $quotation_sql = "SELECT q.quotation_id, q.assessment_id, q.total_amount, a.service_type
                  FROM quotations q 
                  INNER JOIN assessments a ON q.assessment_id = a.assessment_id 
                  WHERE a.user_id = {$project['user_id']} 
                  AND q.status IN ('Sent', 'Approved')
                  AND a.service_type = '{$project['project_type']}'
                  ORDER BY q.created_at DESC 
                  LIMIT 1";
            $quotation_result = mysqli_query($conn, $quotation_sql);
            $quotation = mysqli_fetch_assoc($quotation_result);
            ?>

            <!-- REMAINING BALANCE -->
            <?php if (!$is_archived_payment_view): ?>
              <div class="p-3 border rounded mb-3 bg-light">
                <div class="d-flex justify-content-between mb-2">
                  <span class="fw-semibold">Amount Paid:</span>
                  <span class="text-success fw-bold">₱<?= number_format($amount_paid, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Remaining:</span>
                  <span class="text-danger fw-bold">₱<?= number_format($remaining_balance, 2) ?></span>
                </div>
              </div>
            <?php endif; ?>

            <!-- PAYMENT STATUS -->
            <?php if (!$is_archived_payment_view): ?>
              <?php if ($remaining_balance <= 0): ?>
                <div class="alert alert-success mb-0">
                  <i class="fas fa-check-circle me-2"></i>
                  <strong>Paid in Full</strong>
                </div>
              <?php else: ?>
                <div class="alert alert-warning mb-0">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  <strong>Payment Pending</strong>
                  <p class="small mb-0 mt-1">Please proceed to complete your payment to settle the remaining balance.</p>
                </div>
              <?php endif; ?>
            <?php endif; ?>

          </div>
          <div class="card-footer bg-white">
            <div class="d-grid">
              <?php if ($remaining_balance > 0): ?>
                <button class="btn btn-green mb-2" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                  <i class="fas fa-wallet me-1"></i> Make Payment
                </button>
              <?php endif; ?>

              <?php if ($quotation): ?>
                <a href="user-view-quotation.php?id=<?= $quotation['quotation_id'] ?>"
                  class="btn btn-green mb-2">
                  <i class="fas fa-file-invoice me-1"></i> View Quotation Details
                </a>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>

    </div>

  </div>

  <!-- Process Payment Modal -->
  <div class="modal fade" id="processPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Process Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="../../ADMIN-PAGE/ADMIN-FILES/user-process-payment.php" method="POST" id="paymentForm">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">
          <input type="hidden" name="process_payment" value="1">

          <div class="modal-body">
            <!-- Summary -->
            <div class="p-3 rounded bg-light mb-3">
              <div class="d-flex justify-content-between mb-1">
                <span class="light-text small">Total Amount</span>
                <span class="fw-semibold">₱<?= number_format($total_budget, 2) ?></span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="light-text small">Already Paid</span>
                <span class="fw-semibold">₱<?= number_format($amount_paid, 2) ?></span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="light-text small">Remaining Balance</span>
                <span class="fw-bold text-danger">₱<?= number_format($remaining_balance, 2) ?></span>
              </div>
            </div>

            <!-- Payment Amount -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Enter Payment Amount *</label>
              <input type="number" name="amount" class="form-control" id="paymentAmount"
                step="0.01" min="0.01" max="<?= $remaining_balance ?>"
                placeholder="e.g., 5000" required>
              <small class="text-muted">Maximum: ₱<?= number_format($remaining_balance, 2) ?></small>
            </div>

            <!-- Payment Method -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Payment Method *</label>
              <div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="payment_method"
                    value="Cash" id="cashRadio" required>
                  <label class="form-check-label" for="cashRadio">Cash</label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="payment_method"
                    value="GCash" id="gcashRadio">
                  <label class="form-check-label" for="gcashRadio">GCash</label>
                </div>
              </div>
            </div>

            <!-- GCash Section (Hidden by default) -->
            <div id="gcashSection" class="d-none">
              <div class="mb-3">
                <label class="form-label fw-semibold">GCash Number *</label>
                <input type="text" name="gcash_number" id="gcashNumber" class="form-control"
                  placeholder="09XX XXX XXXX" pattern="[0-9]{11}" maxlength="11">
                <small class="text-muted">Enter 11-digit GCash number (e.g., 09123456789)</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Reference Number *</label>
                <input type="text" name="reference_number" id="referenceNumber" class="form-control"
                  placeholder="Enter reference number">
                <small class="text-muted">Transaction reference number from GCash</small>
              </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
              <label class="form-label">Notes (Optional)</label>
              <textarea name="notes" class="form-control" rows="2"
                placeholder="Add payment notes..."></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-green" id="submitPaymentBtn">
              <i class="fas fa-wallet me-1"></i> Confirm Payment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

<script>
  // Payment method toggle
  const cashRadio = document.getElementById('cashRadio');
  const gcashRadio = document.getElementById('gcashRadio');
  const gcashSection = document.getElementById('gcashSection');
  const gcashNumber = document.getElementById('gcashNumber');
  const referenceNumber = document.getElementById('referenceNumber');

  function updatePaymentFields() {
    if (gcashRadio.checked) {
      gcashSection.classList.remove('d-none');
      gcashNumber.setAttribute('required', 'required');
      referenceNumber.setAttribute('required', 'required');
    } else {
      gcashSection.classList.add('d-none');
      gcashNumber.removeAttribute('required');
      referenceNumber.removeAttribute('required');
      gcashNumber.value = '';
      referenceNumber.value = '';
    }
  }

  cashRadio.addEventListener('change', updatePaymentFields);
  gcashRadio.addEventListener('change', updatePaymentFields);

  // Form submission handler
  document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    const amount = document.getElementById('paymentAmount').value;

    if (!paymentMethod) {
      e.preventDefault();
      alert('Please select a payment method');
      return;
    }

    if (!amount || parseFloat(amount) <= 0) {
      e.preventDefault();
      alert('Please enter a valid payment amount');
      return;
    }

    if (paymentMethod.value === 'GCash') {
      const gcash = document.getElementById('gcashNumber').value;
      const ref = document.getElementById('referenceNumber').value;

      if (!gcash || gcash.length !== 11) {
        e.preventDefault();
        alert('Please enter a valid 11-digit GCash number');
        return;
      }

      if (!ref) {
        e.preventDefault();
        alert('Please enter a reference number');
        return;
      }
    }
  });

  // Show success/error messages
  <?php if (isset($_SESSION['success'])): ?>
    alert('<?= addslashes($_SESSION['success']) ?>');
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    alert('<?= addslashes($_SESSION['error']) ?>');
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
</script>

</html>