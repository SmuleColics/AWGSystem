<?php
ob_start();
include 'admin-header.php';

$errors = [];
$success = '';

// Get the profile ID from URL or use logged-in employee's ID
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['employee_id'];

// SECURITY CHECK: Regular employees can ONLY view their own profile
if (!$is_admin && $profile_id != $_SESSION['employee_id']) {
    echo "<script>
        alert('Access Denied: You can only view your own profile.');
        window.location='admin-employees.php';
    </script>";
    exit;
}

// Fetch the profile data
$profile_sql = "SELECT * FROM employees WHERE employee_id = $profile_id";
$profile_result = mysqli_query($conn, $profile_sql);

if (mysqli_num_rows($profile_result) == 0) {
    echo "<script>
        alert('Employee not found');
        window.location='admin-employees.php';
    </script>";
    exit;
}

$profile_data = mysqli_fetch_assoc($profile_result);
$is_own_profile = ($profile_id == $_SESSION['employee_id']);

// ========== UPDATE PROFILE ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $update_id = intval($_POST['employee_id']);
    
    // Security check
    if (!$is_admin && $update_id != $_SESSION['employee_id']) {
        $errors['security'] = 'Access denied';
    } else {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $house_no = trim($_POST['house_no'] ?? '');
        $brgy = trim($_POST['brgy'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $zip_code = trim($_POST['zip_code'] ?? '');
        
        // Validation
        if (empty($first_name)) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($last_name)) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($phone)) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^\d{11}$/', $phone)) {
            $errors['phone'] = 'Phone number must be exactly 11 digits';
        }
        
        if (empty($house_no)) {
            $errors['house_no'] = 'Street/Building/House No. is required';
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
        } elseif (!preg_match('/^\d{4}$/', $zip_code)) {
            $errors['zip_code'] = 'Zip code must be 4 digits';
        }
        
        // Admin can update employment details
        if ($is_admin) {
            $position = $_POST['position'] ?? '';
            $daily_salary = $_POST['daily_salary'] ?? '';
            $date_hired = $_POST['date_hired'] ?? '';
            
            if (empty($position)) {
                $errors['position'] = 'Position is required';
            }
            
            if ($position !== "Admin" && (empty($daily_salary) || $daily_salary <= 0)) {
                $errors['daily_salary'] = 'Valid daily salary is required';
            }
            
            if ($position !== "Admin" && empty($date_hired)) {
                $errors['date_hired'] = 'Date hired is required';
            }
        }
        
        // Update database
        if (empty($errors)) {
            $first_name_escaped = mysqli_real_escape_string($conn, $first_name);
            $last_name_escaped = mysqli_real_escape_string($conn, $last_name);
            $phone_escaped = mysqli_real_escape_string($conn, $phone);
            $house_no_escaped = mysqli_real_escape_string($conn, $house_no);
            $brgy_escaped = mysqli_real_escape_string($conn, $brgy);
            $city_escaped = mysqli_real_escape_string($conn, $city);
            $province_escaped = mysqli_real_escape_string($conn, $province);
            $zip_code_escaped = mysqli_real_escape_string($conn, $zip_code);
            
            if ($is_admin) {
                // Admin updates all fields
                $position_escaped = mysqli_real_escape_string($conn, $position);
                $date_hired_escaped = mysqli_real_escape_string($conn, $date_hired);
                
                $sql = "UPDATE employees SET 
                        first_name = '$first_name_escaped',
                        last_name = '$last_name_escaped',
                        phone = '$phone_escaped',
                        house_no = '$house_no_escaped',
                        brgy = '$brgy_escaped',
                        city = '$city_escaped',
                        province = '$province_escaped',
                        zip_code = '$zip_code_escaped',
                        position = '$position_escaped',
                        daily_salary = '$daily_salary',
                        date_hired = '$date_hired_escaped'
                        WHERE employee_id = $update_id";
            } else {
                // Employee updates only personal info
                $sql = "UPDATE employees SET 
                        first_name = '$first_name_escaped',
                        last_name = '$last_name_escaped',
                        phone = '$phone_escaped',
                        house_no = '$house_no_escaped',
                        brgy = '$brgy_escaped',
                        city = '$city_escaped',
                        province = '$province_escaped',
                        zip_code = '$zip_code_escaped'
                        WHERE employee_id = $update_id";
            }
            
            if (mysqli_query($conn, $sql)) {
                // Log the activity
                $logged_employee_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
                $target_employee_name = $first_name . ' ' . $last_name;
                
                if ($is_admin && !$is_own_profile) {
                    // Admin updating another employee's profile
                    $description = "Updated profile information for $target_employee_name";
                    log_activity($conn, $_SESSION['employee_id'], $logged_employee_name, 'UPDATE', 'EMPLOYEES', $update_id, $target_employee_name, $description);
                } else {
                    // Employee updating own profile
                    $description = "Updated own profile information";
                    log_activity($conn, $_SESSION['employee_id'], $logged_employee_name, 'UPDATE', 'EMPLOYEES', $update_id, $target_employee_name, $description);
                }
                
                echo "<script>
                    alert('Profile updated successfully!');
                    window.location='my-profile.php?id=$update_id';
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
  <title>My Profile</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />
</head>

<body>

  <main id="main" class="container-xxl px-4 py-5" style="background-color: #ebf0ed">
    <a href="admin-employees.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back
    </a>

    <div class=" d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32"><?= $is_own_profile ? 'My Profile' : htmlspecialchars($profile_data['first_name'] . "'s Profile") ?></h1>
        <p class="admin-top-desc">Manage <?= $is_own_profile ? 'your' : 'employee' ?> personal information</p>
      </div>
      <?php if ($is_own_profile || $is_admin): ?>
      <div>
        <a href="admin-change-pass.php" class="btn btn-light border d-flex align-items-center">
          <i class="fas fa-key me-1 d-none d-md-block"></i> Change Password
        </a>
      </div>
      <?php endif; ?>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center gap-3">
            <div
              class="rounded-circle text-dark d-flex align-items-center justify-content-center my-profile-icon"
              style="width: 80px; height: 80px; font-size: 28px; background-color: #ebf0ed;">
              <?php 
                $display_first_name = isset($_POST['first_name']) ? $_POST['first_name'] : $profile_data['first_name'];
                echo strtoupper(substr($display_first_name, 0, 1));
              ?>
            </div>

            <div>
              <h4 class="mb-0 mobile-fs-20">
                <?php 
                  $display_full_name = (isset($_POST['first_name']) ? $_POST['first_name'] : $profile_data['first_name']) . ' ' . 
                                      (isset($_POST['last_name']) ? $_POST['last_name'] : $profile_data['last_name']);
                  echo htmlspecialchars($display_full_name);
                ?>
              </h4>
              <p class="light-text mb-0"><?= htmlspecialchars($profile_data['email']) ?></p>
            </div>
          </div>

          <div>
            <button type="button" class="btn btn-green text-white d-flex align-items-center" onclick="document.getElementById('profileForm').querySelector('button[name=update_profile]').click();">
              <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
            </button>
          </div>
        </div>

        <hr />

        

        <!-- Profile Form -->
        <form method="POST" id="profileForm">
          <input type="hidden" name="employee_id" value="<?= $profile_data['employee_id'] ?>">
          
          <!-- Personal Information -->
          <h5 class="fw-semibold mb-3">Personal Information</h5>
          <div class="row g-4">

            <!-- First Name -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">First Name *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['first_name']) ? 'border-danger' : '' ?>"
                name="first_name"
                value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : htmlspecialchars($profile_data['first_name']) ?>"
                placeholder="Enter first name" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['first_name']) ? 'block' : 'none' ?>">
                <?= isset($errors['first_name']) ? $errors['first_name'] : '' ?>
              </p>
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Last Name *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['last_name']) ? 'border-danger' : '' ?>"
                name="last_name"
                value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : htmlspecialchars($profile_data['last_name']) ?>"
                placeholder="Enter last name" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['last_name']) ? 'block' : 'none' ?>">
                <?= isset($errors['last_name']) ? $errors['last_name'] : '' ?>
              </p>
            </div>

            <!-- Email (Read-only) -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Email Address *</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($profile_data['email']) ?>" readonly>
              <small class="text-muted">Email cannot be changed</small>
            </div>

            <!-- Phone -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Phone Number *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['phone']) ? 'border-danger' : '' ?>"
                name="phone"
                value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($profile_data['phone'] ?? '') ?>"
                placeholder="09*********"
                maxlength="11" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['phone']) ? 'block' : 'none' ?>">
                <?= isset($errors['phone']) ? $errors['phone'] : '' ?>
              </p>
            </div>

            <!-- Street Name, Bldg, House No -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Street Name, Bldg, House No. *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['house_no']) ? 'border-danger' : '' ?>"
                name="house_no"
                value="<?= isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no']) : htmlspecialchars($profile_data['house_no'] ?? '') ?>"
                placeholder="Block 1 Lot 33 Alfredo Diaz St." />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['house_no']) ? 'block' : 'none' ?>">
                <?= isset($errors['house_no']) ? $errors['house_no'] : '' ?>
              </p>
            </div>

            <!-- Barangay -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Barangay *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['brgy']) ? 'border-danger' : '' ?>"
                name="brgy"
                value="<?= isset($_POST['brgy']) ? htmlspecialchars($_POST['brgy']) : htmlspecialchars($profile_data['brgy'] ?? '') ?>"
                placeholder="Granados" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['brgy']) ? 'block' : 'none' ?>">
                <?= isset($errors['brgy']) ? $errors['brgy'] : '' ?>
              </p>
            </div>

            <!-- City -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">City *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['city']) ? 'border-danger' : '' ?>"
                name="city"
                value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($profile_data['city'] ?? '') ?>"
                placeholder="Carmona City" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['city']) ? 'block' : 'none' ?>">
                <?= isset($errors['city']) ? $errors['city'] : '' ?>
              </p>
            </div>

            <!-- Province -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Province *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['province']) ? 'border-danger' : '' ?>"
                name="province"
                value="<?= isset($_POST['province']) ? htmlspecialchars($_POST['province']) : htmlspecialchars($profile_data['province'] ?? '') ?>"
                placeholder="Cavite" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['province']) ? 'block' : 'none' ?>">
                <?= isset($errors['province']) ? $errors['province'] : '' ?>
              </p>
            </div>

            <!-- Zip Code -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Zip Code *</label>
              <input
                type="text"
                class="form-control <?= isset($errors['zip_code']) ? 'border-danger' : '' ?>"
                name="zip_code"
                value="<?= isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : htmlspecialchars($profile_data['zip_code'] ?? '') ?>"
                placeholder="4117"
                maxlength="4" />
              <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['zip_code']) ? 'block' : 'none' ?>">
                <?= isset($errors['zip_code']) ? $errors['zip_code'] : '' ?>
              </p>
            </div>
          </div>

          <hr class="my-4" />

          <!-- Employment Details -->
          <h5 class="fw-semibold mb-3">Employment Details</h5>
          <div class="row g-4">

            <!-- Position -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Position *</label>
              <?php if ($is_admin): ?>
                <select class="form-select <?= isset($errors['position']) ? 'border-danger' : '' ?>" id="inputPosition" name="position">
                  <option value="Driver" <?= (isset($_POST['position']) ? $_POST['position'] : $profile_data['position']) === 'Driver' ? 'selected' : '' ?>>Driver</option>
                  <option value="Technician" <?= (isset($_POST['position']) ? $_POST['position'] : $profile_data['position']) === 'Technician' ? 'selected' : '' ?>>Technician</option>
                  <option value="Driver/Technician" <?= (isset($_POST['position']) ? $_POST['position'] : $profile_data['position']) === 'Driver/Technician' ? 'selected' : '' ?>>Driver / Technician</option>
                  <option value="Admin/Secretary" <?= (isset($_POST['position']) ? $_POST['position'] : $profile_data['position']) === 'Admin/Secretary' ? 'selected' : '' ?>>Admin / Secretary</option>
                  <option value="Admin" <?= (isset($_POST['position']) ? $_POST['position'] : $profile_data['position']) === 'Admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['position']) ? 'block' : 'none' ?>">
                  <?= isset($errors['position']) ? $errors['position'] : '' ?>
                </p>
              <?php else: ?>
                <input type="text" class="form-control" value="<?= htmlspecialchars($profile_data['position']) ?>" readonly>
              <?php endif; ?>
            </div>

            <!-- Daily Salary (Admin only) -->
            <?php 
            $current_position = isset($_POST['position']) ? $_POST['position'] : $profile_data['position'];
            ?>
            <div class="col-md-4" id="salaryField" style="display: <?= $current_position === 'Admin' ? 'none' : 'block' ?>">
              <label class="form-label fw-semibold">Daily Salary (₱) *</label>
              <?php if ($is_admin): ?>
                <input
                  type="number"
                  class="form-control <?= isset($errors['daily_salary']) ? 'border-danger' : '' ?>"
                  id="inputSalary"
                  name="daily_salary"
                  value="<?= isset($_POST['daily_salary']) ? htmlspecialchars($_POST['daily_salary']) : htmlspecialchars($profile_data['daily_salary']) ?>"
                  min="1"
                  step="0.01"
                  placeholder="600" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['daily_salary']) ? 'block' : 'none' ?>">
                  <?= isset($errors['daily_salary']) ? $errors['daily_salary'] : '' ?>
                </p>
              <?php else: ?>
                <input type="text" class="form-control" value="₱<?= number_format($profile_data['daily_salary'], 2) ?>" readonly>
              <?php endif; ?>
            </div>

            <!-- Date Hired -->
            <div class="col-md-4" id="dateHiredField" style="display: <?= $current_position === 'Admin' ? 'none' : 'block' ?>">
              <label class="form-label fw-semibold">Date Hired *</label>
              <?php if ($is_admin): ?>
                <input
                  type="date"
                  class="form-control <?= isset($errors['date_hired']) ? 'border-danger' : '' ?>"
                  id="inputDateHired"
                  name="date_hired"
                  value="<?= isset($_POST['date_hired']) ? htmlspecialchars($_POST['date_hired']) : htmlspecialchars($profile_data['date_hired']) ?>" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['date_hired']) ? 'block' : 'none' ?>">
                  <?= isset($errors['date_hired']) ? $errors['date_hired'] : '' ?>
                </p>
              <?php else: ?>
                <input type="text" class="form-control" value="<?= date('F d, Y', strtotime($profile_data['date_hired'])) ?>" readonly>
              <?php endif; ?>
            </div>

            <!-- Account Created -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Account Created</label>
              <input type="text" class="form-control" value="<?= date('F d, Y', strtotime($profile_data['created_at'])) ?>" readonly>
            </div>

            <!-- Last Updated -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Last Updated</label>
              <input type="text" class="form-control" value="<?= date('F d, Y g:i A', strtotime($profile_data['updated_at'])) ?>" readonly>
            </div>
          </div>

          <button type="submit" name="update_profile" class="d-none">Submit</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
    const hasErrors = <?= !empty($errors) ? 'true' : 'false' ?>;

    // Handle position change to show/hide salary and date fields
    <?php if ($is_admin): ?>
    const positionSelect = document.getElementById('inputPosition');
    if (positionSelect) {
      positionSelect.addEventListener('change', function() {
        const salaryField = document.getElementById('salaryField');
        const dateHiredField = document.getElementById('dateHiredField');
        
        if (this.value === 'Admin') {
          salaryField.style.display = 'none';
          dateHiredField.style.display = 'none';
        } else {
          salaryField.style.display = 'block';
          dateHiredField.style.display = 'block';
        }
      });
    }
    <?php endif; ?>

    // Auto-scroll to first error if there are validation errors
    <?php if (!empty($errors)): ?>
    window.addEventListener('DOMContentLoaded', function() {
      const firstError = document.querySelector('.text-danger[style*="display: block"]');
      if (firstError) {
        const parentInput = firstError.previousElementSibling;
        if (parentInput) {
          parentInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          parentInput.focus();
        }
      }
    });
    <?php endif; ?>
  </script>

</body>

</html>