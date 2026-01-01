<?php
ob_start();
include 'admin-header.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id === 0) {
  header('Location: admin-projects.php');
  exit;
}

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
WHERE p.project_id = $project_id AND p.is_archived = 0";

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
                WHERE pu.project_id = $project_id AND pu.is_archived = 0
                ORDER BY pu.created_at DESC";
$updates_result = mysqli_query($conn, $updates_sql);
$updates = [];
while ($row = mysqli_fetch_assoc($updates_result)) {
  $updates[] = $row;
}

$payments_sql = "SELECT * FROM project_payments 
                WHERE project_id = $project_id 
                AND (is_archived = 0 OR is_archived IS NULL)
                ORDER BY created_at DESC";
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
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <style>
    .sidebar-content-item:nth-child(6) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(6) .sidebar-anchor,
    .sidebar-content-item:nth-child(6) .sidebar-anchor span {
      color: #16A246 !important;
    }

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

  <div class="container-xxl px-4 py-5 min-vh-100">

    <!-- BACK BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <a href="admin-projects.php" class="btn d-block btn-outline-secondary ">
          <i class="fa fa-arrow-left me-2"></i> Back to Projects
        </a>
      </div>
      <div>
        <?php if ($is_admin): ?>
          <button class="btn btn-primary" onclick="generateProjectReport(<?= $project_id ?>)">
            <i class="fa-solid fa-file-lines me-1"></i> Generate Progress Report
          </button>
        <?php endif; ?>
      </div>
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

              <!-- VISIBILITY OPTION -->
              <?php if ($is_admin): ?>
                <div>
                  <form method="POST" action="update-project-visibility.php" id="visibilityForm">
                    <input type="hidden" name="project_id" value="<?= $project_id ?>">
                    <select class="form-select form-select-sm" name="visibility" onchange="this.form.submit()">
                      <option value="Private" <?= $project['visibility'] === 'Private' ? 'selected' : '' ?>>Private</option>
                      <option value="Public" <?= $project['visibility'] === 'Public' ? 'selected' : '' ?>>Public</option>
                    </select>
                  </form>
                </div>
              <?php endif; ?>
            </div>

            <!-- PROJECT IMAGE -->
            <?php if (!empty($project['project_image']) && file_exists($project['project_image'])): ?>
              <img src="<?= htmlspecialchars($project['project_image']) ?>"
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
            <div class="d-flex gap-2">
              <?php if ($is_admin): ?>
                <a href="project-update-archive.php?id=<?= $project_id ?>" class="btn btn-danger btn-sm">
                  <i class="fa fa-box-archive me-1"></i> View Archived
                </a>
                <button class="btn btn-green btn-sm" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                  <i class="fa fa-plus me-1"></i> Add Update
                </button>
              <?php endif; ?>
            </div>
          </div>

          <div class="card-body p-0">
            <?php if (count($updates) > 0): ?>
              <?php foreach ($updates as $update): ?>
                <div class="p-3 mb-3 rounded border bg-white">
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-1 fs-18"><?= htmlspecialchars($update['update_title']) ?></h6>
                    <?php if ($is_admin): ?>
                      <div class="update-btns">
                        <button class="btn btn-light border btn-sm me-1"
                          onclick="editUpdate(<?= $update['update_id'] ?>, '<?= addslashes($update['update_title']) ?>', '<?= addslashes($update['update_description']) ?>', <?= $update['progress_percentage'] ?>)">
                          <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-danger btn-sm"
                          onclick="archiveUpdate(<?= $update['update_id'] ?>)">
                          <i class="fa-solid fa-box-archive fs-16"></i>
                        </button>
                      </div>

                    <?php endif; ?>
                  </div>
                  <p class="text-secondary small mb-2"><?= nl2br(htmlspecialchars($update['update_description'])) ?></p>

                  <?php if (!empty($update['update_image']) && file_exists($update['update_image'])): ?>
                    <div class="w-100 flex">
                      <img src="<?= htmlspecialchars($update['update_image']) ?>"
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
              <p class="light-text small text-center">No updates yet</p>
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

            <?php if ($remaining_balance == 0 && $amount_paid > 0 && count($payments) == 0): ?>
              <!-- ARCHIVED PAYMENTS STATE -->
              <div class="text-center py-5">
                <div class="mb-4">
                  <i class="fa-solid fa-box-archive text-muted" style="font-size: 64px;"></i>
                </div>
                <h5 class="fw-bold mb-2">Payment Details Archived</h5>
                <p class="text-muted mb-3">
                  All payment records for this project have been archived.
                </p>

              </div>

            <?php elseif ($remaining_balance == 0 && $amount_paid > 0 && count($payments) > 0): ?>
              <!-- FULLY PAID WITH ACTIVE PAYMENTS STATE -->
              <div class="text-center py-5">
                <div class="mb-4">
                  <i class="fa-solid fa-circle-check text-success" style="font-size: 64px;"></i>
                </div>
                <h5 class="fw-bold text-success mb-2">Project Fully Paid!</h5>
                <p class="text-muted mb-3">
                  All payments for this project have been completed.
                </p>
                <div class="p-3 border rounded bg-light">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Total Paid:</span>
                    <span class="fw-bold text-success fs-5">₱<?= number_format($amount_paid, 2) ?></span>
                  </div>
                </div>

              </div>

            <?php else: ?>
              <!-- ACTIVE PAYMENT STATE -->

              <!-- TOTAL COST -->
              <div class="p-3 border rounded mb-3 bg-light">
                <div class="fw-semibold mb-1">Project Cost</div>
                <p class="light-text small mb-0">
                  Total Amount: <strong>₱<?= number_format($total_budget, 2) ?></strong>
                </p>
              </div>

              <!-- PAYMENT HISTORY -->
              <?php if (count($payments) > 0): ?>
                <div class="p-3 border rounded mb-3 bg-light">
                  <div class="fw-semibold mb-2">Payment History</div>
                  <?php foreach ($payments as $payment): ?>
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                      <div class="flex-grow-1">
                        <p class="light-text small mb-1">
                          <strong>₱<?= number_format($payment['payment_amount'], 2) ?></strong> -
                          <?= date('M d, Y \a\t g:i A', strtotime($payment['created_at'])) ?>
                          (<?= htmlspecialchars($payment['payment_method']) ?>)
                          <?php if (!empty($payment['reference_number'])): ?>
                            <br><span class="text-muted">Ref: <?= htmlspecialchars($payment['reference_number']) ?></span>
                          <?php endif; ?>
                          <?php if (!empty($payment['gcash_number'])): ?>
                            <br><span class="text-muted">GCash: <?= htmlspecialchars($payment['gcash_number']) ?></span>
                          <?php endif; ?>
                          <?php if (!empty($payment['payment_notes'])): ?>
                            <br><span class="text-muted">Note: <?= htmlspecialchars($payment['payment_notes']) ?></span>
                          <?php endif; ?>
                        </p>
                      </div>
                      <?php if ($is_admin): ?>
                        <button class="btn btn-sm btn-light border ms-2"
                          onclick="editPayment(<?= $payment['payment_id'] ?>, <?= $payment['payment_amount'] ?>, '<?= $payment['payment_method'] ?>', '<?= addslashes($payment['payment_date']) ?>', '<?= addslashes($payment['reference_number'] ?? '') ?>', '<?= addslashes($payment['gcash_number'] ?? '') ?>', '<?= addslashes($payment['payment_notes'] ?? '') ?>')">
                          <i class="fa-solid fa-pen"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <!-- NO PAYMENT RECORDS MESSAGE -->
                <div class="p-4 border rounded mb-3 bg-light text-center">
                  <i class="fa-solid fa-wallet text-muted mb-3" style="font-size: 48px;"></i>
                  <p class="fw-semibold mb-2">No Payment Records Yet</p>
                  <p class="text-muted small mb-0">
                    No payments have been recorded for this project.
                  </p>
                </div>
              <?php endif; ?>

              <!-- REMAINING BALANCE -->
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

          </div>
          <div class="card-footer bg-white">
            <div class="d-grid gap-2">
              <?php if ($is_admin): ?>
                <?php if ($remaining_balance > 0): ?>
                  <button class="btn btn-green" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                    <i class="fas fa-wallet me-1"></i> Process Payment
                  </button>
                <?php else: ?>
                  <?php if (count($payments) > 0): ?>
                    <button
                      class="btn btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#archivePaymentsModal"
                      data-project-id="<?= $project_id ?>">
                      <i class="fa fa-box-archive me-1"></i> Archive Payments Records
                    </button>
                  <?php else: ?>
                    <a href="admin-archived-payments.php?project_id=<?= $project_id ?>" class="btn btn-outline-secondary">
                      <i class="fa fa-history me-1"></i> View Archived Payments
                    </a>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>

              <?php
              // Get quotation for button (if not already fetched above)
              if (!isset($quotation)) {
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
              }
              ?>

              <a href="admin-quotation-proposal.php?id=<?= $quotation['assessment_id'] ?>&view_only=1"
                class="btn btn-light border">
                <i class="fas fa-file-invoice me-1"></i> View Quotation Details
              </a>
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

            <form action="process-payment.php" method="POST">
              <input type="hidden" name="project_id" value="<?= $project_id ?>">

              <div class="modal-body">
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

                <label class="form-label fw-semibold">Enter Payment Amount *</label>
                <input type="number" name="amount" class="form-control mb-3"
                  step="0.01" min="0.01" max="<?= $remaining_balance ?>"
                  placeholder="e.g., 5000" required>

                <label class="form-label fw-semibold">Payment Date *</label>
                <input type="date" name="payment_date" class="form-control mb-3" value="<?= date('Y-m-d') ?>" required>

                <label class="form-label fw-semibold">Payment Method *</label>
                <div class="mb-3">
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

                <!-- GCash Details Section -->
                <div id="gcashSection" class="d-none">
                  <label class="form-label fw-semibold">GCash Number *</label>
                  <input type="text" name="gcash_number" id="gcashNumberInput" class="form-control mb-3"
                    placeholder="09XXXXXXXXX" maxlength="11" pattern="[0-9]{11}">
                  <small class="text-muted d-block mb-3">Enter 11-digit GCash mobile number</small>

                  <label class="form-label fw-semibold">Reference Number *</label>
                  <input type="text" name="reference_number" id="gcashReferenceInput" class="form-control mb-3"
                    placeholder="Enter GCash reference number">
                </div>

                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2"
                  placeholder="Add payment notes..."></textarea>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="process_payment" class="btn btn-green">Confirm Payment</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

  </div>

  <!-- Add/Edit Update Modal -->
  <div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="updateModalTitle">Add Project Update</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-project-update.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">
          <input type="hidden" name="update_id" id="updateId" value="">

          <div class="modal-body">
            <label class="form-label">Update Title *</label>
            <input type="text" name="update_title" id="updateTitle"
              class="form-control mb-3" required
              placeholder="Enter update title...">

            <label class="form-label">Description *</label>
            <textarea name="update_description" id="updateDescription"
              class="form-control mb-3" rows="4" required
              placeholder="Describe the update..."></textarea>

            <label class="form-label">Image (Optional)</label>
            <input type="file" name="update_image" class="form-control mb-4"
              accept="image/jpeg,image/png,image/jpg,image/webp">

            <hr>

            <h6 class="fw-semibold mb-3">Update Project Progress</h6>
            <label class="form-label">Progress (%)</label>
            <input type="number" name="progress_percentage" id="progressPercentage"
              min="0" max="100" value="<?= $project['progress_percentage'] ?? 0 ?>"
              class="form-control">
            <small class="text-muted">Enter 0–100</small>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="save_update" class="btn btn-green">Save Update</button>
          </div>
        </form>
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

        <form action="process-payment.php" method="POST">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">

          <div class="modal-body">
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

            <label class="form-label fw-semibold">Enter Payment Amount *</label>
            <input type="number" name="amount" class="form-control mb-3"
              step="0.01" min="0.01" max="<?= $remaining_balance ?>"
              placeholder="e.g., 5000" required>

            <label class="form-label fw-semibold">Payment Date *</label>
            <input type="date" name="payment_date" class="form-control mb-3" value="<?= date('Y-m-d') ?>" required>

            <label class="form-label fw-semibold">Payment Method *</label>
            <div class="mb-3">
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

            <!-- GCash Details Section -->
            <div id="gcashSection" class="d-none">
              <label class="form-label fw-semibold">GCash Number *</label>
              <input type="text" name="gcash_number" id="gcashNumberInput" class="form-control mb-3"
                placeholder="09XXXXXXXXX" maxlength="11" pattern="[0-9]{11}">
              <small class="text-muted d-block mb-3">Enter 11-digit GCash mobile number</small>

              <label class="form-label fw-semibold">Reference Number *</label>
              <input type="text" name="reference_number" id="gcashReferenceInput" class="form-control mb-3"
                placeholder="Enter GCash reference number">
            </div>

            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="2"
              placeholder="Add payment notes..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="process_payment" class="btn btn-green">Confirm Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Update Modal -->
  <div class="modal fade" id="deleteUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Project Update</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-project-update.php" method="POST">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">
          <input type="hidden" name="delete_update_id" id="deleteUpdateId">

          <div class="modal-body">
            <h6 class="text-center py-4">Are you sure you want to delete this update?</h6>
            <p class="text-center text-muted">This action cannot be undone.</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="delete_update" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ARCHIVE PROJECT UPDATE -->
  <div class="modal fade" id="archiveUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Archive Project Update</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="process-project-update.php" method="POST">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">
          <input type="hidden" name="archive_update_id" id="archiveUpdateId">

          <div class="modal-body">
            <h6 class="text-center py-4 fs-24">Are you sure you want to archive this update?</h6>
            <p class="text-center text-muted">Archived updates can be restored later.</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="archive_update" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- ARCHIVE PAYMENTS MODAL -->
  <div class="modal fade" id="archivePaymentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-box-archive me-2"></i>
            Archive Payment Records
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive the payment records?</h3>
          <p class="text-center text-muted">Archived payments can be restored later.</p>
        </div>

        <div class="modal-footer">
          <form method="POST" action="archive-payments.php">
            <input type="hidden" name="project_id" id="archiveProjectId">

            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancel
            </button>

            <button type="submit" name="archive_payments" class="btn btn-danger">
              <i class="fa-solid fa-box-archive me-1"></i> Archive
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>



  <!-- Edit Payment Modal -->
  <div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="edit-payment.php" method="POST">
          <input type="hidden" name="project_id" value="<?= $project_id ?>">
          <input type="hidden" name="payment_id" id="editPaymentId">

          <div class="modal-body">
            <label class="form-label fw-semibold">Payment Amount *</label>
            <input type="number" name="amount" id="editAmount" class="form-control mb-3"
              step="0.01" min="0.01" required>

            <label class="form-label fw-semibold">Payment Date *</label>
            <input type="date" name="payment_date" id="editPaymentDate" class="form-control mb-3" required>

            <label class="form-label fw-semibold">Payment Method *</label>
            <div class="mb-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="payment_method"
                  value="Cash" id="editCashRadio" required>
                <label class="form-check-label" for="editCashRadio">Cash</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="payment_method"
                  value="GCash" id="editGcashRadio">
                <label class="form-check-label" for="editGcashRadio">GCash</label>
              </div>
            </div>

            <!-- GCash Details Section -->
            <div id="editGcashSection" class="d-none">
              <label class="form-label fw-semibold">GCash Number *</label>
              <input type="text" name="gcash_number" id="editGcashNumber" class="form-control mb-3"
                placeholder="09XXXXXXXXX" maxlength="11" pattern="[0-9]{11}">
              <small class="text-muted d-block mb-3">Enter 11-digit GCash mobile number</small>

              <label class="form-label fw-semibold">Reference Number *</label>
              <input type="text" name="reference_number" id="editReferenceNumber" class="form-control mb-3"
                placeholder="Enter GCash reference number">
            </div>

            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" id="editNotes" class="form-control" rows="2"
              placeholder="Add payment notes..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="edit_payment" class="btn btn-green">Update Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>
    function generateProjectReport(projectId) {
      const url = `admin-project-progress-report.php?id=${projectId}&auto=1`;
      const reportWindow = window.open(url, '_blank', 'width=1200,height=800');

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

            const filename = 'Project_Report_<?= date("Y-m-d") ?>.pdf';
            pdf.save(filename);

            reportWindow.close();
          } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
            reportWindow.close();
          }
        }, 1500);
      });
    }
    // Process Payment Modal - Payment method toggle
    document.querySelectorAll("#processPaymentModal input[name='payment_method']").forEach(radio => {
      radio.addEventListener("change", function() {
        const gcashSection = document.getElementById("gcashSection");
        const gcashNumberInput = document.getElementById("gcashNumberInput");
        const gcashReferenceInput = document.getElementById("gcashReferenceInput");

        if (this.value === "GCash") {
          gcashSection.classList.remove("d-none");
          gcashNumberInput.setAttribute('required', 'required');
          gcashReferenceInput.setAttribute('required', 'required');
        } else {
          gcashSection.classList.add("d-none");
          gcashNumberInput.removeAttribute('required');
          gcashReferenceInput.removeAttribute('required');
          gcashNumberInput.value = '';
          gcashReferenceInput.value = '';
        }
      });
    });

    // Edit Payment Modal - Payment method toggle
    document.querySelectorAll("#editPaymentModal input[name='payment_method']").forEach(radio => {
      radio.addEventListener("change", function() {
        const editGcashSection = document.getElementById("editGcashSection");
        const editGcashNumber = document.getElementById("editGcashNumber");
        const editReferenceNumber = document.getElementById("editReferenceNumber");

        if (this.value === "GCash") {
          editGcashSection.classList.remove("d-none");
          editGcashNumber.setAttribute('required', 'required');
          editReferenceNumber.setAttribute('required', 'required');
        } else {
          editGcashSection.classList.add("d-none");
          editGcashNumber.removeAttribute('required');
          editReferenceNumber.removeAttribute('required');
        }
      });
    });

    document.getElementById('gcashNumberInput')?.addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('editGcashNumber')?.addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('processPaymentModal')?.addEventListener('hidden.bs.modal', function() {
      document.querySelector('form[action="process-payment.php"]').reset();
      document.getElementById("gcashSection").classList.add("d-none");
    });

    // Edit update function
    function editUpdate(id, title, description, progress) {
      document.getElementById('updateModalTitle').textContent = 'Edit Project Update';
      document.getElementById('updateId').value = id;
      document.getElementById('updateTitle').value = title;
      document.getElementById('updateDescription').value = description;
      document.getElementById('progressPercentage').value = progress;

      const modal = new bootstrap.Modal(document.getElementById('addUpdateModal'));
      modal.show();
    }

    // Delete update function
    function deleteUpdate(id) {
      document.getElementById('deleteUpdateId').value = id;
      const modal = new bootstrap.Modal(document.getElementById('deleteUpdateModal'));
      modal.show();
    }

    // Reset modal on close
    document.getElementById('processPaymentModal')?.addEventListener('hidden.bs.modal', function() {
      document.querySelector('#processPaymentModal form').reset();
      document.getElementById("gcashSection").classList.add("d-none");
    });

    // Edit payment function
    function editPayment(id, amount, method, date, reference, gcashNumber, notes) {
      document.getElementById('editPaymentId').value = id;
      document.getElementById('editAmount').value = amount;
      document.getElementById('editPaymentDate').value = date;
      document.getElementById('editNotes').value = notes || '';

      // Set payment method
      if (method === 'Cash') {
        document.getElementById('editCashRadio').checked = true;
        document.getElementById('editGcashSection').classList.add('d-none');
      } else if (method === 'GCash') {
        document.getElementById('editGcashRadio').checked = true;
        document.getElementById('editGcashSection').classList.remove('d-none');
        document.getElementById('editGcashNumber').value = gcashNumber || '';
        document.getElementById('editReferenceNumber').value = reference || '';
        document.getElementById('editGcashNumber').setAttribute('required', 'required');
        document.getElementById('editReferenceNumber').setAttribute('required', 'required');
      }

      const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
      modal.show();
    }

    function openArchivePaymentsModal(projectId) {
      document.getElementById('archiveProjectId').value = projectId;

      const modal = new bootstrap.Modal(
        document.getElementById('archivePaymentsModal')
      );
      modal.show();
    }

    // Archive Payments Modal - Set project ID when modal opens
    document.getElementById('archivePaymentsModal')?.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const projectId = button?.getAttribute('data-project-id') || <?= $project_id ?>;
      document.getElementById('archiveProjectId').value = projectId;
    });

    // Show messages
    <?php if (isset($_SESSION['success'])): ?>
      alert('<?= addslashes($_SESSION['success']) ?>');
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      alert('<?= addslashes($_SESSION['error']) ?>');
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>


    function archiveUpdate(id) {
      document.getElementById('archiveUpdateId').value = id;
      const modal = new bootstrap.Modal(document.getElementById('archiveUpdateModal'));
      modal.show();
    }
  </script>

</body>

</html>