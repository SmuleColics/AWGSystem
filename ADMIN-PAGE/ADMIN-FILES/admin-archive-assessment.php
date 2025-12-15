<?php

// Check if user is logged in


// HANDLE RESTORE FORM SUBMISSION BEFORE ANY HTML OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'])) {
  $assessment_id = intval($_POST['assessment_id']);
  
  // Get assessment details
  $assessment_sql = "SELECT a.*, u.first_name, u.last_name 
                    FROM assessments a 
                    LEFT JOIN users u ON a.user_id = u.user_id 
                    WHERE a.assessment_id = $assessment_id";
  $assessment_result = mysqli_query($conn, $assessment_sql);
  
  if ($assessment_result && mysqli_num_rows($assessment_result) > 0) {
    $assessment = mysqli_fetch_assoc($assessment_result);
    
    // Update assessment status back to Pending
    $update_sql = "UPDATE assessments SET status = 'Pending' WHERE assessment_id = $assessment_id";
    
    if (mysqli_query($conn, $update_sql)) {
      $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
      
      // LOG ACTIVITY
      log_activity(
        $conn,
        $employee_id,
        $employee_name,
        'RESTORE',
        'ASSESSMENTS',
        $assessment_id,
        $assessment['service_type'],
        'Assessment restored for ' . $user_full_name . ' | Service: ' . $assessment['service_type']
      );
      
      $_SESSION['success_message'] = 'Assessment restored successfully!';
      header('Location: admin-archive-assessment.php');
      exit;
    } else {
      $_SESSION['error_message'] = 'Error restoring assessment: ' . mysqli_error($conn);
      header('Location: admin-archive-assessment.php');
      exit;
    }
  } else {
    $_SESSION['error_message'] = 'Assessment not found!';
    header('Location: admin-archive-assessment.php');
    exit;
  }
}

// NOW INCLUDE HEADER (which outputs HTML)
include 'admin-header.php';

// Fetch archived assessments with user information
$sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.phone, 
                u.house_no, u.brgy, u.city, u.province, u.zip_code
        FROM assessments a
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE a.status = 'Archived'
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
$archived_assessments = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $archived_assessments[] = $row;
  }
}

// Count statistics
$total_sql = "SELECT COUNT(*) as total FROM assessments";
$active_sql = "SELECT COUNT(*) as active FROM assessments WHERE status != 'Archived'";
$archived_sql = "SELECT COUNT(*) as archived FROM assessments WHERE status = 'Archived'";

$total_result = mysqli_query($conn, $total_sql);
$active_result = mysqli_query($conn, $active_sql);
$archived_result = mysqli_query($conn, $archived_sql);

