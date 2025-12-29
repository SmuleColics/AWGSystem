<?php
include 'user-header.php';

// Get quotation ID from URL
$quotation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quotation_id === 0) {
  header('Location: user-assessments.php');
  exit;
}

// Fetch quotation details with assessment and user information
$sql = "SELECT q.*, a.assessment_id, a.service_type, a.preferred_date, a.preferred_time, 
        a.estimated_budget, a.notes as assessment_notes,
        u.first_name, u.last_name, u.email, u.phone, 
        u.house_no, u.brgy, u.city, u.province, u.zip_code
        FROM quotations q
        LEFT JOIN assessments a ON q.assessment_id = a.assessment_id
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE q.quotation_id = $quotation_id AND a.user_id = $user_id";

$result = mysqli_query($conn, $sql);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
  header('Location: user-assessments.php');
  exit;
}

$project_sql = "SELECT p.project_id 
                FROM projects p
                WHERE p.quotation_id = $quotation_id
                AND p.user_id = $user_id
                AND p.is_archived = 0
                LIMIT 1";
$project_result = mysqli_query($conn, $project_sql);
$project_data = mysqli_fetch_assoc($project_result);

// Fetch quotation items
$items_sql = "SELECT * FROM quotation_items WHERE assessment_id = {$quotation['assessment_id']} ORDER BY created_at ASC";
$items_result = mysqli_query($conn, $items_sql);
$quotation_items = [];
if ($items_result) {
  while ($row = mysqli_fetch_assoc($items_result)) {
    $quotation_items[] = $row;
  }
}

// Fetch labor charges
$labor_sql = "SELECT * FROM quotation_labor WHERE assessment_id = {$quotation['assessment_id']} ORDER BY created_at ASC";
$labor_result = mysqli_query($conn, $labor_sql);
$labor_charges = [];
if ($labor_result) {
  while ($row = mysqli_fetch_assoc($labor_result)) {
    $labor_charges[] = $row;
  }
}

// Calculate totals
$items_total = 0;
$labor_total = 0;

foreach ($quotation_items as $item) {
  $items_total += $item['total'];
}

foreach ($labor_charges as $labor) {
  $labor_total += $labor['amount'];
}

$grand_total = $items_total + $labor_total;

// Format data
$user_full_name = $quotation['first_name'] . ' ' . $quotation['last_name'];

// Build full address
$address_parts = array_filter([
  $quotation['house_no'],
  $quotation['brgy'],
  $quotation['city'],
  $quotation['province'],
  $quotation['zip_code']
]);
$full_address = !empty($address_parts) ? implode(', ', $address_parts) : 'Address not provided';

