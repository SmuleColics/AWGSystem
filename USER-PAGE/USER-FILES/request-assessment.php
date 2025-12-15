<?php
// session_start();
include 'user-header.php';

// Initialize variables
$errors = [];
$success = false;
$user_data = [];
$user_id = null;

// Get user data
$user_id = $_SESSION['user_id'] ?? null;
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $user_data = mysqli_fetch_assoc($result);
  $user_id = $user_data['user_id'];
} else {
  echo "<script>
          alert('User not found. Please login again.');
          window.location = 'login.php';
        </script>";
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_assessment'])) {
  
  // Get and validate inputs
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $service_type = trim($_POST['service_type'] ?? '');
  $preferred_date = trim($_POST['preferred_date'] ?? '');
  $preferred_time = trim($_POST['preferred_time'] ?? '');
  $house_no = trim($_POST['house_no'] ?? '');
  $brgy = trim($_POST['brgy'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $province = trim($_POST['province'] ?? '');
  $zip_code = trim($_POST['zip_code'] ?? '');
  $estimated_budget = trim($_POST['estimated_budget'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  // Validation
  if (empty($first_name)) {
    $errors['first_name'] = 'First name is required';
  }

  if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required';
  }

  if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
  } elseif (!preg_match('/^09[0-9]{9}$/', $phone)) {
    $errors['phone'] = 'Invalid phone number format (should be 09XXXXXXXXX)';
  }

  if (empty($service_type)) {
    $errors['service_type'] = 'Assessment type is required';
  }

  if (empty($preferred_date)) {
    $errors['preferred_date'] = 'Preferred date is required';
  } elseif (strtotime($preferred_date) < strtotime(date('Y-m-d'))) {
    $errors['preferred_date'] = 'Date cannot be in the past';
  }

  if (empty($preferred_time)) {
    $errors['preferred_time'] = 'Preferred time is required';
  }

  if (empty($house_no)) {
    $errors['house_no'] = 'Street name/House number is required';
  }

  if (empty($brgy)) {
    $errors['brgy'] = 'Barangay is required';
  }

  if (empty($city)) {
    $errors['city'] = 'City is required';
  }

  if (empty($province)) {
    $errors['province'] = 'Province is required';
  }

  if (empty($zip_code)) {
    $errors['zip_code'] = 'Zip code is required';
  } elseif (!preg_match('/^[0-9]{4}$/', $zip_code)) {
    $errors['zip_code'] = 'Invalid zip code format (should be 4 digits)';
  }

  // If no errors, process the data
  if (empty($errors)) {
    // First, update user information
    $update_sql = "UPDATE users SET 
                    first_name = '" . mysqli_real_escape_string($conn, $first_name) . "',
                    last_name = '" . mysqli_real_escape_string($conn, $last_name) . "',
                    phone = '" . mysqli_real_escape_string($conn, $phone) . "',
                    house_no = '" . mysqli_real_escape_string($conn, $house_no) . "',
                    brgy = '" . mysqli_real_escape_string($conn, $brgy) . "',
                    city = '" . mysqli_real_escape_string($conn, $city) . "',
                    province = '" . mysqli_real_escape_string($conn, $province) . "',
                    zip_code = '" . mysqli_real_escape_string($conn, $zip_code) . "'
                  WHERE user_id = $user_id";
    
    if (!mysqli_query($conn, $update_sql)) {
      $errors['database'] = 'Error updating user information: ' . mysqli_error($conn);
    } else {
      // Insert assessment schedule with only user_id reference
      $service_type_esc = mysqli_real_escape_string($conn, $service_type);
      $preferred_date_esc = mysqli_real_escape_string($conn, $preferred_date);
      $preferred_time_esc = mysqli_real_escape_string($conn, $preferred_time);
      $estimated_budget_esc = $estimated_budget ? mysqli_real_escape_string($conn, $estimated_budget) : 'NULL';
      $notes_esc = mysqli_real_escape_string($conn, $notes);

      $sql = "INSERT INTO assessments (user_id, service_type, preferred_date, preferred_time, estimated_budget, notes, status) 
              VALUES ($user_id, '$service_type_esc', '$preferred_date_esc', '$preferred_time_esc', " . 
              ($estimated_budget_esc === 'NULL' ? 'NULL' : "'$estimated_budget_esc'") . ", '$notes_esc', 'Pending')";

      if (mysqli_query($conn, $sql)) {
        $assessment_id = mysqli_insert_id($conn);
        $user_full_name = $first_name . ' ' . $last_name;
        
        // LOG USER ACTIVITY
        log_activity(
          $conn,
          $user_id,
          $user_full_name,
          'CREATE',
          'ASSESSMENTS',
          $assessment_id,
          $service_type,
          'User requested assessment | Service: ' . $service_type . ' | Date: ' . $preferred_date . ' ' . $preferred_time
        );

        // CREATE NOTIFICATION FOR ALL ADMINS
        // Get all admin employees
        $admin_sql = "SELECT employee_id FROM employees WHERE position LIKE '%Admin%' AND is_archived = 0";
        $admin_result = mysqli_query($conn, $admin_sql);
        
        if ($admin_result) {
          while ($admin = mysqli_fetch_assoc($admin_result)) {
            $notif_title = 'New Assessment Request';
            $notif_message = $user_full_name . ' requested a ' . $service_type . ' on ' . 
                            date('M d, Y', strtotime($preferred_date));
            $notif_link = 'admin-assessments.php?id=' . $assessment_id;
            
            $notif_sql = "INSERT INTO notifications (recipient_id, type, title, message, link, is_read) 
                         VALUES ({$admin['employee_id']}, 'ASSESSMENT_REQUEST', 
                                '" . mysqli_real_escape_string($conn, $notif_title) . "',
                                '" . mysqli_real_escape_string($conn, $notif_message) . "',
                                '" . mysqli_real_escape_string($conn, $notif_link) . "',
                                0)";
            mysqli_query($conn, $notif_sql);
          }
        }

        $success = true;
        echo "<script>
                alert('Assessment scheduled successfully!');
                window.location = '" . $_SERVER['PHP_SELF'] . "?success=1';
              </script>";
        exit;
      } else {
        $errors['database'] = 'Database error: ' . mysqli_error($conn);
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule Assessment</title>
  <link rel="stylesheet" href="../../ADMIN-PAGE/ADMIN-CSS/admin-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light py-5">

  <div class="container-xxl py-3">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">

        <div class="card shadow">
          <div class="card-header bg-white p-4">
            <h3 class="mb-0 green-text">Schedule an Assessment</h3>
            <p class="text-muted mb-0">Book a site assessment to receive a detailed quotation.</p>
          </div>

          <div class="card-body p-4">
            <form method="POST" action="">

              <div class="row g-3">

                <div class="col-md-4">
                  <label for="first-name" class="form-label">First Name *</label>
                  <input id="first-name" type="text" name="first_name" class="form-control" 
                    placeholder="James" 
                    value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : htmlspecialchars($user_data['first_name'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['first_name']) ? 'block' : 'none' ?>">
                    <?= $errors['first_name'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="col-md-4">
                  <label for="last-name" class="form-label">Last Name *</label>
                  <input id="last-name" type="text" name="last_name" class="form-control" 
                    placeholder="Macalintal" 
                    value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : htmlspecialchars($user_data['last_name'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['last_name']) ? 'block' : 'none' ?>">
                    <?= $errors['last_name'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="col-md-4">
                  <label for="phone-no" class="form-label">Phone Number *</label>
                  <input id="phone-no" type="tel" name="phone" class="form-control" 
                    placeholder="09XXXXXXXXX" 
                    value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user_data['phone'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['phone']) ? 'block' : 'none' ?>">
                    <?= $errors['phone'] ?? 'This field is required' ?>
                  </p>
                </div>

              </div>

              <div class="mt-3 row">
                <div class="col-md-12">
                  <label for="email" class="form-label">Email *</label>
                  <input id="email" type="email" name="email" class="form-control" 
                    placeholder="jamesmacalintal@gmail.com" 
                    value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" readonly>
                  <small class="text-muted">Email cannot be changed</small>
                </div>
              </div>

              <div class="mt-3 row">
                <div class="col-md-12">
                  <label for="assess-type" class="form-label">Assessment Type *</label>
                  <select id="assess-type" name="service_type" class="form-select">
                    <option value="">Select assessment type</option>
                    <option value="CCTV Installation Assessment" <?= (isset($_POST['service_type']) && $_POST['service_type'] == 'CCTV Installation Assessment') ? 'selected' : '' ?>>CCTV Installation Assessment</option>
                    <option value="Solar Panel Installation Assessment" <?= (isset($_POST['service_type']) && $_POST['service_type'] == 'Solar Panel Installation Assessment') ? 'selected' : '' ?>>Solar Panel Installation Assessment</option>
                    <option value="Renovation Assessment" <?= (isset($_POST['service_type']) && $_POST['service_type'] == 'Renovation Assessment') ? 'selected' : '' ?>>Renovation Assessment</option>
                    <option value="Other" <?= (isset($_POST['service_type']) && $_POST['service_type'] == 'Other') ? 'selected' : '' ?>>Other</option>
                  </select>
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['service_type']) ? 'block' : 'none' ?>">
                    <?= $errors['service_type'] ?? 'This field is required' ?>
                  </p>
                </div>
              </div>

              <div class="row g-3 mt-1">

                <div class="col-md-6 mt-3">
                  <label for="pref-date" class="form-label">Preferred Date *</label>
                  <input id="pref-date" type="date" name="preferred_date" class="form-control"
                    min="<?= date('Y-m-d'); ?>" 
                    value="<?= $_POST['preferred_date'] ?? '' ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['preferred_date']) ? 'block' : 'none' ?>">
                    <?= $errors['preferred_date'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="col-md-6 mt-3">
                  <label for="pref-time" class="form-label">Preferred Time *</label>
                  <select id="pref-time" name="preferred_time" class="form-select">
                    <option value="">Select time</option>
                    <option value="Morning (8AM - 12PM)" <?= (isset($_POST['preferred_time']) && $_POST['preferred_time'] == 'Morning (8AM - 12PM)') ? 'selected' : '' ?>>Morning (8AM - 12PM)</option>
                    <option value="Afternoon (12PM - 5PM)" <?= (isset($_POST['preferred_time']) && $_POST['preferred_time'] == 'Afternoon (12PM - 5PM)') ? 'selected' : '' ?>>Afternoon (12PM - 5PM)</option>
                  </select>
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['preferred_time']) ? 'block' : 'none' ?>">
                    <?= $errors['preferred_time'] ?? 'This field is required' ?>
                  </p>
                </div>
                
              </div>

              <div class="row">
                <div class="mt-4 col-md-6">
                  <label for="house-no" class="form-label">Street Name, Bldg, House No *</label>
                  <input id="house-no" type="text" name="house_no" class="form-control"
                    placeholder="Block 1 Lot 33 Alfredo Diaz St." 
                    value="<?= isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no']) : htmlspecialchars($user_data['house_no'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['house_no']) ? 'block' : 'none' ?>">
                    <?= $errors['house_no'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="mt-4 col-md-3">
                  <label for="brgy" class="form-label">Barangay *</label>
                  <input id="brgy" type="text" name="brgy" class="form-control"
                    placeholder="Granados" 
                    value="<?= isset($_POST['brgy']) ? htmlspecialchars($_POST['brgy']) : htmlspecialchars($user_data['brgy'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['brgy']) ? 'block' : 'none' ?>">
                    <?= $errors['brgy'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="mt-4 col-md-3">
                  <label for="city" class="form-label">City *</label>
                  <input id="city" type="text" name="city" class="form-control"
                    placeholder="Carmona City" 
                    value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($user_data['city'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['city']) ? 'block' : 'none' ?>">
                    <?= $errors['city'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="mt-4 col-md-3">
                  <label for="province" class="form-label">Province *</label>
                  <input id="province" type="text" name="province" class="form-control"
                    placeholder="Cavite" 
                    value="<?= isset($_POST['province']) ? htmlspecialchars($_POST['province']) : htmlspecialchars($user_data['province'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['province']) ? 'block' : 'none' ?>">
                    <?= $errors['province'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="mt-4 col-md-3">
                  <label for="zip-code" class="form-label">Zip Code *</label>
                  <input id="zip-code" type="text" name="zip_code" class="form-control"
                    placeholder="4117" 
                    value="<?= isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : htmlspecialchars($user_data['zip_code'] ?? '') ?>">
                  <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['zip_code']) ? 'block' : 'none' ?>">
                    <?= $errors['zip_code'] ?? 'This field is required' ?>
                  </p>
                </div>

                <div class="mt-4 col-md-6">
                  <label class="form-label">Estimated Budget (Optional)</label>
                  <input type="number" name="estimated_budget" class="form-control" 
                    placeholder="Enter budget in PHP" 
                    value="<?= $_POST['estimated_budget'] ?? '' ?>">
                </div>

              </div>

              <div class="mt-3">
                <label for="notes" class="form-label">Additional Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="4" 
                  placeholder="Any specific requirements or questions?"><?= $_POST['notes'] ?? '' ?></textarea>
              </div>

              <button type="submit" name="schedule_assessment" class="btn btn-green w-100 mt-4">
                Schedule Assessment
              </button>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>