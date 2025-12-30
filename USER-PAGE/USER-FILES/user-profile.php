<?php
ob_start();
include 'user-header.php';

$errors = [];
$success = '';

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header('Location: ../../LOGS/LOGS-FILES/login.php');
  exit;
}

// Fetch user profile data
$profile_sql = "SELECT * FROM users WHERE user_id = $user_id";
$profile_result = mysqli_query($conn, $profile_sql);

if (mysqli_num_rows($profile_result) == 0) {
  echo "<script>
    alert('Profile not found');
    window.location='user-portal.php';
  </script>";
  exit;
}

$profile = mysqli_fetch_assoc($profile_result);

// ========== UPDATE PROFILE ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
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
  } elseif (!preg_match('/^09\d{9}$/', $phone)) {
    $errors['phone'] = 'Phone number must be in format 09XXXXXXXXX';
  }

  if (!empty($zip_code) && !preg_match('/^\d{4}$/', $zip_code)) {
    $errors['zip_code'] = 'Zip code must be 4 digits';
  }

  // Update database if no errors
  if (empty($errors)) {
    $first_name_escaped = mysqli_real_escape_string($conn, $first_name);
    $last_name_escaped = mysqli_real_escape_string($conn, $last_name);
    $phone_escaped = mysqli_real_escape_string($conn, $phone);
    $house_no_escaped = mysqli_real_escape_string($conn, $house_no);
    $brgy_escaped = mysqli_real_escape_string($conn, $brgy);
    $city_escaped = mysqli_real_escape_string($conn, $city);
    $province_escaped = mysqli_real_escape_string($conn, $province);
    $zip_code_escaped = mysqli_real_escape_string($conn, $zip_code);

    $sql = "UPDATE users SET 
            first_name = '$first_name_escaped',
            last_name = '$last_name_escaped',
            phone = '$phone_escaped',
            house_no = '$house_no_escaped',
            brgy = '$brgy_escaped',
            city = '$city_escaped',
            province = '$province_escaped',
            zip_code = '$zip_code_escaped'
            WHERE user_id = $user_id";

    if (mysqli_query($conn, $sql)) {
      // Update session variables
      $_SESSION['first_name'] = $first_name;
      $_SESSION['last_name'] = $last_name;

      // Log the activity
      $user_full_name = $first_name . ' ' . $last_name;
      $description = "Updated profile information";
      log_activity($conn, $user_id, $user_full_name, 'UPDATE', 'USERS', $user_id, $user_full_name, $description);

      echo "<script>
        alert('Profile updated successfully!');
        window.location='user-profile.php';
      </script>";
      exit;
    } else {
      $errors['database'] = 'Database error: ' . mysqli_error($conn);
    }
  }
}

function getInitials($name)
{
  $parts = explode(' ', trim($name));
  $initials = '';
  foreach ($parts as $p) {
    if (!empty($p)) {
      $initials .= strtoupper($p[0]);
    }
  }
  return substr($initials, 0, 2);
}