$formatted_date = date('m/d/Y', strtotime($quotation['preferred_date']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>View Quotation - A We Green Enterprise</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-responsiveness.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .nav-assessment {
      color: #fff !important;
    }

    .quotation-header-card {
      background: linear-gradient(135deg, #16A249 0%, #0d7a35 100%);
      color: white;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .info-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      height: 100%;
    }

    .info-label {
      font-size: 12px;
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 5px;
    }

    .info-value {
      font-size: 16px;
      color: #212529;
      font-weight: 500;
    }

    .quotation-item-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }

    .quotation-item-card:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .labor-item-card {
      background: #f8f9fa;
      border-left: 4px solid #16A249;
      border-radius: 8px;
      padding: 15px 20px;
      margin-bottom: 10px;
    }

    .total-section {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .grand-total {
      background: linear-gradient(135deg, #16A249 0%, #0d7a35 100%);
      color: white;
      padding: 20px;
      border-radius: 10px;
    }

    .section-title {
      font-size: 20px;
      font-weight: 600;
      color: #212529;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #16A249;
    }

    .print-button {
      background: white;
      color: #16A249;
      border: 2px solid white;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .questions-quotation,
    .total-container {
      border-top: 2px solid #16A249;
    }
  </style>
</head>

<body class="bg-light">
  <main class="container-xxl text-dark px-4 min-vh-100">

    <!-- BACK BUTTON -->
    <div class="d-flex justify-content-between align-items-center" style=" padding-top: 22px;">
      <a href="user-assessments.php" class="btn btn-outline-secondary mb-3 no-print">
        <i class="fa fa-arrow-left me-2"></i> Back to Assessments
      </a>
      <a href="user-project-monitoring.php?id=<?= $project_data['project_id'] ?>" class="btn btn-green mb-3">
        <i class="fas fa-chart-line me-2"></i> View Project Monitoring</a>
    </div>

    <!-- QUOTATION HEADER -->
    <div class="quotation-header-card">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <h1 class="fs-28 mb-2 fw-bold">Quotation Proposal</h1>
          <p class="mb-1 opacity-75">
            <i class="fas fa-file-invoice me-2"></i>
            Quotation ID: #<?= str_pad($quotation_id, 5, '0', STR_PAD_LEFT) ?>
          </p>
          <p class="mb-0 opacity-75">
            <i class="fas fa-calendar me-2"></i>
            Created: <?= date('F d, Y', strtotime($quotation['created_at'])) ?>
          </p>
        </div>

      </div>
    </div>

    <!-- PROJECT & CLIENT INFO -->
    <div class="row g-3 mb-4">
      <!-- Project Details -->
      <div class="col-md-6">
        <div class="info-card">
          <h3 class="section-title">
            <i class="fas fa-project-diagram me-2"></i>Project Details
          </h3>

          <div class="mb-3">
            <div class="info-label">Project Name</div>
            <div class="info-value"><?= htmlspecialchars($quotation['project_name']) ?></div>
          </div>

          <div class="mb-3">
            <div class="info-label">Category / Service Type</div>
            <div class="info-value"><?= htmlspecialchars($quotation['category']) ?></div>
          </div>

          <div class="mb-3">
            <div class="info-label">Client Budget</div>
            <div class="info-value">
              <?= !empty($quotation['estimated_budget'])
                ? '₱' . number_format($quotation['estimated_budget'], 2)
                : 'Not provided'; ?>
            </div>
          </div>


          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <div class="info-label">Start Date</div>
                <div class="info-value">
                  <?= $quotation['start_date'] ? date('M d, Y', strtotime($quotation['start_date'])) : 'TBD' ?>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <div class="info-label">End Date</div>
                <div class="info-value">
                  <?= $quotation['end_date'] ? date('M d, Y', strtotime($quotation['end_date'])) : 'TBD' ?>
                </div>
              </div>
            </div>
          </div>

          <?php if (!empty($quotation['notes'])): ?>
            <div class="mb-0">
              <div class="info-label">Project Notes</div>
              <div class="info-value"><?= nl2br(htmlspecialchars($quotation['notes'])) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Client Information -->
      <div class="col-md-6">
        <div class="info-card">
          <h3 class="section-title">
            <i class="fas fa-user me-2"></i>Client Information
          </h3>

          <div class="mb-3">
            <div class="info-label">Client Name</div>
            <div class="info-value"><?= htmlspecialchars($user_full_name) ?></div>
          </div>

          <div class="mb-3">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?= htmlspecialchars($quotation['email']) ?></div>
          </div>

          <div class="mb-3">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?= htmlspecialchars($quotation['phone']) ?></div>
          </div>

          <div class="mb-3">
            <div class="info-label">Service Location</div>
            <div class="info-value"><?= htmlspecialchars($full_address) ?></div>
          </div>

          <?php if (!empty($quotation['estimated_budget'])): ?>
            <div class="mb-0">
              <div class="info-label">Your Estimated Budget</div>
              <div class="info-value">₱<?= number_format($quotation['estimated_budget'], 2) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- QUOTATION ITEMS -->
    <div class="bg-white rounded-3 p-4 mb-4">
      <h3 class="section-title">
        <i class="fas fa-boxes me-2"></i>Quotation Items
      </h3>

      <?php if (count($quotation_items) > 0): ?>
        <?php foreach ($quotation_items as $index => $item): ?>
          <div class="quotation-item-card">
            <div class="row align-items-center">
              <div class="col-md-5">
                <div class="info-label">Item #<?= $index + 1 ?></div>
                <div class="info-value fs-18"><?= htmlspecialchars($item['item_name']) ?></div>
              </div>
              <div class="col-md-2 text-center">
                <div class="info-label">Quantity</div>
                <div class="info-value"><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_type']) ?></div>
              </div>
              <div class="col-md-2 text-center">
                <div class="info-label">Unit Price</div>
                <div class="info-value">₱<?= number_format($item['unit_price'], 2) ?></div>
              </div>
              <div class="col-md-3 text-end">
                <div class="info-label">Total</div>
                <div class="info-value fs-20 fw-bold green-text">₱<?= number_format($item['total'], 2) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center py-4">
          <i class="fas fa-box-open fa-3x light-text mb-3"></i>
          <p class="light-text">No items in this quotation</p>
        </div>
      <?php endif; ?>

      <!-- LABOR CHARGES -->
      <?php if (count($labor_charges) > 0): ?>
        <h4 class="mt-4 mb-3 fs-20 fw-semibold">
          <i class="fas fa-wrench me-2"></i>Labor & Additional Charges
        </h4>
        <?php foreach ($labor_charges as $labor): ?>
          <div class="labor-item-card">
            <div class="row align-items-center">
              <div class="col-md-8">
                <div class="info-label">Service Description</div>
                <div class="info-value"><?= htmlspecialchars($labor['description']) ?></div>
              </div>
              <div class="col-md-4 text-end">
                <div class="info-label">Amount</div>
                <div class="info-value fs-18 fw-bold">₱<?= number_format($labor['amount'], 2) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <div class="row mb-3 mt-3 pt-3 total-container">
        <!-- LEFT SIDE: SUBTOTALS -->
        <div class="d-flex justify-content-between align-items-center">
          <div class="col-md-6">
            <div class="mb-2">
              <span class="info-label">Items Subtotal:</span>
              <span class="info-value fs-18">
                ₱<?= number_format($items_total, 2) ?>
              </span>
            </div>

            <?php if ($labor_total > 0): ?>
              <div class="mb-2">
                <span class="info-label">Labor & Services:</span>
                <span class="info-value fs-18">
                  ₱<?= number_format($labor_total, 2) ?>
                </span>
              </div>
            <?php endif; ?>
          </div>

          <!-- RIGHT SIDE: GRAND TOTAL -->
          <div class="col-md-6">
            <div class="grand-total">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div style="font-size: 14px; opacity: 0.9;">Total Amount</div>
                  <div class="fs-28 fw-bold">
                    ₱<?= number_format($grand_total, 2) ?>
                  </div>
                </div>
                <i class="fas fa-file-invoice-dollar fa-3x opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if ($quotation['estimated_budget'] > 0): ?>
        <div class="mt-3 p-3 bg-light rounded">
          <div class="d-flex justify-content-between">
            <span class="light-text">Your Budget:</span>
            <span class="fw-semibold">
              ₱<?= number_format($quotation['estimated_budget'], 2) ?>
            </span>
          </div>

          <?php
          $difference = $grand_total - $quotation['estimated_budget'];
          $percentage = ($grand_total / $quotation['estimated_budget']) * 100;
          ?>

          <div class="d-flex justify-content-between mt-2">
            <span class="light-text">Difference:</span>
            <span class="fw-semibold <?= $difference > 0 ? 'text-danger' : 'green-text' ?>">
              <?= $difference > 0 ? '+' : '' ?>
              ₱<?= number_format(abs($difference), 2) ?>
              (<?= number_format($percentage, 1) ?>%)
            </span>
          </div>
        </div>
      <?php endif; ?>

    </div>


    <!-- TERMS & CONDITIONS -->
    <div class="bg-white rounded-3 p-4 mb-4">
      <h3 class="section-title">
        <i class="fas fa-file-contract me-2"></i>Terms & Conditions
      </h3>
      <div class="row">
        <div class="col-md-12">
          <ul class="list-unstyled">
            <li class="mb-2">
              <i class="fas fa-check-circle green-text me-2"></i>
              This quotation is valid for 30 days from the date of issue
            </li>
            <li class="mb-2">
              <i class="fas fa-check-circle green-text me-2"></i>
              Prices are subject to change based on material availability
            </li>
            <li class="mb-2">
              <i class="fas fa-check-circle green-text me-2"></i>
              Payment terms: 50% down payment, 50% upon project completion
            </li>
            <li class="mb-2">
              <i class="fas fa-check-circle green-text me-2"></i>
              Project timeline may vary based on weather conditions and material delivery
            </li>
            <li class="mb-2">
              <i class="fas fa-check-circle green-text me-2"></i>
              Warranty terms shall take effect upon the completion of the project.
            </li>

          </ul>
        </div>
      </div>
      <div class="mt-2 bg-white text-center questions-quotation">
        <p class="mb-2 fw-semibold pt-2">
          <i class="fas fa-info-circle green-text me-2"></i>Questions about this quotation?
        </p>
        <p class="mb-0 light-text">
          Contact us at <a href="mailto:info@awegreen.com" class="green-text">awegreenenterprise@gmail.com</a>
          or call us at <a href="tel:+639123456789" class="green-text"> 0917 752 3343 </a>
        </p>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>