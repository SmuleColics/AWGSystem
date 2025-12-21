<?php
include 'admin-header.php';

// Fetch all assessments with user information
$sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, 
        u.house_no, u.brgy, u.city, u.province, u.zip_code
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE is_archived = 0
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
$assessments = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $assessments[] = $row;
  }
}

// HANDLE ARCHIVE ASSESSMBENT (INLINE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_assessment'])) {
  $assessment_id = intval($_POST['assessment_id']);

  $archive_sql = "
    UPDATE assessments 
    SET is_archived = 1
    WHERE assessment_id = $assessment_id
  ";

  if (mysqli_query($conn, $archive_sql)) {

    // 🔹 ACTIVITY LOG
    log_activity(
      $conn,
      $employee_id,
      $employee_name,
      'ARCHIVE',
      'ASSESSMENTS',
      $assessment_id,
      $service_type,
      "Archived assessment for $user_full_name | Service: $service_type"
    );

    echo "<script>
        alert('Assessment archived successfully.');
        window.location.href = 'admin-assessments.php';
      </script>";
    exit;
  } else {
    echo "<script>
      alert('Error archiving assessment.');
    </script>";
  }
}

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
    .sidebar-content-item:nth-child(4) {
      background-color: #f2f2f2 !important;
    }

    .sidebar-content-item:nth-child(4) .sidebar-anchor,
    .sidebar-content-item:nth-child(4) .sidebar-anchor span {
      color: #16A249 !important;
    }
  </style>
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center gap-4">

      <div>
        <h1 class="fs-36 mobile-fs-32">Assessments Request</h1>
        <p class="admin-top-desc">Manage customer assessment requests and create quotations</p>
      </div>
      <div>
        <a href="admin-archive-assessment.php" class="btn btn-danger text-white d-flex align-items-center">
          <i class="fa-solid fa-box-archive me-1"></i> Archived <span class="d-none d-md-block ms-1">Assessments</span>
        </a>
      </div>

    </div>

    <div class="row g-3 mb-4">

      <div class="col-12">
        <div class="assessment-container rounded-3 bg-white">
          <div class="assessment-top p-4 d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
            <h2 class="fs-24 mobile-fs-22 mb-0">All Assessments Requests (<?= count($assessments) ?>)</h2>
            <div class="d-flex gap-2">
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
              <!-- Empty State (shown on initial page load when no assessments exist) -->
              <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No assessment requests found</p>
              </div>
            <?php else: ?>
              <!-- Empty State (shown when filtering returns no results) -->
              <div id="assessmentEmptyState" class="text-center py-5 d-none">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No assessments found for this status</p>
              </div>

              <!-- Assessment Cards Container -->
              <div id="assessmentCardsContainer">
                <?php foreach ($assessments as $assessment): ?>
                  <?php
                  $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
                  $location = trim($assessment['city'] . ', ' . $assessment['province']);
                  $formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));

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
                      <div class="d-flex align-items-center gap-3 mb-2">
                        <h3 class="fs-18 mb-0">
                          <?= htmlspecialchars($user_full_name) ?>
                          <span class="fs-14 light-text">(<?= htmlspecialchars($assessment['email']) ?>)</span>
                        </h3>
                        <span class="<?= $statusClass ?>"><?= htmlspecialchars($assessment['status']) ?></span>
                      </div>

                      <div class="row mt-1">
                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="light-text">Service: </span>
                            <?= htmlspecialchars($assessment['service_type']) ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Time: </span>
                            <?= htmlspecialchars($assessment['preferred_time']) ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Location: </span>
                            <?= htmlspecialchars($location) ?>
                          </p>
                          <?php if (!empty($assessment['notes'])): ?>
                            <p class="fs-14 mb-0">
                              <span class="light-text">Notes: </span><br />
                              <?= htmlspecialchars($assessment['notes']) ?>
                            </p>
                          <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                          <p class="fs-14 mb-2">
                            <span class="light-text">Date: </span>
                            <?= $formatted_date ?>
                          </p>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Phone: </span>
                            <?= htmlspecialchars($assessment['phone']) ?>
                          </p>
                          <?php if (!empty($assessment['estimated_budget'])): ?>
                            <p class="fs-14 mb-2">
                              <span class="light-text">Estimated Budget: </span>
                              ₱<?= number_format($assessment['estimated_budget'], 2) ?>
                            </p>
                          <?php endif; ?>
                          <p class="fs-14 mb-2">
                            <span class="light-text">Requested: </span>
                            <?= date('M d, Y h:i A', strtotime($assessment['created_at'])) ?>
                          </p>
                        </div>
                      </div>

                    </div>

                    <div class="assessment-actions d-flex flex-column gap-2">
                      <?php
                      // Check if quotation exists and its status
                      $quotation_check_sql = "SELECT status FROM quotations WHERE assessment_id = {$assessment['assessment_id']}";
                      $quotation_check_result = mysqli_query($conn, $quotation_check_sql);
                      $quotation_check = mysqli_fetch_assoc($quotation_check_result);
                      $quotation_status = $quotation_check ? $quotation_check['status'] : null;
                      ?>

                      <?php if ($is_admin): ?>
                        <!-- ADMIN USER ACTIONS -->

                        <?php if ($assessment['status'] === 'Pending'): ?>
                          <!-- Pending Status Actions -->
                          <form method="POST" action="accept-assessment.php" style="margin: 0;">
                            <input type="hidden" name="assessment_id" value="<?= $assessment['assessment_id'] ?>">
                            <button type="submit" class="btn btn-green flex w-100">
                              <i class="fas fa-check-circle me-1"></i>
                              Accept
                            </button>
                          </form>

                          <button
                            class="btn btn-danger border flex"
                            data-bs-toggle="modal"
                            data-bs-target="#rejectAssessmentModal"
                            onclick="setRejectAssessmentId(<?= $assessment['assessment_id'] ?>)">
                            <i class="fas fa-times-circle me-1"></i>
                            Reject
                          </button>

                        <?php elseif ($assessment['status'] === 'Accepted'): ?>
                          <!-- Accepted Status Actions for Admin -->
                          <?php if ($quotation_status === 'Sent'): ?>
                            <!-- Quotation is completed/sent -->
                            <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                              class="btn btn-success border flex">
                              <i class="fas fa-file-invoice me-1"></i>
                              Manage Quotation
                            </a>
                          <?php else: ?>
                            <!-- Quotation not yet completed -->
                            <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                              class="btn btn-green border flex">
                              <i class="fas fa-plus me-1"></i>
                              Create Quotation
                            </a>
                          <?php endif; ?>

                        <?php elseif ($assessment['status'] === 'Completed'): ?>
                          <!-- Completed Status Actions for Admin -->
                          <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>"
                            class="btn btn-success border flex">
                            <i class="fas fa-file-invoice me-1"></i>
                            Manage Quotation
                          </a>
                        <?php endif; ?>

                        <!-- Archive button for all statuses (Admin only) -->
                        <button
                          type="button"
                          class="btn btn-light border flex w-100"
                          data-bs-toggle="modal"
                          data-bs-target="#archiveAssessmentModal"
                          onclick="setArchiveAssessmentId(<?= $assessment['assessment_id'] ?>)">
                          <i class="fa-solid fa-box-archive me-1"></i>
                          Archive
                        </button>

                      <?php else: ?>
                        <!-- NON-ADMIN USERS (Employees) -->

                        <?php if ($assessment['status'] === 'Pending'): ?>
                          <!-- Show nothing or a disabled button for pending -->
                          <button class="btn btn-secondary border flex w-100" disabled>
                            <i class="fas fa-clock me-1"></i>
                            Pending Approval
                          </button>

                        <?php elseif ($assessment['status'] === 'Accepted'): ?>
                          <?php if ($quotation_status === 'Sent'): ?>
                            <!-- Quotation is sent - employees can only view -->
                            <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>&view_only=1"
                              class="btn btn-green border flex">
                              <i class="fa-solid fa-eye me-1"></i>
                              View Quotation
                            </a>
                          <?php else: ?>
                            <!-- No quotation yet -->
                            <button class="btn btn-secondary border flex w-100" disabled>
                              <i class="fas fa-hourglass-half me-1"></i>
                              Quotation Pending
                            </button>
                          <?php endif; ?>

                        <?php elseif ($assessment['status'] === 'Completed'): ?>
                          <!-- Completed - employees can view -->
                          <a href="admin-quotation-proposal.php?id=<?= $assessment['assessment_id'] ?>&view_only=1"
                            class="btn btn-green border flex">
                            <i class="fa-solid fa-eye me-1"></i>
                            View Quotation
                          </a>

                        <?php elseif ($assessment['status'] === 'Rejected'): ?>
                          <!-- Rejected status -->
                          <button class="btn btn-danger border flex w-100" disabled>
                            <i class="fas fa-times-circle me-1"></i>
                            Rejected
                          </button>
                        <?php endif; ?>

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
  <!-- END OF MAIN -->

  <!-- Reject Assessment Modal -->
  <div class="modal fade" id="rejectAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3">

        <div class="modal-header">
          <h5 class="modal-title">Reject Assessment Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" action="reject-assessment.php">
          <div class="modal-body">

            <!-- Reason Select -->
            <div class="mb-3">
              <label class="form-label">Reason for Rejection</label>
              <select class="form-select" name="reject_reason" id="rejectReason" required>
                <option value="">Select a reason</option>
                <option value="Location is too far away">Location is too far away</option>
                <option value="Budget is too low">Budget is too low</option>
                <option value="Schedule conflict">Schedule conflict</option>
                <option value="Service not available">Service not available</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <!-- Other Reason Input -->
            <div class="mb-3 d-none" id="otherReasonWrapper">
              <label class="form-label">Please specify</label>
              <textarea
                class="form-control"
                name="other_reason"
                rows="3"
                placeholder="Please specify the reason for rejection..."></textarea>
            </div>

            <!-- Hidden Assessment ID -->
            <input type="hidden" name="assessment_id" id="rejectAssessmentId" value="">

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="submit" class="btn btn-danger">
              Confirm Rejection
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Archive Assessment Modal -->
  <div class="modal fade" id="archiveAssessmentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="archiveAssessmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between">
          <h1 class="modal-title fs-5" id="archiveAssessmentLabel">Archive Assessment</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST">
          <input type="hidden" name="assessment_id" id="archiveAssessmentId">
          <input type="hidden" name="archive_assessment" value="1">
          <div class="modal-body">
            <h3 class="fs-24 text-center m-0 py-4">Are you sure you want to archive this assessment?</h3>
            <p class="text-center text-muted">Archived assessments can be restored later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Archive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const rejectReason = document.getElementById('rejectReason');
    const otherReasonWrapper = document.getElementById('otherReasonWrapper');

    if (rejectReason) {
      rejectReason.addEventListener('change', function() {
        if (this.value === 'Others') {
          otherReasonWrapper.classList.remove('d-none');
        } else {
          otherReasonWrapper.classList.add('d-none');
        }
      });
    }

    function setRejectAssessmentId(assessmentId) {
      document.getElementById('rejectAssessmentId').value = assessmentId;
    }

    function setArchiveAssessmentId(assessmentId) {
      document.getElementById('archiveAssessmentId').value = assessmentId;
    }

    document.addEventListener('DOMContentLoaded', function() {

      const statusFilter = document.getElementById('assessmentFilter');
      const serviceFilter = document.getElementById('serviceFilter');
      const assessmentCons = document.querySelectorAll('.assessment-con');
      const emptyStateMessage = document.getElementById('assessmentEmptyState');

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

        if (emptyStateMessage) {
          emptyStateMessage.classList.toggle('d-none', visibleCount !== 0);
        }
      }

      statusFilter.addEventListener('change', applyFilters);
      serviceFilter.addEventListener('change', applyFilters);

    });
  </script>


</body>

</html>