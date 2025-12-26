<?php
ob_start();
include 'admin-header.php';

$errors = [];
$success = '';

// ========== CHANGE PASSWORD LOGIC ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePass'])) {
    $employee_id = $_SESSION['employee_id'];
    $current_password = trim($_POST['currentPass'] ?? '');
    $new_password = trim($_POST['newPass'] ?? '');
    $confirm_password = trim($_POST['confirmPass'] ?? '');
    
    // Validation
    if (empty($current_password)) {
        $errors['currentPass'] = 'Current password is required';
    }
    
    if (empty($new_password)) {
        $errors['newPass'] = 'New password is required';
    } elseif (strlen($new_password) < 8) {
        $errors['newPass'] = 'Password must be at least 8 characters';
    }
    
    if (empty($confirm_password)) {
        $errors['confirmPass'] = 'Please confirm your new password';
    }
    
    // Check if passwords match
    if (!empty($new_password) && !empty($confirm_password) && $new_password !== $confirm_password) {
        $errors['confirmPass'] = 'New password and confirmation do not match';
    }
    
    // If no validation errors, check current password
    if (empty($errors)) {
        // Fetch current password from database
        $sql = "SELECT password, first_name, last_name FROM employees WHERE employee_id = $employee_id";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $stored_password = $user_data['password'];
            
            // Check if current password matches (using password_verify for hashed passwords)
            if (!password_verify($current_password, $stored_password)) {
                $errors['currentPass'] = 'Current password is incorrect';
            } else {
                // Hash the new password before storing
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $hashed_password_escaped = mysqli_real_escape_string($conn, $hashed_password);
                
                $update_sql = "UPDATE employees SET password = '$hashed_password_escaped' WHERE employee_id = $employee_id";
                
                if (mysqli_query($conn, $update_sql)) {
                    // Log the activity
                    $employee_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
                    $description = "Changed account password";
                    log_activity($conn, $employee_id, $employee_name, 'UPDATE', 'EMPLOYEES', $employee_id, $employee_name, $description);
                    
                    echo "<script>
                        alert('Password updated successfully!');
                        window.location='my-profile.php';
                    </script>";
                    exit;
                } else {
                    $errors['database'] = 'Database error: ' . mysqli_error($conn);
                }
            }
        } else {
            $errors['database'] = 'User not found';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <link rel="stylesheet" href="../ADMIN-CSS/admin-dashboard.css" />

  <style>
    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
      user-select: none;
    }

    .toggle-password:hover {
      color: #333;
    }

    .hover-underline:hover {
      text-decoration: underline !important;
    }
  </style>
</head>

<body>

  <main id="main" class="container-xxl px-4 py-5" style="background-color: #ebf0ed">
    <a href="my-profile.php" class="btn btn-outline-secondary mb-2">
      <i class="fa fa-arrow-left me-2"></i> Back to Profile
    </a>

    <div>
      <h1 class="fs-36 mobile-fs-32">Change Password</h1>
      <p class="admin-top-desc">Update your account password</p>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">

        <?php if (isset($errors['database'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>
          <?= htmlspecialchars($errors['database']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" id="changePasswordForm">

          <!-- Current Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Current Password *</label>
            <div class="password-wrapper">
              <input 
                type="password" 
                name="currentPass" 
                class="form-control <?= isset($errors['currentPass']) ? 'border-danger' : '' ?>" 
                placeholder="Enter current password"
                value="<?= isset($_POST['currentPass']) ? htmlspecialchars($_POST['currentPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['currentPass']) ? 'block' : 'none' ?>">
              <?= isset($errors['currentPass']) ? $errors['currentPass'] : '' ?>
            </p>
          </div>

          <!-- New Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">New Password *</label>
            <div class="password-wrapper">
              <input 
                type="password" 
                name="newPass" 
                class="form-control <?= isset($errors['newPass']) ? 'border-danger' : '' ?>" 
                placeholder="Enter new password (min. 8 characters)"
                value="<?= isset($_POST['newPass']) ? htmlspecialchars($_POST['newPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['newPass']) ? 'block' : 'none' ?>">
              <?= isset($errors['newPass']) ? $errors['newPass'] : '' ?>
            </p>
            <small class="text-muted">Password must be at least 8 characters long</small>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Confirm New Password *</label>
            <div class="password-wrapper">
              <input 
                type="password" 
                name="confirmPass" 
                class="form-control <?= isset($errors['confirmPass']) ? 'border-danger' : '' ?>" 
                placeholder="Re-enter new password"
                value="<?= isset($_POST['confirmPass']) ? htmlspecialchars($_POST['confirmPass']) : '' ?>">
              <i class="fa-solid fa-eye toggle-password"></i>
            </div>
            <p class="fs-14 text-danger mb-0 mt-1" style="display: <?= isset($errors['confirmPass']) ? 'block' : 'none' ?>">
              <?= isset($errors['confirmPass']) ? $errors['confirmPass'] : '' ?>
            </p>
          </div>

          <hr class="my-4">

          <!-- Buttons -->
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <a href="../../LOGS/LOGS-FILES/forgot-pass.php" class="fs-18 text-dark hover-underline text-decoration-none">
                <i class="fas fa-question-circle me-1"></i>Forgot Password?
              </a>
            </div>
            <div>
              <a href="my-profile.php" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>Cancel
              </a>
              <button type="submit" name="changePass" class="btn btn-green text-white px-4 ms-2">
                <i class="fas fa-check me-1"></i>Update Password
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </main>

  <script>
    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(icon => {
      icon.addEventListener("click", function () {
        let input = this.previousElementSibling;

        if (input.type === "password") {
          input.type = "text";
          this.classList.remove("fa-eye");
          this.classList.add("fa-eye-slash");
        } else {
          input.type = "password";
          this.classList.remove("fa-eye-slash");
          this.classList.add("fa-eye");
        }
      });
    });

    // Auto-scroll to first error if there are validation errors
    <?php if (!empty($errors)): ?>
    window.addEventListener('DOMContentLoaded', function() {
      const firstError = document.querySelector('.text-danger[style*="display: block"]');
      if (firstError) {
        const parentInput = firstError.previousElementSibling;
        if (parentInput && parentInput.querySelector) {
          const inputField = parentInput.querySelector('input');
          if (inputField) {
            inputField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputField.focus();
          }
        }
      }
    });
    <?php endif; ?>

    // Clear error styling on input
    document.querySelectorAll('input[name]').forEach(input => {
      input.addEventListener('input', function() {
        this.classList.remove('border-danger');
        const errorMsg = this.closest('.mb-3').querySelector('.text-danger');
        if (errorMsg) {
          errorMsg.style.display = 'none';
        }
      });
    });
  </script>

</body>

</html>