$full_name = trim($profile['first_name'] . ' ' . $profile['last_name']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .avatar-circle {
      width: 80px;
      height: 80px;
      background-color: #e9ecef;
      border-radius: 50%;
      font-size: 28px;
      font-weight: 500;
    }

    .btn-green {
      background-color: #16A249;
      color: white;
      border: none;
    }

    .btn-green:hover {
      background-color: #138a3d;
      color: white;
    }
  </style>
</head>

<body class="bg-light py-5 px-4">

  <div class="container-xxl">
    <div class="mx-auto" style="max-width: 700px;">

      <!-- Header -->
      <div class="mb-4 d-flex align-items-center justify-content-between gap-3">
        <div>
          <h1 class="fs-36 mobile-fs-32">My Profile</h1>
          <p class="text-muted">Manage your personal details and account information</p>
        </div>
        <div>
          <a href="user-change-pass.php" class="btn btn-light border d-flex align-items-center">
            <i class="fas fa-key me-1 d-none d-md-block"></i> Change Password</a>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">

          <!-- Profile Form -->
          <form method="POST" id="profileForm">

            <!-- Profile Info -->
            <div class="d-flex justify-content-between align-items-center mb-4 px-2">

              <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle flex my-profile-icon">
                  <?php 
                    $display_first_name = isset($_POST['first_name']) ? $_POST['first_name'] : $profile['first_name'];
                    $display_last_name = isset($_POST['last_name']) ? $_POST['last_name'] : $profile['last_name'];
                    $display_full_name = $display_first_name . ' ' . $display_last_name;
                    echo getInitials($display_full_name);
                  ?>
                </div>

                <div>
                  <h4 class="mb-0"><?= htmlspecialchars($display_full_name); ?></h4>
                  <small class="text-muted"><?= htmlspecialchars($profile['email']); ?></small>
                </div>
              </div>

              <div>
                <button type="submit" name="update_profile" class="btn btn-green d-flex align-items-center">
                  <i class="fa-solid fa-floppy-disk me-1 d-none d-md-block"></i>Save Changes
                </button>
              </div>

            </div>

            <hr>

            <!-- Personal Information -->
            <h5 class="fw-bold mb-3 px-2">Personal Information</h5>

            <div class="row g-3 mb-3 px-2">

              <!-- First Name -->
              <div class="col-md-6">
                <label class="form-label">First Name *</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['first_name']) ? 'border-danger' : '' ?>"
                  name="first_name"
                  value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : htmlspecialchars($profile['first_name']) ?>"
                  placeholder="Enter first name" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['first_name']) ? 'block' : 'none' ?>">
                  <?= isset($errors['first_name']) ? $errors['first_name'] : '' ?>
                </p>
              </div>

              <!-- Last Name -->
              <div class="col-md-6">
                <label class="form-label">Last Name *</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['last_name']) ? 'border-danger' : '' ?>"
                  name="last_name"
                  value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : htmlspecialchars($profile['last_name']) ?>"
                  placeholder="Enter last name" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['last_name']) ? 'block' : 'none' ?>">
                  <?= isset($errors['last_name']) ? $errors['last_name'] : '' ?>
                </p>
              </div>

              <!-- Email (Read-only) -->
              <div class="col-md-6">
                <label class="form-label">Email Address *</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($profile['email']) ?>" readonly>
                <small class="text-muted">Email cannot be changed</small>
              </div>

              <!-- Phone -->
              <div class="col-md-6">
                <label class="form-label">Phone Number *</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['phone']) ? 'border-danger' : '' ?>"
                  name="phone"
                  value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($profile['phone']) ?>"
                  placeholder="09*********"
                  maxlength="11" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['phone']) ? 'block' : 'none' ?>">
                  <?= isset($errors['phone']) ? $errors['phone'] : '' ?>
                </p>
              </div>

              <!-- Address Fields -->
              <div class="col-md-12">
                <label class="form-label">Street Name, Bldg, House No</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['house_no']) ? 'border-danger' : '' ?>"
                  name="house_no"
                  value="<?= isset($_POST['house_no']) ? htmlspecialchars($_POST['house_no']) : htmlspecialchars($profile['house_no'] ?? '') ?>"
                  placeholder="Block 1 Lot 33 Alfredo Diaz St." />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['house_no']) ? 'block' : 'none' ?>">
                  <?= isset($errors['house_no']) ? $errors['house_no'] : '' ?>
                </p>
              </div>

              <div class="col-md-6">
                <label class="form-label">Barangay</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['brgy']) ? 'border-danger' : '' ?>"
                  name="brgy"
                  value="<?= isset($_POST['brgy']) ? htmlspecialchars($_POST['brgy']) : htmlspecialchars($profile['brgy'] ?? '') ?>"
                  placeholder="Granados" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['brgy']) ? 'block' : 'none' ?>">
                  <?= isset($errors['brgy']) ? $errors['brgy'] : '' ?>
                </p>
              </div>

              <div class="col-md-6">
                <label class="form-label">City</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['city']) ? 'border-danger' : '' ?>"
                  name="city"
                  value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($profile['city'] ?? '') ?>"
                  placeholder="Carmona City" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['city']) ? 'block' : 'none' ?>">
                  <?= isset($errors['city']) ? $errors['city'] : '' ?>
                </p>
              </div>

              <div class="col-md-6">
                <label class="form-label">Province</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['province']) ? 'border-danger' : '' ?>"
                  name="province"
                  value="<?= isset($_POST['province']) ? htmlspecialchars($_POST['province']) : htmlspecialchars($profile['province'] ?? '') ?>"
                  placeholder="Cavite" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['province']) ? 'block' : 'none' ?>">
                  <?= isset($errors['province']) ? $errors['province'] : '' ?>
                </p>
              </div>

              <div class="col-md-6">
                <label class="form-label">Zip Code</label>
                <input
                  type="text"
                  class="form-control <?= isset($errors['zip_code']) ? 'border-danger' : '' ?>"
                  name="zip_code"
                  value="<?= isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : htmlspecialchars($profile['zip_code'] ?? '') ?>"
                  placeholder="4117"
                  maxlength="4" />
                <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['zip_code']) ? 'block' : 'none' ?>">
                  <?= isset($errors['zip_code']) ? $errors['zip_code'] : '' ?>
                </p>
              </div>

            </div>

          </form>

        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
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