$total_count = mysqli_fetch_assoc($total_result)['total'];
$active_count = mysqli_fetch_assoc($active_result)['active'];
$archived_count = mysqli_fetch_assoc($archived_result)['archived'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1, initial-scale=1.0">
  <title>Archived Assessments</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
  <link rel="stylesheet" href="../ADMIN-CSS/admin-responsiveness.css" />
</head>

<body>
  <!-- START OF MAIN  -->
  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">

    <!-- Display Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- BACK BUTTON -->
    <a href="admin-assessments.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">Archived Assessments</h1>
        <p class="admin-top-desc">View and manage archived assessment requests</p>
      </div>
    </div>

    <!-- Assessment Stats -->
    <div class="row g-3 mb-2">

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Total Assessments</p>
            <p class="mb-0 fs-24 mobile-fs-22 fw-bold"><?= $total_count ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-clipboard-list fs-20"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Active Assessments</p>
            <p class="mb-0 fs-24 mobile-fs-22 green-text fw-bold"><?= $active_count ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-check-circle fs-20 green-text"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="p-4 inventory-category rounded d-flex justify-content-between align-items-start">
          <div class="inventory-desc">
            <p class="mb-1 fs-14 light-text">Archived Assessments</p>
            <p class="mb-0 fs-24 mobile-fs-22 text-warning fw-bold"><?= $archived_count ?></p>
          </div>
          <div class="inventory-icon">
            <i class="fas fa-archive fs-20 text-warning"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- Archived Assessments Table -->
    <div class="row g-3 mt-2 pb-5">
      <div class="col-12">
        <div class="border bg-white rounded-3 mt-0 p-4">

          <div class="table-responsive bg-white rounded">
            <table id="assessmentsTable" class="table table-hover mb-0">
              <thead>
                <tr class="bg-white">
                  <th>Client Name</th>
                  <th>Email</th>
                  <th>Service Type</th>
                  <th>Preferred Date</th>
                  <th>Date Requested</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                <?php if (empty($archived_assessments)): ?>
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                      <p class="text-muted">No archived assessments found</p>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($archived_assessments as $assessment): ?>
                    <?php
                    $user_full_name = $assessment['first_name'] . ' ' . $assessment['last_name'];
                    $formatted_date = date('m/d/Y', strtotime($assessment['preferred_date']));
                    $created_date = date('m/d/Y', strtotime($assessment['created_at']));
                    ?>
                    <tr class="bg-white">
                      <th scope="row"><?= htmlspecialchars($user_full_name) ?></th>
                      <td><?= htmlspecialchars($assessment['email']) ?></td>
                      <td><?= htmlspecialchars($assessment['service_type']) ?></td>
                      <td><?= $formatted_date ?></td>
                      <td><?= $created_date ?></td>
                      <td><span class="status-badge taskstatus-pending">Archived</span></td>
                      <td>
                        <button 
                          class="btn btn-sm btn-light" 
                          data-bs-toggle="modal" 
                          data-bs-target="#viewAssessmentModal"
                          onclick="viewAssessment(<?= htmlspecialchars(json_encode($assessment)) ?>)">
                          <i class="fa-solid fa-eye me-1"></i> View
                        </button>

                        <form method="POST" action="" style="display: inline;">
                          <input type="hidden" name="assessment_id" value="<?= $assessment['assessment_id'] ?>">
                          <button type="submit" class="btn btn-sm btn-success text-white" onclick="return confirm('Are you sure you want to restore this assessment?')">
                            <i class="fa-solid fa-rotate-left me-1"></i> Restore
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>

            </table>
          </div>

        </div>
      </div>
    </div>

  </main>
  <!-- END OF MAIN -->

  <!-- Assessment Detail Modal -->
  <div class="modal fade" id="viewAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Assessment Details</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- CLIENT INFO -->
          <h5 class="fw-bold mb-3">Client Information</h5>

          <div class="row g-3">

            <div class="col-md-6">
              <p class="light-text mb-1">Full Name</p>
              <p id="modal-name"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Email</p>
              <p id="modal-email"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Phone Number</p>
              <p id="modal-phone"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Service Type</p>
              <p id="modal-service"></p>
            </div>

          </div>

          <hr>

          <!-- ASSESSMENT INFO -->
          <h5 class="fw-bold mb-3">Assessment Information</h5>

          <div class="row g-3">

            <div class="col-md-6">
              <p class="light-text mb-1">Preferred Date</p>
              <p id="modal-date"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Preferred Time</p>
              <p id="modal-time"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Estimated Budget</p>
              <p id="modal-budget"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Date Requested</p>
              <p id="modal-created"></p>
            </div>

          </div>

          <hr>

          <!-- LOCATION INFO -->
          <h5 class="fw-bold mb-3">Location Information</h5>

          <div class="row g-3">

            <div class="col-md-6">
              <p class="light-text mb-1">Street Name, Bldg, House No</p>
              <p id="modal-house"></p>
            </div>

            <div class="col-md-6">
              <p class="light-text mb-1">Barangay</p>
              <p id="modal-brgy"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">City</p>
              <p id="modal-city"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">Province</p>
              <p id="modal-province"></p>
            </div>

            <div class="col-md-4">
              <p class="light-text mb-1">Zip Code</p>
              <p id="modal-zip"></p>
            </div>

          </div>

          <hr>

          <!-- NOTES -->
          <h5 class="fw-bold mb-3">Additional Notes</h5>
          <p id="modal-notes" class="text-muted"></p>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  <!-- jQuery & DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</body>

<script>
  $(document).ready(function() {
    $('#assessmentsTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [{
        orderable: false,
        targets: [6] // Actions column cannot be sorted
      }]
    });
  });

  function viewAssessment(assessment) {
    // Populate modal with assessment data
    document.getElementById('modal-name').textContent = assessment.first_name + ' ' + assessment.last_name;
    document.getElementById('modal-email').textContent = assessment.email;
    document.getElementById('modal-phone').textContent = assessment.phone || 'N/A';
    document.getElementById('modal-service').textContent = assessment.service_type;
    
    // Format dates
    const prefDate = new Date(assessment.preferred_date);
    document.getElementById('modal-date').textContent = prefDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    document.getElementById('modal-time').textContent = assessment.preferred_time;
    
    // Format budget
    const budget = assessment.estimated_budget ? '₱' + parseFloat(assessment.estimated_budget).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'Not specified';
    document.getElementById('modal-budget').textContent = budget;
    
    // Format created date
    const createdDate = new Date(assessment.created_at);
    document.getElementById('modal-created').textContent = createdDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + ' at ' + createdDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    
    // Location
    document.getElementById('modal-house').textContent = assessment.house_no || 'N/A';
    document.getElementById('modal-brgy').textContent = assessment.brgy || 'N/A';
    document.getElementById('modal-city').textContent = assessment.city || 'N/A';
    document.getElementById('modal-province').textContent = assessment.province || 'N/A';
    document.getElementById('modal-zip').textContent = assessment.zip_code || 'N/A';
    
    // Notes
    document.getElementById('modal-notes').textContent = assessment.notes || 'No additional notes';
  }
</script>

</html>