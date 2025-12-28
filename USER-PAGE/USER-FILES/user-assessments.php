<?php
include 'user-header.php';

// Fetch all assessments for this user with complete user information from users table
$sql = "SELECT a.*, 
        u.first_name, u.last_name, u.email, u.phone,
        u.house_no, u.brgy, u.city, u.province, u.zip_code,
        e1.first_name as employee1_first_name, e1.last_name as employee1_last_name,
        e2.first_name as employee2_first_name, e2.last_name as employee2_last_name
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        LEFT JOIN employees e1 ON a.assigned_to_id = e1.employee_id
        LEFT JOIN employees e2 ON a.assigned_to_id_2 = e2.employee_id
        WHERE a.user_id = $user_id AND a.is_archived = 0
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
$assessments = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $assessments[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>My Assessments - A We Green Enterprise</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-responsiveness.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .nav-assessment {
      color: #fff !important;
    }

    .assessment-status-badge {
      font-size: 12px;
      padding: 4px 12px;
      border-radius: 12px;
      font-weight: 500;
    }

    .assessment-completed-badge {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .assessment-pending-badge {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .assigned-team-badge {
      display: inline-block;
      background-color: #e7f3ff;
      color: #004085;
      padding: 3px 8px;
      border-radius: 8px;
      font-size: 12px;
      margin-right: 4px;
      margin-bottom: 4px;
    }

    .rejection-reason-box {
      background-color: #f8d7da;
      border: 1px solid #f5c2c7;
      border-radius: 8px;
      padding: 12px;
      margin-top: 8px;
    }

    .rejection-reason-box i {
      color: #842029;
    }

    .info-label {
      font-weight: 500;
      color: #6c757d;
    }
  </style>
</head>

<body class="bg-light">
  <main class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center gap-4 flex-wrap">
      <div>
        <h1 class="fs-36 mobile-fs-32">My Assessment Requests</h1>
        <p class="admin-top-desc">View the status of your assessment requests and updates</p>
      </div>
      <div>
        <a href="request-assessment.php" class="btn green-bg text-white add-item-btn">
          <i class="fa-solid fa-calendar-check me-1"></i> Schedule Assessment
        </a>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4 d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Assessment Requests (<?= count($assessments) ?>)</h2>
            <div class="d-flex gap-2 flex-wrap">
              <div>
                <select id="serviceFilter" class="form-select">
                  <option value="all">All Services</option>
                  <option value="cctv">CCTV</option>
                  <option value="solar">Solar</option>
                  <option value="renovation">Renovation</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <select id="assessmentFilter" class="form-select">
                  <option value="all">Show All</option>
                  <option value="Pending">Pending</option>
                  <option value="Accepted">Accepted</option>
                  <option value="Rejected">Rejected</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>
          </div>

          <div class="px-4 pb-4 d-flex flex-column gap-4">

            <?php if (empty($assessments)): ?>
              <!-- Empty State -->
              <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x light-text mb-3"></i>
                <p class="light-text mb-2 fs-18">No assessment requests found</p>
                <p class="light-text mb-3">Ready to get started? Schedule your first assessment with us!</p>
                <a href="request-assessment.php" class="btn btn-green">
                  <i class="fa-solid fa-calendar-check me-2"></i> Schedule Your First Assessment
                </a>
              </div>
            <?php else: ?>
              <!-- Empty State (for filtering) -->
              <div id="assessmentEmptyState" class="text-center py-5 d-none">
                <i class="fas fa-clipboard-list fa-3x light-text mb-3"></i>
                <p class="light-text">No assessments found for this filter</p>
              </div>

              <!-- Assessment Cards Container -->
              <div id="assessmentCardsContainer">
                <?php foreach ($assessments as $assessment): ?>
                  <?php
                  // Format user information from users table
                  $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];

                  // Build full address from users table fields
                  $address_parts = array_filter([
                    $assessment['house_no'],
                    $assessment['brgy'],
                    $assessment['city'],
                    $assessment['province'],
                    $assessment['zip_code']
                  ]);
                  $full_address = !empty($address_parts) ? implode(', ', $address_parts) : 'Address not provided';

                  // Short location (city, province only)
                  $location = trim($assessment['city'] . ', ' . $assessment['province']);
                  if (empty($location) || $location === ',') {
                    $location = 'Location not specified';
                  }

                  $formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));

                  $assigned_employees = [];
                  if ($assessment['assigned_to_id']) {
                    $assigned_employees[] = $assessment['employee1_first_name'] . ' ' . $assessment['employee1_last_name'];
                  }
                  if ($assessment['assigned_to_id_2']) {
                    $assigned_employees[] = $assessment['employee2_first_name'] . ' ' . $assessment['employee2_last_name'];
                  }

                  // Status badge classes
                  $statusClass = match ($assessment['status']) {
                    "Pending"      => "badge-pill taskstatus-pending",
                    "Accepted"     => "badge-pill taskstatus-inprogress",
                    "Completed"    => "badge-pill taskstatus-completed",
                    "Rejected"     => "badge-pill priority-high",
                    default        => "badge-pill"
                  };
                  ?>

                  <div class="assessment-con d-flex flex-md-row flex-column border p-3 rounded-3 gap-4 mb-3"
                    data-status="<?= htmlspecialchars($assessment['status']) ?>"
                    data-service="<?= strtolower(str_replace(' ', '_', $assessment['service_type'])) ?>">

                    <div class="w-100">
                      <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <h3 class="fs-18 mb-0 fw-bold">
                          <?= htmlspecialchars($assessment['service_type']) ?> Assessment
                        </h3>
                        <span class="<?= $statusClass ?>"><?= htmlspecialchars($assessment['status']) ?></span>

                        <?php if ($assessment['status'] === 'Accepted'): ?>
                          <?php if ($assessment['assessment_completed']): ?>
                            <span class="assessment-status-badge assessment-completed-badge">
                              <i class="fas fa-check-circle me-1"></i>Assessment Done
                            </span>
                          <?php else: ?>
                            <span class="assessment-status-badge assessment-pending-badge">
                              <i class="fas fa-clock me-1"></i>Assessment Pending
                            </span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>

                      <div class="row mt-2">
                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="info-label">Service Type:</span>
                            <span class="ms-1"><?= htmlspecialchars($assessment['service_type']) ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Preferred Date:</span>
                            <span class="ms-1"><?= $formatted_date ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Preferred Time:</span>
                            <span class="ms-1"><?= htmlspecialchars($assessment['preferred_time']) ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Assessment Location:</span>
                            <span class="ms-1"><?= htmlspecialchars($full_address) ?></span>
                          </p>
                          <?php if (!empty($assessment['estimated_budget'])): ?>
                            <p class="fs-14 mb-2">
                              <span class="info-label">Estimated Budget:</span>
                              <span class="ms-1">₱<?= number_format($assessment['estimated_budget'], 2) ?></span>
                            </p>
                          <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="info-label">Contact Person:</span>
                            <span class="ms-1"><?= htmlspecialchars($user_full_name) ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Phone:</span>
                            <span class="ms-1"><?= htmlspecialchars($assessment['phone']) ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Email:</span>
                            <span class="ms-1"><?= htmlspecialchars($assessment['email']) ?></span>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="info-label">Requested On:</span>
                            <span class="ms-1"><?= date('M d, Y h:i A', strtotime($assessment['created_at'])) ?></span>
                          </p>
                          <?php if ($assessment['assessment_completed'] && $assessment['assessment_completed_at']): ?>
                            <p class="fs-14 mb-2">
                              <span class="info-label">Completed On:</span>
                              <span class="ms-1"><?= date('M d, Y h:i A', strtotime($assessment['assessment_completed_at'])) ?></span>
                            </p>
                          <?php endif; ?>

                          <?php if (!empty($assigned_employees)): ?>
                            <p class="fs-14 mb-2">
                              <span class="info-label">Assigned Team:</span><br>
                              <span class="ms-1">
                                <?php foreach ($assigned_employees as $emp_name): ?>
                                  <span class="assigned-team-badge">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($emp_name) ?>
                                  </span>
                                <?php endforeach; ?>
                              </span>
                            </p>
                          <?php endif; ?>
                        </div>
                      </div>

                      <?php if (!empty($assessment['notes'])): ?>

                        <p class="fs-14 mb-1 light-text fw-semibold">
                          Your Notes:
                        </p>
                        <p class="fs-14 mb-0">
                          <?= nl2br(htmlspecialchars($assessment['notes'])) ?>
                        </p>

                      <?php endif; ?>

                      <?php if ($assessment['status'] === 'Rejected' && !empty($assessment['rejection_reason'])): ?>
                        <div class="rejection-reason-box mt-2">
                          <p class="fs-14 mb-1 fw-semibold">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Rejection Reason:
                          </p>
                          <p class="fs-14 mb-0 ps-4">
                            <?= nl2br(htmlspecialchars($assessment['rejection_reason'])) ?>
                          </p>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="assessment-actions d-flex flex-column gap-2" style="min-width: 180px;">
                      <?php
                      // Check if quotation exists and its status
                      $quotation_check_sql = "SELECT quotation_id, status FROM quotations WHERE assessment_id = {$assessment['assessment_id']}";
                      $quotation_check_result = mysqli_query($conn, $quotation_check_sql);
                      $quotation_check = mysqli_fetch_assoc($quotation_check_result);
                      $quotation_status = $quotation_check ? $quotation_check['status'] : null;
                      $quotation_id = $quotation_check ? $quotation_check['quotation_id'] : null;
                      ?>

                      <?php if ($assessment['status'] === 'Pending'): ?>
                        <!-- Pending Status -->
                        <div class="alert alert-info mb-0 p-2 text-center">
                          <i class="fas fa-clock me-1"></i>
                          <small>Awaiting Review</small>
                        </div>

                      <?php elseif ($assessment['status'] === 'Accepted'): ?>
                        <!-- Accepted Status -->
                        <?php if (!$assessment['assessment_completed']): ?>
                          <div class="alert alert-warning mb-0 p-2 text-center">
                            <i class="fas fa-hourglass-half me-1"></i>
                            <small>Assessment Scheduled</small>
                          </div>
                        <?php elseif ($quotation_status === 'Sent'): ?>
                          <!-- Quotation is available -->
                          <a href="user-view-quotation.php?id=<?= $quotation_id ?>"
                            class="btn btn-green border flex w-100">
                            <i class="fas fa-file-invoice me-1"></i>
                            View Quotation
                          </a>
                        <?php else: ?>
                          <div class="alert alert-info mb-0 p-2 text-center">
                            <i class="fas fa-file-alt me-1"></i>
                            <small>Quotation Being Prepared</small>
                          </div>
                        <?php endif; ?>

                      <?php elseif ($assessment['status'] === 'Completed'): ?>
                        <!-- Completed Status -->
                        <a href="user-view-quotation.php?id=<?= $quotation_id ?>"
                          class="btn btn-green border flex w-100">
                          <i class="fas fa-file-invoice me-1"></i>
                          View Quotation
                        </a>

                      <?php elseif ($assessment['status'] === 'Rejected'): ?>
                        <!-- Rejected Status -->
                        <div class="alert alert-danger mb-2 p-2 text-center">
                          <i class="fas fa-times-circle me-1"></i>
                          <small>Request Rejected</small>
                        </div>
                        <a href="request-assessment.php" class="btn btn-outline-success border flex w-100">
                          <i class="fas fa-redo me-1"></i>
                          Request New Assessment
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>

                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const statusFilter = document.getElementById('assessmentFilter');
      const serviceFilter = document.getElementById('serviceFilter');
      const assessmentCons = document.querySelectorAll('.assessment-con');
      const emptyStateMessage = document.getElementById('assessmentEmptyState');
      const cardsContainer = document.getElementById('assessmentCardsContainer');

      function applyFilters() {
        const selectedStatus = statusFilter.value;
        const selectedService = serviceFilter.value;
        let visibleCount = 0;

        assessmentCons.forEach(card => {
          const cardStatus = card.dataset.status;
          const cardService = card.dataset.service;

          const statusMatch = selectedStatus === 'all' || cardStatus === selectedStatus;
          const serviceMatch = selectedService === 'all' || cardService.includes(selectedService);

          if (statusMatch && serviceMatch) {
            card.classList.remove('d-none');
            visibleCount++;
          } else {
            card.classList.add('d-none');
          }
        });

        // Toggle empty state and cards container
        if (emptyStateMessage && cardsContainer) {
          if (visibleCount === 0) {
            emptyStateMessage.classList.remove('d-none');
            cardsContainer.classList.add('d-none');
          } else {
            emptyStateMessage.classList.add('d-none');
            cardsContainer.classList.remove('d-none');
          }
        }
      }

      statusFilter.addEventListener('change', applyFilters);
      serviceFilter.addEventListener('change', applyFilters);
    });
  </script>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>